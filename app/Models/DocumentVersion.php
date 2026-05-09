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
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'is_current'  => 'boolean',
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

    // ─── Helper ───────────────────────────────────────────

    /** URL untuk download file */
    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /** Ukuran file dalam format human-readable */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return '-';

        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }

    /** Generate nomor versi berikutnya untuk dokumen ini */
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
