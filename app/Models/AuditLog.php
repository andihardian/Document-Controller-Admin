<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false; // Hanya pakai created_at

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'subject_id',
        'subject_type',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Static helper untuk catat log ────────────────────

    /**
     * Catat aktivitas ke audit log.
     *
     * Contoh penggunaan:
     *   AuditLog::record('upload', 'documents', 'Upload SOP-HR-001 v1.0', $document->id, Document::class);
     */
    public static function record(
        string $action,
        string $module,
        string $description,
        ?int $subjectId = null,
        ?string $subjectType = null
    ): void {
        self::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'module'       => $module,
            'description'  => $description,
            'subject_id'   => $subjectId,
            'subject_type' => $subjectType,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
            'created_at'   => now(),
        ]);
    }
}
