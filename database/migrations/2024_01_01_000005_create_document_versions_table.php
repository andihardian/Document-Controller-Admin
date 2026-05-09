<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_id')
                  ->constrained('documents')
                  ->cascadeOnDelete();

            // Nomor versi, e.g. "1.0", "2.0", "2.1"
            $table->string('version_number', 10);

            // Catatan perubahan pada versi ini
            $table->text('revision_note')->nullable();

            // Path file PDF di storage, e.g. "documents/HR/SOP-HR-001/v2.pdf"
            $table->string('file_path');

            // Ukuran file dalam bytes
            $table->unsignedBigInteger('file_size')->nullable();

            $table->foreignId('uploaded_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'rejected',
            ])->default('draft');

            // Hanya satu versi yang aktif (is_current = true) per dokumen
            $table->boolean('is_current')->default(false);

            $table->timestamps();

            // Satu dokumen tidak boleh punya dua versi dengan nomor yang sama
            $table->unique(['document_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
