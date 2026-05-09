<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_number',
        'title',
        'description',
        'category_id',
        'department_id',
        'created_by',
        'current_version',
        'status',
        'effective_date',
        'expiry_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date'    => 'date',
    ];

    // Status constants
    const STATUS_DRAFT            = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED         = 'approved';
    const STATUS_REJECTED         = 'rejected';
    const STATUS_OBSOLETE         = 'obsolete';
    const STATUS_EXPIRED          = 'expired';

    // ─── Relasi ───────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Semua versi dokumen (termasuk arsip) */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('created_at');
    }

    /** Versi aktif saat ini */
    public function currentVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->where('is_current', true);
    }

    /** Versi yang sedang pending approval */
    public function pendingVersion(): HasOne
    {
        return $this->hasOne(DocumentVersion::class)->where('status', 'pending_approval');
    }

    // ─── Scopes ───────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
                     ->whereNotNull('expiry_date')
                     ->where('expiry_date', '<', now());
    }

    public function scopeForDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    // ─── Helper ───────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'approved'         => 'green',
            'pending_approval' => 'yellow',
            'rejected'         => 'red',
            'draft'            => 'gray',
            'obsolete'         => 'purple',
            'expired'          => 'orange',
            default            => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'            => 'Draft',
            'pending_approval' => 'Pending Approval',
            'approved'         => 'Approved',
            'rejected'         => 'Rejected',
            'obsolete'         => 'Obsolete',
            'expired'          => 'Expired',
            default            => ucfirst($this->status),
        };
    }
}
