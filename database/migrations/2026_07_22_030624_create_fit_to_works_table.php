<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fit_to_works', function (Blueprint $table) {
            $table->id();

            // --- DATA KARYAWAN (Diisi Pekerja) ---
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('age')->nullable();
            $table->integer('sleep_hours')->nullable();
            $table->string('job_type')->nullable(); // Jenis Pekerjaan (Confined Space, dll)

            // --- KUESIONER KESEHATAN (Diisi Pekerja) ---
            // Boolean: 1 = Ya, 0 = Tidak
            $table->boolean('q1_is_fit')->nullable();
            $table->boolean('q2_accustomed')->nullable();
            $table->boolean('q3_vision_issue')->nullable();
            $table->boolean('q4_asthma')->nullable();
            $table->boolean('q5_epilepsy')->nullable();
            $table->boolean('q6_trained')->nullable();
            $table->text('notes')->nullable(); // Keterangan kuesioner jika ada

            // --- PEMERIKSAAN FISIK (Diisi oleh Medis/Dokter) ---
            $table->string('blood_pressure')->nullable();   // mmHg
            $table->string('body_temp')->nullable();        // Celcius
            $table->string('spo2')->nullable();             // %
            $table->string('heart_rate')->nullable();       // Denyut nadi x/menit
            $table->string('respiration_rate')->nullable(); // Pernafasan x/menit

            // --- REKOMENDASI DOKTER ---
            // Boolean lama: 1 = Dapat Bekerja, 0 = Tidak Dapat Bekerja (tetap diisi otomatis oleh controller)
            $table->boolean('is_recommended')->nullable();

            // Rekomendasi dokter 3 opsi: Fit / Fit With Note / Tidak Bisa Bekerja
            $table->enum('fit_status', ['Fit', 'Fit_With_Note', 'Unfit'])->nullable();

            // Catatan dokter (opsional): batasan kerja, saran, atau alasan Unfit
            $table->text('medical_notes')->nullable();

            // --- STATUS BERJENJANG ---
            // Pekerja Submit -> Pending_Medis -> Dokter Submit -> Pending_QHSE -> QHSE Submit -> Approved/Rejected
            $table->enum('status', ['Pending_Medis', 'Pending_QHSE', 'Approved', 'Rejected'])->default('Pending_Medis');

            // Pihak yang menyetujui
            $table->foreignId('medical_id')->nullable()->constrained('users')->nullOnDelete(); // Dokter Bertugas
            $table->foreignId('qhse_id')->nullable()->constrained('users')->nullOnDelete();    // QHSE Bertugas

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fit_to_works');
    }
};