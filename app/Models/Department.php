<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relasi ───────────────────────────────────────────

    /** Semua user yang tergabung dalam departemen ini */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Semua dokumen milik departemen ini */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // ─── Accessor ─────────────────────────────────────────

    /** Jumlah dokumen aktif (approved) */
    public function getApprovedDocumentsCountAttribute(): int
    {
        return $this->documents()->where('status', 'approved')->count();
    }
}
