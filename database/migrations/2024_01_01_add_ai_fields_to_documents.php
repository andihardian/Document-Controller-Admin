<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── documents: tambah kolom AI ──────────────────────────────────────
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('allow_ai_access')->default(true)->after('status');
            $table->enum('sensitivity_level', ['public', 'internal', 'confidential', 'restricted'])
                  ->default('internal')->after('allow_ai_access');
            $table->timestamp('ai_processed_at')->nullable()->after('sensitivity_level');
        });

        // ── document_versions: tambah embedding_status ──────────────────────
        Schema::table('document_versions', function (Blueprint $table) {
            $table->enum('embedding_status', ['pending', 'processing', 'done', 'failed'])
                  ->default('pending')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['allow_ai_access', 'sensitivity_level', 'ai_processed_at']);
        });

        Schema::table('document_versions', function (Blueprint $table) {
            $table->dropColumn('embedding_status');
        });
    }
};