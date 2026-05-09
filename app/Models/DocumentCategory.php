<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    protected $fillable = [
        'name',
        'prefix',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    /**
     * Generate nomor dokumen berikutnya untuk kategori & departemen ini.
     * Contoh: SOP-HR-001, SOP-HR-002, WI-QA-001
     */
    public function generateDocumentNumber(Department $department): string
    {
        $prefix = strtoupper($this->prefix);
        $deptCode = strtoupper($department->code);

        // Hitung dokumen yang sudah ada untuk kombinasi ini
        $count = Document::where('category_id', $this->id)
            ->where('department_id', $department->id)
            ->withTrashed()
            ->count();

        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$deptCode}-{$sequence}";
    }
}
