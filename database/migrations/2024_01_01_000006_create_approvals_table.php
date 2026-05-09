<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('document_version_id')
                  ->constrained('document_versions')
                  ->cascadeOnDelete();

            $table->foreignId('approver_id')
                  ->constrained('users')
                  ->restrictOnDelete();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
            ])->default('pending');

            // Komentar / catatan dari approver
            $table->text('comments')->nullable();

            // Waktu approver mengambil aksi
            $table->timestamp('action_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
