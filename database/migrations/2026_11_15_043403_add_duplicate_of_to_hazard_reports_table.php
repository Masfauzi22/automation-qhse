<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hazard_reports', function (Blueprint $table) {
            // Kalau ditolak karena duplikat: laporan asli yang sudah lebih dulu masuk
            $table->foreignId('duplicate_of_id')
                ->nullable()
                ->after('rejection_reason')
                ->constrained('hazard_reports')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hazard_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('duplicate_of_id');
        });
    }
};