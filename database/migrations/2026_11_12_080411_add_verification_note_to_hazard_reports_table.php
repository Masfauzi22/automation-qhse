<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hazard_reports', function (Blueprint $table) {
            // Catatan HSE saat memverifikasi bukti closing dari PIC (disetujui / dikembalikan)
            $table->text('verification_note')->nullable()->after('closing_note');
        });
    }

    public function down(): void
    {
        Schema::table('hazard_reports', function (Blueprint $table) {
            $table->dropColumn('verification_note');
        });
    }
};