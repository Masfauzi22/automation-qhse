<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pemeriksa permit dipilih langsung oleh pengaju (dropdown dari data user).
 * Nama pemeriksa tetap tersimpan di supervisor_name sebagai catatan,
 * jadi tetap terbaca walaupun akunnya kelak dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_permits', function (Blueprint $table) {
            $table->foreignId('checker_id')->nullable()->after('supervisor_name')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_permits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checker_id');
        });
    }
};