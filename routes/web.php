<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ─── Public ───────────────────────────────────────────────
// Landing page
Route::get('/', fn() => view('welcome'))->name('home');

// ─── Authenticated ────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Documents ────────────────────────────────────────
    Route::resource('documents', DocumentController::class)->except(['destroy']);

    Route::get(
        'documents/{document}/download/{version?}',
        [DocumentController::class, 'download']
    )->name('documents.download');

    Route::post(
        'documents/{document}/submit',
        [DocumentController::class, 'submit']
    )->name('documents.submit');

    Route::post(
        'documents/{document}/revise',
        [DocumentController::class, 'revise']
    )->name('documents.revise');

    // ─── AI Assistant ─────────────────────────────────────
    Route::prefix('ai')->name('ai.')->group(function () {

        // Halaman utama AI
        Route::get('/', [AiChatController::class, 'index'])
            ->name('index');

        // AI dengan konteks dokumen
        Route::get(
            '/documents/{document}',
            [AiChatController::class, 'documentContext']
        )->name('document-context');

        // ── Chat Session ──────────────────────────────────
        Route::post('/chats', [AiChatController::class, 'createChat'])
            ->name('chats.create');

        Route::get('/chats/{chat}', [AiChatController::class, 'showChat'])
            ->name('chats.show');

        Route::delete('/chats/{chat}', [AiChatController::class, 'deleteChat'])
            ->name('chats.delete');

        // ── Messages ──────────────────────────────────────
        Route::post(
            '/chats/{chat}/messages',
            [AiChatController::class, 'sendMessage']
        )->name('chats.messages.send');

        // ── Document AI Features ──────────────────────────
        Route::post(
            '/documents/{document}/summarize',
            [AiChatController::class, 'summarize']
        )->name('documents.summarize');

        Route::post(
            '/documents/{document}/compare-versions',
            [AiChatController::class, 'compareVersions']
        )->name('documents.compare-versions');
    });

    // ─── Approvals ────────────────────────────────────────
    Route::middleware('role:admin|department_head')->group(function () {

        Route::get('/approvals', [ApprovalController::class, 'index'])
            ->name('approvals.index');

        Route::get('/approvals/{documentVersion}', [ApprovalController::class, 'show'])
            ->name('approvals.show');

        Route::post('/approvals/{documentVersion}/approve', [ApprovalController::class, 'approve'])
            ->name('approvals.approve');

        Route::post('/approvals/{documentVersion}/reject', [ApprovalController::class, 'reject'])
            ->name('approvals.reject');
    });

    // ─── Admin only ───────────────────────────────────────
    Route::middleware('role:admin')->group(function () {

        Route::resource('users', UserController::class);

        Route::patch(
            'users/{user}/toggle-status',
            [UserController::class, 'toggleStatus']
        )->name('users.toggle-status');

        Route::patch(
            'users/{user}/reset-password',
            [UserController::class, 'resetPassword']
        )->name('users.reset-password');

        Route::resource('departments', DepartmentController::class);
        Route::resource('categories', DocumentCategoryController::class);

        Route::get('/audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index');

        Route::patch(
            'documents/{id}/restore',
            [DocumentController::class, 'restore']
        )->name('documents.restore');

        Route::delete(
            'documents/{document}/force-delete',
            [DocumentController::class, 'forceDelete']
        )->name('documents.force-delete');
    });
});

require __DIR__ . '/auth.php';