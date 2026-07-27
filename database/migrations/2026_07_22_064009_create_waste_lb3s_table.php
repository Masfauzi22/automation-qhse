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
        Schema::create('waste_lb3s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Yang menyerahkan
            $table->string('waste_type'); // Jenis limbah (Oli bekas, majun, dll)
            $table->decimal('quantity', 8, 2); // Jumlah limbah
            $table->string('unit'); // Satuan (Liter, Kg, Pcs)
            $table->string('source_location'); // Asal limbah
            $table->string('status')->default('Pending'); // Pending / Received
            
            // Kolom untuk Tanda Tangan & Penerima
            $table->string('signature_path')->nullable(); 
            $table->string('receiver_name')->nullable(); // Nama petugas TPS
            $table->timestamp('received_at')->nullable(); // Waktu diterima
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_lb3s');
    }
};
