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
        Schema::create('permit_to_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Pemohon/Supervisor
            
            $table->string('work_type'); // Hot Work, Confined Space, Working at Height, dll
            $table->string('location'); // Lokasi kerja
            $table->dateTime('start_time'); // Mulai kerja
            $table->dateTime('end_time'); // Selesai kerja
            $table->text('description'); // Deskripsi pekerjaan
            
            // Status & Approver
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Closed'])->default('Pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); // QHSE yang ACC
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permit_to_works');
    }
};
