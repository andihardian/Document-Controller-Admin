<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentVersion extends Model
{
    protected $fillable = [
        'document_id',
        'version_number',
        'revision_note',
        'file_path',
        'file_size',
        'uploaded_by',
        'approved_by',
        'approved_at',
        'status',
        'is_current',
        'embedding_status', // pending | processing | done | failed
        'page_count',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'is_current'  => 'boolean',
        'page_count'  => 'integer',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class);
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeEmbeddingDone($query)
    {
        return $query->where('embedding_status', 'done');
    }

    public function scopeEmbeddingPending($query)
    {
        return $query->where('embedding_status', 'pending');
    }

    // ─── Attributes ───────────────────────────────────────

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return '-';

        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    public function getEmbeddingStatusLabelAttribute(): string
    {
        return match ($this->embedding_status) {
            'pending'    => '⏳ Menunggu proses',
            'processing' => '🔄 Sedang diproses',
            'done'       => '✅ Siap digunakan AI',
            'failed'     => '❌ Gagal diproses',
            default      => '-',
        };
    }

    // ─── Helpers ─────────────────────────────────────────

    public static function nextVersionNumber(int $documentId): string
    {
        $latest = self::where('document_id', $documentId)
                      ->orderByDesc('created_at')
                      ->value('version_number');

        if (!$latest) return '1.0';

        $parts = explode('.', $latest);
        $major = (int) ($parts[0] ?? 1);
        return ($major + 1) . '.0';
    }
}