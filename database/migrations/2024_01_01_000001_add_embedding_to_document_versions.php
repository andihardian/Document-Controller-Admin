<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom ke document_versions jika belum ada
        Schema::table('document_versions', function (Blueprint $table) {
            if (!Schema::hasColumn('document_versions', 'embedding_status')) {
                $table->enum('embedding_status', ['pending', 'processing', 'done', 'failed'])
                      ->default('pending')
                      ->after('is_current');
            }

            if (!Schema::hasColumn('document_versions', 'page_count')) {
                $table->unsignedInteger('page_count')->nullable()->after('embedding_status');
            }
        });

        // Buat tabel document_chunks jika belum ada
        if (!Schema::hasTable('document_chunks')) {
            Schema::create('document_chunks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')
                      ->constrained('documents')
                      ->cascadeOnDelete();
                $table->foreignId('document_version_id')
                      ->constrained('document_versions')
                      ->cascadeOnDelete();
                $table->unsignedInteger('chunk_index');
                $table->unsignedInteger('page_number')->nullable();
                $table->longText('content');
                $table->longText('embedding')->nullable();  // JSON array float
                $table->string('content_hash', 64)->nullable();
                $table->enum('embedding_status', ['pending', 'done', 'failed'])->default('pending');
                $table->timestamps();

                $table->index(['document_id', 'embedding_status']);
                $table->index('document_version_id');
                $table->index('content_hash');
            });
        }
    }

    public function down(): void
    {
        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropColumn(['embedding_status', 'page_count']);
        });

        Schema::dropIfExists('document_chunks');
    }
};