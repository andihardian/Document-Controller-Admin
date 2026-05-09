<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Modifikasi tabel users bawaan Breeze — tambah kolom DCS
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departments')
                  ->nullOnDelete()
                  ->after('email');
            $table->string('phone', 20)->nullable()->after('department_id');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['phone', 'avatar', 'is_active', 'last_login_at']);
        });
    }
};
