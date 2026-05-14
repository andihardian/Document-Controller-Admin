<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChunk extends Model
{
    protected $fillable = [
        'document_id',
        'document_version_id',
        'chunk_index',
        'page_number',
        'content',
        'embedding',
        'content_hash',
        'embedding_status',
    ];

    protected $casts = [
        // embedding disimpan sebagai JSON string, di-cast ke array saat dibaca
        'embedding'    => 'array',
        'chunk_index'  => 'integer',
        'page_number'  => 'integer',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('embedding_status', 'pending');
    }

    public function scopeDone($query)
    {
        return $query->where('embedding_status', 'done');
    }

    public function scopeFailed($query)
    {
        return $query->where('embedding_status', 'failed');
    }

    public function scopeForDocument($query, int $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    // ─── Helper ───────────────────────────────────────────

    /**
     * Hitung cosine similarity antara embedding chunk ini
     * dengan query embedding yang diberikan.
     *
     * Digunakan untuk mencari chunk paling relevan di PHP-side
     * (sebelum upgrade ke pgvector yang bisa query langsung di SQL).
     *
     * @param  array<float> $queryEmbedding  Embedding vektor dari query user
     * @return float  Nilai 0.0 – 1.0 (semakin tinggi = semakin relevan)
     */
    public function cosineSimilarity(array $queryEmbedding): float
    {
        $chunkEmbedding = $this->embedding;

        if (empty($chunkEmbedding) || empty($queryEmbedding)) {
            return 0.0;
        }

        $dotProduct  = 0.0;
        $normA       = 0.0;
        $normB       = 0.0;

        $length = min(count($chunkEmbedding), count($queryEmbedding));

        for ($i = 0; $i < $length; $i++) {
            $dotProduct += $chunkEmbedding[$i] * $queryEmbedding[$i];
            $normA      += $chunkEmbedding[$i] ** 2;
            $normB      += $queryEmbedding[$i] ** 2;
        }

        $denominator = sqrt($normA) * sqrt($normB);

        return $denominator > 0 ? $dotProduct / $denominator : 0.0;
    }

    /**
     * Generate hash konten untuk deteksi duplikat.
     */
    public static function makeContentHash(string $content): string
    {
        return hash('sha256', trim($content));
    }
}