<?php

namespace App\Services\AI;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RAGService
{
    private int   $maxChunks;
    private float $similarityThreshold;

    public function __construct(
        private readonly EmbeddingService $embeddingService,
    ) {
        $this->maxChunks           = (int)   config('ai.max_chunks', 5);
        $this->similarityThreshold = (float) config('ai.similarity_threshold', 0.05);
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Ambil chunks paling relevan untuk query, dengan filter RBAC.
     */
    public function retrieve(string $query, User $user, ?int $documentId = null): Collection
    {
        $queryEmbedding  = $this->embeddingService->embedQuery($query);
        $candidateChunks = $this->getCandidateChunks($user, $documentId);

        if ($candidateChunks->isEmpty()) {
            Log::info('RAGService: tidak ada candidate chunk', [
                'user_id'     => $user->id,
                'document_id' => $documentId,
            ]);
            return collect();
        }

        if (empty($queryEmbedding)) {
            // Fallback: ambil chunk pertama saja jika embedding query gagal
            return $candidateChunks->take($this->maxChunks);
        }

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
            'user_id'         => $user->id,
            'document_id'     => $documentId,
            'candidate_count' => $candidateChunks->count(),
            'matched_count'   => $scored->count(),
            'top_similarity'  => $scored->first()['similarity'] ?? 0,
        ]);

        return $scored->pluck('chunk');
    }

    /**
     * Build context string dari chunks untuk dikirim ke LLM.
     */
    public function buildContext(Collection $chunks): string
    {
        if ($chunks->isEmpty()) {
            return '';
        }

        return $chunks->map(function (DocumentChunk $chunk) {
            $docTitle  = $chunk->document->title ?? 'Tidak diketahui';
            $pageLabel = $chunk->page_number ? "Halaman {$chunk->page_number}" : 'Halaman tidak diketahui';
            return "[Sumber: {$docTitle} | {$pageLabel}]\n{$chunk->content}";
        })->implode("\n\n---\n\n");
    }

    /**
     * Format citations dari chunks untuk ditampilkan di UI.
     */
    public function buildCitations(Collection $chunks): array
    {
        return $chunks
            ->unique(fn (DocumentChunk $c) => $c->document_id . '-' . $c->page_number)
            ->map(function (DocumentChunk $chunk) {
                $doc = $chunk->document;
                return [
                    'document_id'     => $chunk->document_id,
                    'document'        => $doc->title ?? 'Tidak diketahui',
                    'document_number' => $doc->document_number ?? '-',
                    'page'            => $chunk->page_number,
                    'chunk_id'        => $chunk->chunk_index,
                    'version'         => $doc->current_version ?? '-',
                    'department'      => $doc->department->name ?? '-',
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Retrieve khusus untuk perbandingan dua versi dokumen.
     */
    public function retrieveForVersionCompare(
        string $query,
        User   $user,
        int    $documentId,
        int    $oldVersionId,
        int    $newVersionId,
    ): array {
        $queryEmbedding = $this->embeddingService->embedQuery($query);
        $base           = $this->getCandidateChunks($user, $documentId);

        $oldChunks = $base->where('document_version_id', $oldVersionId);
        $newChunks = $base->where('document_version_id', $newVersionId);

        return [
            'old' => $this->scoreAndFilter($oldChunks, $queryEmbedding),
            'new' => $this->scoreAndFilter($newChunks, $queryEmbedding),
        ];
    }

    // ─── RBAC Filter ─────────────────────────────────────────────────────────

    private function getCandidateChunks(User $user, ?int $documentId = null): Collection
    {
        $accessibleDocIds = $this->getAccessibleDocumentIds($user);

        if ($accessibleDocIds->isEmpty()) {
            return collect();
        }

        // Jika dokumen spesifik diminta, pastikan user boleh aksesnya
        if ($documentId !== null && !$accessibleDocIds->contains($documentId)) {
            Log::warning('RAGService: akses dokumen ditolak RBAC', [
                'user_id'     => $user->id,
                'document_id' => $documentId,
            ]);
            return collect();
        }

        $query = DocumentChunk::with(['document.department'])
            ->whereIn('document_id', $accessibleDocIds)
            ->where('embedding_status', 'done')
            ->whereNotNull('embedding');

        if ($documentId !== null) {
            $query->where('document_id', $documentId);
        }

        return $query->get();
    }

    /**
     * Dapatkan daftar document_id yang boleh diakses user.
     *
     * RBAC:
     *  - admin            → semua dokumen (allow_ai_access = true)
     *  - department_head  → dokumen departemen sendiri saja (approved)
     *  - employee         → dokumen departemen sendiri saja (approved)
     *  - viewer           → dokumen approved + sensitivity public/internal
     */
    private function getAccessibleDocumentIds(User $user): Collection
    {
        $role   = $user->roles->first()?->name;
        $deptId = $user->department_id;

        $query = Document::query()
            ->where('allow_ai_access', true)
            ->whereNull('deleted_at');

        switch ($role) {
            case 'admin':
                // Semua dokumen tanpa batasan departemen
                break;

            case 'department_head':
            case 'employee':
                // Hanya dokumen approved di departemen sendiri
                $query->where('department_id', $deptId)
                      ->where('status', Document::STATUS_APPROVED);
                break;

            case 'viewer':
            default:
                $query->where('status', Document::STATUS_APPROVED)
                      ->where('department_id', $deptId)
                      ->whereIn('sensitivity_level', ['public', 'internal']);
                break;
        }

        return $query->pluck('id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function scoreAndFilter(Collection $chunks, array $queryEmbedding): Collection
    {
        if (empty($queryEmbedding)) {
            return $chunks->take($this->maxChunks);
        }

        return $chunks
            ->map(fn ($c) => ['chunk' => $c, 'similarity' => $c->cosineSimilarity($queryEmbedding)])
            ->filter(fn ($i) => $i['similarity'] >= $this->similarityThreshold)
            ->sortByDesc('similarity')
            ->take($this->maxChunks)
            ->pluck('chunk');
    }
}