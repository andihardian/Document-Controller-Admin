<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Nomor dokumen unik, e.g. "SOP-HR-001"
            $table->string('document_number')->unique();

            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('category_id')
                  ->constrained('document_categories')
                  ->restrictOnDelete();

            $table->foreignId('department_id')
                  ->constrained('departments')
                  ->restrictOnDelete();

            $table->foreignId('created_by')
                  ->constrained('users')
                  ->restrictOnDelete();

            // Versi aktif saat ini, e.g. "1.0", "2.0"
            $table->string('current_version', 10)->default('1.0');

            $table->enum('status', [
                'draft',
                'pending_approval',
                'approved',
                'rejected',
                'obsolete',
                'expired',
            ])->default('draft');

            $table->date('effective_date')->nullable();  // Tanggal mulai berlaku
            $table->date('expiry_date')->nullable();     // Tanggal kadaluarsa

            $table->softDeletes(); // Dokumen tidak dihapus permanen kecuali oleh admin
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
