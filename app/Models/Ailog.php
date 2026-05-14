<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLog extends Model
{
    public $timestamps = false; // Hanya pakai created_at (konsisten dengan AuditLog)

    protected $fillable = [
        'user_id',
        'ai_chat_id',
        'action',
        'query',
        'documents_retrieved',
        'tokens_used',
        'response_time_ms',
        'status',
        'error_message',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at'          => 'datetime',
        'documents_retrieved' => 'integer',
        'tokens_used'         => 'integer',
        'response_time_ms'    => 'integer',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(AiChat::class, 'ai_chat_id');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    // ─── Static helper ────────────────────────────────────

    /**
     * Catat aktivitas AI ke log.
     *
     * Contoh:
     *   AiLog::record('chat', 'Apa isi SOP-HR-001?', 3, 520, 1200);
     */
    public static function record(
        string  $action,
        ?string $query             = null,
        int     $documentsRetrieved = 0,
        ?int    $tokensUsed        = null,
        ?int    $responseTimeMs    = null,
        string  $status            = 'success',
        ?string $errorMessage      = null,
        ?int    $aiChatId          = null,
    ): void {
        self::create([
            'user_id'             => auth()->id(),
            'ai_chat_id'          => $aiChatId,
            'action'              => $action,
            'query'               => $query,
            'documents_retrieved' => $documentsRetrieved,
            'tokens_used'         => $tokensUsed,
            'response_time_ms'    => $responseTimeMs,
            'status'              => $status,
            'error_message'       => $errorMessage,
            'ip_address'          => request()->ip(),
            'created_at'          => now(),
        ]);
    }
}