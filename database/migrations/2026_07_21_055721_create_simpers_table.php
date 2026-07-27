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
        Schema::create('simpers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            
            // --- BAGIAN 1: Diisi oleh Pekerja (Wajib) ---
            $table->string('name');
            $table->string('nrp')->nullable();
            $table->string('company');
            $table->string('department');
            $table->string('blood_type', 3); 
            $table->string('position');
            $table->string('police_license');  
            
            // --- BAGIAN 2: Approval PIC Dept (TAMBAHAN BARU) ---
            // Menyimpan status persetujuan atasan
            $table->enum('pic_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            // Menyimpan ID Atasan yang memberikan approval (kosong saat baru diajukan)
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete(); 
            // Catatan atau alasan jika atasan menolak/menyetujui
            $table->text('pic_note')->nullable(); 

            // --- BAGIAN 3: Hasil Tes (PERSIAPAN - Boleh kosong dulu) ---
            $table->integer('written_test_score')->nullable(); // Nilai tes tulis
            $table->string('practical_test_status')->nullable(); // Contoh: Lulus / Tidak Lulus
            
            // --- BAGIAN 4: Diisi oleh QHSE (Boleh Kosong saat pekerja request awal) ---
            $table->string('authorized_area')->nullable(); 
            $table->string('simper_number')->nullable()->unique(); 
            $table->date('issue_date')->nullable(); 
            $table->date('valid_until')->nullable(); 
            $table->json('equipments')->nullable(); 
            
            // Status Akhir (HSE) & File
            $table->enum('status', ['Pending', 'Active', 'Expired', 'Revoked'])->default('Pending');
            $table->string('photo_path')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simpers');
    }
};