<?php

namespace App\Services\AI;

use App\Models\DocumentChunk;
use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class EmbeddingService
{
    private int $chunkSize;
    private int $chunkOverlap;

    public function __construct()
    {
        $this->chunkSize    = (int) config('ai.chunk_size', 500);
        $this->chunkOverlap = (int) config('ai.chunk_overlap', 50);
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Proses satu DocumentVersion: chunk → embed → simpan ke DB.
     * Return: jumlah chunk yang berhasil disimpan.
     */
    public function processVersion(DocumentVersion $version, string $text): int
    {
        // Hapus chunk lama jika ada (re-process)
        DocumentChunk::where('document_version_id', $version->id)->delete();

        $chunks = $this->chunkText($text);

        if (empty($chunks)) {
            Log::warning('EmbeddingService: tidak ada chunk yang dihasilkan', [
                'version_id' => $version->id,
            ]);
            return 0;
        }

        $saved = 0;
        foreach ($chunks as $index => $chunk) {
            try {
                $embedding = $this->generateEmbedding($chunk['text']);

                DocumentChunk::create([
                    'document_id'         => $version->document_id,
                    'document_version_id' => $version->id,
                    'chunk_index'         => $index,
                    'content'             => $chunk['text'],
                    'embedding'           => json_encode($embedding),
                    'page'                => $chunk['page'],
                    'token_count'         => $this->estimateTokens($chunk['text']),
                ]);

                $saved++;

            } catch (\Throwable $e) {
                Log::error('EmbeddingService: gagal embed chunk', [
                    'version_id'  => $version->id,
                    'chunk_index' => $index,
                    'error'       => $e->getMessage(),
                ]);
                // Lanjutkan chunk berikutnya meski satu gagal
            }
        }

        return $saved;
    }

    /**
     * Generate embedding untuk satu string query (untuk semantic search).
     */
    public function embedQuery(string $query): array
    {
        return $this->generateEmbedding($query);
    }

    // ─── Chunking ────────────────────────────────────────────────────────────

    /**
     * Pecah teks menjadi chunks dengan sliding window + deteksi halaman.
     * Return: [['text' => string, 'page' => int], ...]
     */
    public function chunkText(string $text): array
    {
        $chunks      = [];
        $currentPage = 1;

        // Pisahkan berdasarkan marker halaman yang disisipkan oleh parser
        $segments = preg_split('/\[\[PAGE:(\d+)\]\]/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $buffer   = '';
        $page     = 1;

        for ($i = 0; $i < count($segments); $i++) {
            $segment = $segments[$i];

            // Jika segmen adalah nomor halaman (hasil PREG_SPLIT_DELIM_CAPTURE)
            if (is_numeric($segment)) {
                $page = (int) $segment;
                continue;
            }

            $buffer .= ' ' . $segment;

            // Ketika buffer cukup besar, potong menjadi chunks
            $words = explode(' ', trim($buffer));

            while (count($words) >= $this->chunkSize) {
                $chunkWords  = array_slice($words, 0, $this->chunkSize);
                $chunkText   = implode(' ', $chunkWords);

                if (! empty(trim($chunkText))) {
                    $chunks[] = ['text' => trim($chunkText), 'page' => $page];
                }

                // Geser window dengan overlap
                $words = array_slice($words, $this->chunkSize - $this->chunkOverlap);
            }

            $buffer = implode(' ', $words);
        }

        // Sisa buffer terakhir
        if (! empty(trim($buffer))) {
            $chunks[] = ['text' => trim($buffer), 'page' => $page];
        }

        return $chunks;
    }

    // ─── Embedding ───────────────────────────────────────────────────────────

    /**
     * Panggil OpenAI Embedding API.
     */
    private function generateEmbedding(string $text): array
    {
        $model = config('ai.embedding_model', 'text-embedding-3-small');

        // Truncate jika terlalu panjang (max ~8192 token untuk embedding model)
        $text = $this->truncateForEmbedding($text);

        $response = OpenAI::embeddings()->create([
            'model' => $model,
            'input' => $text,
        ]);

        return $response->embeddings[0]->embedding;
    }

    // ─── Similarity ──────────────────────────────────────────────────────────

    /**
     * Cosine similarity antara dua vector embedding.
     * Return: float antara -1 dan 1 (1 = identik).
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return 0.0;
        }

        $dot    = 0.0;
        $normA  = 0.0;
        $normB  = 0.0;

        for ($i = 0; $i < count($a); $i++) {
            $dot   += $a[$i] * $b[$i];
            $normA += $a[$i] ** 2;
            $normB += $b[$i] ** 2;
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dot / ($normA * $normB);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function estimateTokens(string $text): int
    {
        // Estimasi kasar: 1 token ≈ 4 karakter (untuk teks bahasa Indonesia/Inggris)
        return (int) ceil(strlen($text) / 4);
    }

    private function truncateForEmbedding(string $text, int $maxChars = 30000): string
    {
        if (strlen($text) > $maxChars) {
            return substr($text, 0, $maxChars);
        }
        return $text;
    }
}