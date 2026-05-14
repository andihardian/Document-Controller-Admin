<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'ai_chat_id',
        'role',
        'content',
        'sources',
        'tokens_used',
        'response_time_ms',
    ];

    protected $casts = [
        // sources otomatis decode/encode JSON
        'sources'          => 'array',
        'tokens_used'      => 'integer',
        'response_time_ms' => 'integer',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function chat(): BelongsTo
    {
        return $this->belongsTo(AiChat::class, 'ai_chat_id');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeFromUser($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeFromAssistant($query)
    {
        return $query->where('role', 'assistant');
    }

    // ─── Helper ───────────────────────────────────────────

    public function isFromUser(): bool
    {
        return $this->role === 'user';
    }

    public function isFromAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Format sources untuk ditampilkan di UI.
     *
     * Return contoh:
     * [
     *   ['document' => 'SOP-HR-001.pdf', 'page' => 12, 'chunk_id' => 45],
     * ]
     */
    public function getFormattedSourcesAttribute(): array
    {
        return $this->sources ?? [];
    }
}