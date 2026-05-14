<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: AI Chat With Documents
 *
 * Membuat 4 tabel:
 *  1. ai_chats          — sesi chat per user
 *  2. ai_messages       — pesan dalam sesi chat
 *  3. document_chunks   — potongan teks dokumen + embedding
 *  4. ai_logs           — audit log khusus aktivitas AI
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 1. ai_chats — Sesi percakapan user dengan AI
        // ─────────────────────────────────────────────────────────────
        Schema::create('ai_chats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Opsional: chat terkait dokumen tertentu (document context mode)
            $table->foreignId('document_id')
                  ->nullable()
                  ->constrained('documents')
                  ->nullOnDelete();

            // Judul sesi, auto-generated dari pesan pertama
            $table->string('title')->default('New Chat');

            // Mode chat: 'global' (semua dokumen) | 'document' (fokus 1 dokumen)
            $table->enum('mode', ['global', 'document'])->default('global');

            // Soft delete agar history bisa dipulihkan
            $table->softDeletes();
            $table->timestamps();
        });

        // ─────────────────────────────────────────────────────────────
        // 2. ai_messages — Pesan dalam sesi chat
        // ─────────────────────────────────────────────────────────────
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ai_chat_id')
                  ->constrained('ai_chats')
                  ->cascadeOnDelete();

            // 'user' = pertanyaan dari user, 'assistant' = jawaban AI
            $table->enum('role', ['user', 'assistant']);

            $table->longText('content');

            // Sumber/citation yang digunakan AI untuk menjawab (JSON array)
            // Contoh: [{"document":"SOP-HR-001.pdf","page":12,"chunk_id":45}]
            $table->json('sources')->nullable();

            // Token yang digunakan (untuk monitoring cost)
            $table->unsignedInteger('tokens_used')->nullable();

            // Waktu respons AI dalam ms
            $table->unsignedInteger('response_time_ms')->nullable();

            $table->timestamps();
        });

        // ─────────────────────────────────────────────────────────────
        // 3. document_chunks — Potongan teks dokumen untuk RAG
        // ─────────────────────────────────────────────────────────────
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();

            $table->foreignId('document_version_id')
                  ->constrained('document_versions')
                  ->cascadeOnDelete();

            // Urutan chunk dalam dokumen (0-based)
            $table->unsignedSmallInteger('chunk_index');

            // Nomor halaman sumber (untuk citation)
            $table->unsignedSmallInteger('page_number')->nullable();

            // Teks asli chunk
            $table->text('content');

            // Embedding vektor disimpan sebagai JSON array of floats
            // Contoh: [0.123, -0.456, 0.789, ...]
            // Diset MEDIUMTEXT agar cukup untuk 1536 dimensi (text-embedding-3-small)
            // Saat upgrade ke pgvector → kolom ini diganti tipe vector(1536)
            $table->mediumText('embedding')->nullable();

            // Hash konten untuk deteksi duplikat & cache
            $table->string('content_hash', 64)->nullable();

            // Status proses embedding chunk ini
            $table->enum('embedding_status', ['pending', 'processing', 'done', 'failed'])
                  ->default('pending');

            $table->timestamps();

            // Index untuk mempercepat pencarian per dokumen
            $table->index(['document_id', 'chunk_index']);
            $table->index(['document_version_id', 'embedding_status']);
            $table->index('content_hash');
        });

        // ─────────────────────────────────────────────────────────────
        // 4. ai_logs — Audit log khusus aktivitas AI
        // ─────────────────────────────────────────────────────────────
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->foreignId('ai_chat_id')
                  ->nullable()
                  ->constrained('ai_chats')
                  ->nullOnDelete();

            // Jenis aksi: chat, embedding, search, summary, version_compare, dll.
            $table->string('action', 50);

            // Query yang dikirim user (untuk analitik)
            $table->text('query')->nullable();

            // Jumlah dokumen/chunk yang ditemukan
            $table->unsignedSmallInteger('documents_retrieved')->default(0);

            // Token yang digunakan
            $table->unsignedInteger('tokens_used')->nullable();

            // Waktu respons dalam ms
            $table->unsignedInteger('response_time_ms')->nullable();

            // Status: success | failed | blocked (RBAC)
            $table->enum('status', ['success', 'failed', 'blocked'])->default('success');

            // Pesan error jika gagal
            $table->text('error_message')->nullable();

            $table->string('ip_address', 45)->nullable();

            // Audit log tidak perlu updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
        Schema::dropIfExists('document_chunks');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_chats');
    }
};