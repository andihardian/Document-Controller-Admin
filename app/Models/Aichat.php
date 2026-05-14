<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiChat extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'document_id',
        'title',
        'mode',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Dokumen fokus (hanya ada saat mode = 'document')
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class)->orderBy('created_at');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AiLog::class);
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeGlobal($query)
    {
        return $query->where('mode', 'global');
    }

    public function scopeDocumentMode($query)
    {
        return $query->where('mode', 'document');
    }

    // ─── Helper ───────────────────────────────────────────

    /**
     * Auto-generate judul dari pesan pertama user.
     * Potong di 60 karakter agar tidak terlalu panjang.
     */
    public function generateTitle(string $firstMessage): void
    {
        $this->update([
            'title' => mb_substr($firstMessage, 0, 60) . (mb_strlen($firstMessage) > 60 ? '...' : ''),
        ]);
    }

    /**
     * Pesan terakhir dalam sesi ini.
     */
    public function getLastMessageAttribute(): ?AiMessage
    {
        return $this->messages()->latest()->first();
    }
}