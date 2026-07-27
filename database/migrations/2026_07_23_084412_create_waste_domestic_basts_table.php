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
        Schema::create('waste_domestic_basts', function (Blueprint $table) {
            $table->id();
            $table->string('month_year'); // Format: "2026-08"
            $table->string('location'); // "Coal Terminal" atau "Fuel Terminal"
            
            $table->string('bast_file_path')->nullable(); 
            $table->string('status')->default('Menunggu BAST'); // Menunggu BAST / Menunggu Validasi / Tervalidasi
            
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();

            // Memastikan tidak ada duplikat bulan & lokasi yang sama
            $table->unique(['month_year', 'location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_domestic_basts');
    }
};