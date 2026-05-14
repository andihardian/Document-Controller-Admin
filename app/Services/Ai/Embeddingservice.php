<?php

namespace App\Services\AI;

use App\Models\DocumentChunk;
use App\Models\DocumentVersion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private int    $chunkSize;
    private int    $chunkOverlap;
    private string $openAiKey;
    private bool   $useSimpleEmbedding;

    public function __construct()
    {
        $this->chunkSize          = (int) config('ai.chunk_size', 500);
        $this->chunkOverlap       = (int) config('ai.chunk_overlap', 50);
        $this->openAiKey          = config('services.openai.key', env('OPENAI_API_KEY', ''));

        // Jika OpenAI key kosong → pakai simple TF-IDF style embedding
        $this->useSimpleEmbedding = empty($this->openAiKey);

        if ($this->useSimpleEmbedding) {
            Log::warning('EmbeddingService: OPENAI_API_KEY kosong, menggunakan simple keyword embedding. Akurasi pencarian berkurang.');
        }
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    public function processVersion(DocumentVersion $version, string $text): int
    {
        DocumentChunk::where('document_version_id', $version->id)->delete();

        $chunks = $this->chunkText($text);

        if (empty($chunks)) {
            Log::warning('EmbeddingService: tidak ada chunk', ['version_id' => $version->id]);
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
                    'embedding'           => $embedding,
                    'page_number'         => $chunk['page'],
                    'content_hash'        => DocumentChunk::makeContentHash($chunk['text']),
                    'embedding_status'    => 'done',
                ]);

                $saved++;

            } catch (\Throwable $e) {
                Log::error('EmbeddingService: gagal embed chunk', [
                    'version_id'  => $version->id,
                    'chunk_index' => $index,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        return $saved;
    }

    public function embedQuery(string $query): array
    {
        try {
            return $this->generateEmbedding($query);
        } catch (\Throwable $e) {
            Log::error('EmbeddingService::embedQuery gagal', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ─── Chunking ────────────────────────────────────────────────────────────

    public function chunkText(string $text): array
    {
        $chunks  = [];
        $buffer  = '';
        $page    = 1;

        $segments = preg_split('/\[\[PAGE:(\d+)\]\]/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 0; $i < count($segments); $i++) {
            $segment = $segments[$i];

            if (is_numeric($segment)) {
                $page = (int) $segment;
                continue;
            }

            $buffer .= ' ' . $segment;
            $words   = explode(' ', trim($buffer));

            while (count($words) >= $this->chunkSize) {
                $chunkWords = array_slice($words, 0, $this->chunkSize);
                $chunkText  = implode(' ', $chunkWords);

                if (!empty(trim($chunkText))) {
                    $chunks[] = ['text' => trim($chunkText), 'page' => $page];
                }

                $words = array_slice($words, $this->chunkSize - $this->chunkOverlap);
            }

            $buffer = implode(' ', $words);
        }

        if (!empty(trim($buffer))) {
            $chunks[] = ['text' => trim($buffer), 'page' => $page];
        }

        return $chunks;
    }

    // ─── Embedding ───────────────────────────────────────────────────────────

    private function generateEmbedding(string $text): array
    {
        if ($this->useSimpleEmbedding) {
            return $this->simpleKeywordEmbedding($text);
        }

        return $this->openAiEmbedding($text);
    }

    /**
     * Embedding via OpenAI text-embedding-3-small.
     * Digunakan jika OPENAI_API_KEY tersedia.
     */
    private function openAiEmbedding(string $text): array
    {
        $model    = config('ai.embedding_model', 'text-embedding-3-small');
        $text     = $this->truncateForEmbedding($text);

        $response = Http::withToken($this->openAiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/embeddings', [
                'model' => $model,
                'input' => $text,
            ]);

        if ($response->failed()) {
            $error = $response->json('error.message') ?? $response->body();
            throw new \RuntimeException("OpenAI Embedding error: {$error}");
        }

        return $response->json('data.0.embedding') ?? [];
    }

    /**
     * Simple keyword-based embedding (TF-IDF style, 512 dimensi).
     * Digunakan sebagai fallback jika OPENAI_API_KEY kosong.
     * Akurasi lebih rendah dari OpenAI tapi tetap fungsional.
     */
    private function simpleKeywordEmbedding(string $text): array
    {
        $text   = mb_strtolower($text);
        $words  = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $size   = 512;
        $vector = array_fill(0, $size, 0.0);

        foreach ($words as $word) {
            // Hapus karakter non-alfanumerik
            $word = preg_replace('/[^a-z0-9]/', '', $word);
            if (strlen($word) < 2) continue;

            // Hash word ke beberapa posisi di vector (hash trick)
            $hash1 = crc32($word) % $size;
            $hash2 = crc32('x' . $word) % $size;
            $hash3 = crc32($word . 'z') % $size;

            $hash1 = abs($hash1);
            $hash2 = abs($hash2);
            $hash3 = abs($hash3);

            $vector[$hash1] += 1.0;
            $vector[$hash2] += 0.5;
            $vector[$hash3] += 0.3;
        }

        // Normalisasi L2
        $norm = sqrt(array_sum(array_map(fn($v) => $v ** 2, $vector)));
        if ($norm > 0) {
            $vector = array_map(fn($v) => $v / $norm, $vector);
        }

        return $vector;
    }

    // ─── Similarity ──────────────────────────────────────────────────────────

    public function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b) || empty($a)) return 0.0;

        $dot   = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($a); $i++) {
            $dot   += $a[$i] * $b[$i];
            $normA += $a[$i] ** 2;
            $normB += $b[$i] ** 2;
        }

        $denom = sqrt($normA) * sqrt($normB);
        return $denom > 0 ? $dot / $denom : 0.0;
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function truncateForEmbedding(string $text, int $maxChars = 30000): string
    {
        return strlen($text) > $maxChars ? substr($text, 0, $maxChars) : $text;
    }
}