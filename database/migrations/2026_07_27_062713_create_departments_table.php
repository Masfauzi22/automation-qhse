<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // 2. Modifikasi tabel users agar terhubung ke departments
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom string lama jika ada
            if (Schema::hasColumn('users', 'department')) {
                $table->dropColumn('department');
            }
            // Tambahkan kolom ID berelasi (Dropdown)
            $table->foreignId('department_id')->nullable()->after('email')->constrained('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
            $table->string('department')->nullable();
        });
        Schema::dropIfExists('departments');
    }
};