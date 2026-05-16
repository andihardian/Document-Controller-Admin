<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Admin bisa semua tanpa cek apapun.
     */
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null; // lanjut ke method masing-masing
    }

    /**
     * Apakah user boleh melihat dokumen ini?
     *
     * - department_head : hanya dokumen departemennya sendiri
     * - employee        : hanya dokumen departemennya sendiri
     * - viewer          : hanya dokumen departemennya (public/internal) — extend jika perlu
     */
    public function view(User $user, Document $document): bool
    {
        return $user->department_id === $document->department_id;
    }

    /**
     * Apakah user boleh membuat dokumen baru?
     */
    public function create(User $user): bool
    {
        return $user->isDepartmentHead() || $user->isEmployee();
    }

    /**
     * Apakah user boleh mengedit dokumen?
     * Hanya pembuat dokumen atau department_head departemen yang sama.
     */
    public function update(User $user, Document $document): bool
    {
        if ($user->isDepartmentHead() && $user->department_id === $document->department_id) {
            return true;
        }

        return $document->created_by === $user->id;
    }

    /**
     * Apakah user boleh menghapus (soft-delete) dokumen?
     * Hanya department_head departemen yang sama.
     */
    public function delete(User $user, Document $document): bool
    {
        return $user->isDepartmentHead()
            && $user->department_id === $document->department_id;
    }

    /**
     * Apakah user boleh restore dokumen yang di-soft-delete?
     */
    public function restore(User $user, Document $document): bool
    {
        return $user->isDepartmentHead()
            && $user->department_id === $document->department_id;
    }

    /**
     * Apakah user boleh force-delete dokumen?
     * Hanya admin — sudah di-handle oleh before().
     */
    public function forceDelete(User $user, Document $document): bool
    {
        return false; // hanya admin, sudah di-handle before()
    }
}