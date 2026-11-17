<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengukuran gas untuk Ruang Terbatas & Tertutup:
 * - co2                      : form Ruang Terbatas mengukur CO₂ (< 2500 ppm)
 * - work_permit_daily_log_id : pengukuran ulang setiap aktivasi harian (null = saat pengajuan)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_permit_gas_tests', function (Blueprint $table) {
            $table->decimal('co2', 8, 2)->nullable()->after('co');
            $table->foreignId('work_permit_daily_log_id')->nullable()->after('work_permit_id')
                ->constrained('work_permit_daily_logs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_permit_gas_tests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_permit_daily_log_id');
            $table->dropColumn('co2');
        });
    }
};