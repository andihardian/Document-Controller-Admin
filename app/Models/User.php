<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_active'         => 'boolean',
        'password'          => 'hashed',
    ];

    // ─── Relasi ───────────────────────────────────────────

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** Dokumen yang dibuat oleh user ini */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    /** Versi dokumen yang diupload oleh user ini */
    public function documentVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'uploaded_by');
    }

    /** Approval yang dilakukan oleh user ini */
    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    /** Log aktivitas user ini */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // ─── Helper ───────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isDepartmentHead(): bool
    {
        return $this->hasRole('department_head');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    public function isViewer(): bool
    {
        return $this->hasRole('viewer');
    }

    /** URL avatar, fallback ke inisial jika tidak ada */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
    }
}
