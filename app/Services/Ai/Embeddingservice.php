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

    // Stopwords Bahasa Indonesia + Inggris untuk diabaikan saat embedding
    private const STOPWORDS = [
        // Indonesia
        'yang', 'dan', 'di', 'ke', 'dari', 'ini', 'itu', 'dengan', 'untuk',
        'pada', 'adalah', 'dalam', 'tidak', 'akan', 'oleh', 'juga', 'ada',
        'telah', 'bisa', 'atau', 'sudah', 'dapat', 'serta', 'lebih', 'harus',
        'nya', 'kami', 'kita', 'anda', 'saya', 'dia', 'mereka', 'hal', 'cara',
        'bagi', 'sesuai', 'setiap', 'berdasarkan', 'tersebut', 'sebagai',
        'bahwa', 'jika', 'maka', 'antara', 'seluruh', 'semua', 'pula',
        // Inggris
        'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
        'of', 'with', 'by', 'from', 'is', 'are', 'was', 'were', 'be', 'been',
        'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
        'should', 'may', 'might', 'shall', 'can', 'this', 'that', 'these',
        'those', 'it', 'its', 'we', 'you', 'he', 'she', 'they', 'not', 'all',
    ];

    public function __construct()
    {
        $this->chunkSize          = (int) config('ai.chunk_size', 400);
        $this->chunkOverlap       = (int) config('ai.chunk_overlap', 80);
        $this->openAiKey          = config('services.openai.key', env('OPENAI_API_KEY', ''));
        $this->useSimpleEmbedding = empty($this->openAiKey);

        if ($this->useSimpleEmbedding) {
            Log::info('EmbeddingService: mode simple keyword embedding (tanpa OpenAI).');
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
     * Improved keyword embedding — TF-IDF style dengan:
     * - Stopword removal (Indonesia + Inggris)
     * - N-gram bigram untuk konteks frasa
     * - Bobot berbeda untuk kata pendek vs panjang
     * - Normalisasi L2
     * Dimensi: 1024 (lebih besar = lebih sedikit collision)
     */
    private function simpleKeywordEmbedding(string $text): array
    {
        $size   = 1024;
        $vector = array_fill(0, $size, 0.0);

        // Normalisasi teks
        $text  = mb_strtolower($text);
        $text  = preg_replace('/[^a-z0-9\s]/u', ' ', $text);
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        // Filter stopwords dan kata terlalu pendek
        $filteredWords = array_values(array_filter($words, function ($word) {
            return strlen($word) >= 2 && !in_array($word, self::STOPWORDS);
        }));

        if (empty($filteredWords)) {
            return $vector;
        }

        // Hitung TF (term frequency)
        $termFreq = array_count_values($filteredWords);
        $maxFreq  = max($termFreq);

        // Unigram embedding
        foreach ($termFreq as $word => $freq) {
            // Bobot: kata lebih panjang = lebih penting (informatif)
            $lengthBonus = min(strlen($word) / 6.0, 1.5);
            // Normalized TF
            $tf = $freq / $maxFreq;
            $weight = $tf * $lengthBonus;

            // Hash ke beberapa posisi (hash trick)
            foreach (['', '_a', '_b', '_c'] as $salt) {
                $pos = abs(crc32($word . $salt)) % $size;
                $vector[$pos] += $weight * (1.0 - (array_search($salt, ['', '_a', '_b', '_c']) * 0.2));
            }
        }

        // Bigram embedding (frasa 2 kata berturut-turut)
        for ($i = 0; $i < count($filteredWords) - 1; $i++) {
            $bigram = $filteredWords[$i] . '_' . $filteredWords[$i + 1];
            $pos1   = abs(crc32($bigram)) % $size;
            $pos2   = abs(crc32('bi_' . $bigram)) % $size;
            $vector[$pos1] += 0.8;
            $vector[$pos2] += 0.4;
        }

        // Normalisasi L2
        $norm = sqrt(array_sum(array_map(fn($v) => $v ** 2, $vector)));
        if ($norm > 0) {
            $vector = array_map(fn($v) => round($v / $norm, 6), $vector);
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