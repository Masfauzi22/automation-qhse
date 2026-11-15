<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sertifikat Penyelesaian: terbit otomatis saat PTW Closed (close-out di-approve QHSE)
        Schema::create('work_permit_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_permit_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('certificate_number', 40)->unique();   // CERT/PTW/IBT/2026/11/0001
            $table->string('verify_token', 40)->unique();         // untuk QR verifikasi (tidak bisa ditebak)
            $table->boolean('incident_free')->default(true);      // false = ada insiden / near miss
            $table->text('incident_note')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('issued_by_name');
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->index('issued_at'); // untuk rekap bulanan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_permit_certificates');
    }
};