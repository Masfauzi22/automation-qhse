<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hazard_reports', function (Blueprint $table) {
            // Titik lokasi temuan dari peta / GPS (opsional)
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            // Akurasi GPS dalam meter. Kosong kalau titik dipilih manual di peta.
            $table->unsignedSmallInteger('location_accuracy')->nullable()->after('longitude');

            $table->index(['latitude', 'longitude']); // untuk cek laporan duplikat berdasarkan jarak
        });
    }

    public function down(): void
    {
        Schema::table('hazard_reports', function (Blueprint $table) {
            $table->dropIndex(['latitude', 'longitude']);
            $table->dropColumn(['latitude', 'longitude', 'location_accuracy']);
        });
    }
};