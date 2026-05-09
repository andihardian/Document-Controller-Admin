<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Null jika aksi dilakukan oleh sistem (e.g. auto-expire)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // Jenis aksi: login, logout, upload, approve, reject, delete, dll.
            $table->string('action', 50);

            // Modul yang terlibat: documents, users, approvals, dll.
            $table->string('module', 50);

            // Deskripsi lengkap aksi
            $table->text('description');

            // ID record yang terdampak (opsional)
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type', 100)->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // Audit log tidak perlu updated_at
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
