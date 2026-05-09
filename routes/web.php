<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─── Public ───────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ─── Authenticated ────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Documents (semua role bisa akses) ────────────────
    Route::resource('documents', DocumentController::class)->except(['destroy']);
    Route::get('documents/{document}/download/{version?}', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('documents/{document}/submit', [DocumentController::class, 'submit'])->name('documents.submit');
    Route::post('documents/{document}/revise', [DocumentController::class, 'revise'])->name('documents.revise');

    // ─── Approvals (department_head & admin) ──────────────
    Route::middleware('role:admin|department_head')->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{documentVersion}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{documentVersion}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{documentVersion}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    // ─── Admin only ───────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        // User management
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Department management
        Route::resource('departments', DepartmentController::class);

        // Document category management
        Route::resource('categories', DocumentCategoryController::class);

        // Audit log
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        // Restore dokumen yang di-soft delete
        Route::patch('documents/{id}/restore', [DocumentController::class, 'restore'])->name('documents.restore');
        Route::delete('documents/{document}/force-delete', [DocumentController::class, 'forceDelete'])->name('documents.force-delete');
    });
});

require __DIR__.'/auth.php';