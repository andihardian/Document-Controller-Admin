<?php

namespace App\Services\AI;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RAGService
{
    private int $maxChunks;
    private float $similarityThreshold;

    public function __construct(
        private readonly EmbeddingService $embeddingService,
    ) {
        $this->maxChunks           = (int) config('ai.max_chunks', 5);
        $this->similarityThreshold = (float) config('ai.similarity_threshold', 0.3);
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Ambil chunks paling relevan untuk query, dengan filter RBAC.
     *
     * @param  string    $query       Pertanyaan dari user
     * @param  User      $user        User yang sedang login (untuk filter RBAC)
     * @param  int|null  $documentId  Jika tidak null → fokus ke dokumen ini saja
     * @return Collection<DocumentChunk>  Diurutkan dari paling relevan
     */
    public function retrieve(string $query, User $user, ?int $documentId = null): Collection
    {
        // 1. Embed query
        $queryEmbedding = $this->embeddingService->embedQuery($query);

        // 2. Ambil candidate chunks (sudah difilter RBAC + AI permission)
        $candidateChunks = $this->getCandidateChunks($user, $documentId);

        if ($candidateChunks->isEmpty()) {
            Log::info('RAGService: tidak ada candidate chunk', [
                'user_id'     => $user->id,
                'document_id' => $documentId,
            ]);
            return collect();
        }

        // 3. Hitung cosine similarity & filter threshold
        $scored = $candidateChunks
            ->map(function (DocumentChunk $chunk) use ($queryEmbedding) {
                return [
                    'chunk'      => $chunk,
                    'similarity' => $chunk->cosineSimilarity($queryEmbedding),
                ];
            })
            ->filter(fn ($item) => $item['similarity'] >= $this->similarityThreshold)
            ->sortByDesc('similarity')
            ->take($this->maxChunks);

        Log::info('RAGService: chunks retrieved', [
            'user_id'          => $user->id,
            'document_id'      => $documentId,
            'candidate_count'  => $candidateChunks->count(),
            'matched_count'    => $scored->count(),
        ]);

        return $scored->pluck('chunk');
    }

    /**
     * Build context string dari chunks untuk dikirim ke LLM.
     * Format yang dibaca AI: "Dokumen X, Hal Y:\n<konten>"
     */
    public function buildContext(Collection $chunks): string
    {
        if ($chunks->isEmpty()) {
            return '';
        }

        $parts = $chunks->map(function (DocumentChunk $chunk) {
            $docTitle  = $chunk->document->title ?? 'Tidak diketahui';
            $pageLabel = $chunk->page_number ? "Halaman {$chunk->page_number}" : 'Halaman tidak diketahui';

            return "[Sumber: {$docTitle} | {$pageLabel}]\n{$chunk->content}";
        });

        return $parts->implode("\n\n---\n\n");
    }

    /**
     * Format citations dari chunks (untuk ditampilkan di UI).
     * Return: array of citation objects.
     */
    public function buildCitations(Collection $chunks): array
    {
        return $chunks
            ->unique(fn (DocumentChunk $c) => $c->document_id . '-' . $c->page_number)
            ->map(function (DocumentChunk $chunk) {
                $doc = $chunk->document;

                return [
                    'document_id'      => $chunk->document_id,
                    'document_title'   => $doc->title ?? 'Tidak diketahui',
                    'document_number'  => $doc->document_number ?? '-',
                    'page_number'      => $chunk->page_number,
                    'chunk_index'      => $chunk->chunk_index,
                    'version'          => $doc->current_version ?? '-',
                    'department'       => $doc->department->name ?? '-',
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Retrieve khusus untuk perbandingan dua versi dokumen.
     * Return ['old' => Collection, 'new' => Collection]
     */
    public function retrieveForVersionCompare(
        string $query,
        User $user,
        int $documentId,
        int $oldVersionId,
        int $newVersionId,
    ): array {
        $queryEmbedding = $this->embeddingService->embedQuery($query);

        $base = $this->getChunksForDocument($user, $documentId);

        $oldChunks = $base->where('document_version_id', $oldVersionId);
        $newChunks = $base->where('document_version_id', $newVersionId);

        return [
            'old' => $this->scoreAndFilter($oldChunks, $queryEmbedding),
            'new' => $this->scoreAndFilter($newChunks, $queryEmbedding),
        ];
    }

    // ─── RBAC Filter ─────────────────────────────────────────────────────────

    /**
     * Ambil chunks yang BOLEH diakses user ini.
     * Filter dilakukan di level query (bukan di PHP) untuk efisiensi.
     */
    private function getCandidateChunks(User $user, ?int $documentId = null): Collection
    {
        $accessibleDocIds = $this->getAccessibleDocumentIds($user);

        if ($accessibleDocIds->isEmpty()) {
            return collect();
        }

        $query = DocumentChunk::with(['document.department'])
            ->whereIn('document_id', $accessibleDocIds)
            ->where('embedding_status', 'done')
            ->whereNotNull('embedding');

        if ($documentId !== null) {
            // Pastikan document_id yang diminta memang boleh diakses
            if (! $accessibleDocIds->contains($documentId)) {
                Log::warning('RAGService: user mencoba akses dokumen yang tidak diizinkan', [
                    'user_id'     => $user->id,
                    'document_id' => $documentId,
                ]);
                return collect();
            }
            $query->where('document_id', $documentId);
        }

        return $query->get();
    }

    private function getChunksForDocument(User $user, int $documentId): Collection
    {
        return $this->getCandidateChunks($user, $documentId);
    }

    /**
     * Dapatkan daftar document_id yang boleh diakses user.
     * Logika RBAC:
     *  - admin           → semua dokumen (allow_ai_access = true)
     *  - head_department → dokumen dept sendiri + approved dokumen dept lain
     *  - employee        → dokumen dept sendiri yang approved
     *  - viewer          → hanya dokumen approved yang allow_ai_access = true
     */
    private function getAccessibleDocumentIds(User $user): Collection
    {
        $role       = $user->roles->first()?->name;
        $deptId     = $user->department_id;

        $query = Document::query()
            ->where('allow_ai_access', true)
            ->whereNull('deleted_at');

        switch ($role) {
            case 'admin':
                // Admin bisa lihat semua, kecuali deleted
                break;

            case 'head_department':
                // Semua di dept sendiri + approved di dept lain
                $query->where(function ($q) use ($deptId) {
                    $q->where('department_id', $deptId)
                      ->orWhere('status', Document::STATUS_APPROVED);
                });
                break;

            case 'employee':
                // Hanya dokumen approved di dept sendiri
                $query->where('department_id', $deptId)
                      ->where('status', Document::STATUS_APPROVED);
                break;

            case 'viewer':
            default:
                // Hanya dokumen approved + sensitivity rendah
                $query->where('status', Document::STATUS_APPROVED)
                      ->whereIn('sensitivity_level', ['public', 'internal']);
                break;
        }

        return $query->pluck('id');
    }

    // ─── Internal Helpers ────────────────────────────────────────────────────

    private function scoreAndFilter(Collection $chunks, array $queryEmbedding): Collection
    {
        return $chunks
            ->map(fn ($c) => ['chunk' => $c, 'similarity' => $c->cosineSimilarity($queryEmbedding)])
            ->filter(fn ($i) => $i['similarity'] >= $this->similarityThreshold)
            ->sortByDesc('similarity')
            ->take($this->maxChunks)
            ->pluck('chunk');
    }
}