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
        Schema::create('ppe_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Pemohon
            
            $table->string('item_name'); // Helm, Sepatu Safety, Kacamata, dll
            $table->string('size')->nullable(); // Ukuran (jika sepatu/seragam)
            $table->text('reason'); // Alasan: Rusak, Hilang, Karyawan Baru
            
            // Status & Approver
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Completed'])->default('Pending');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete(); // Admin/QHSE
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppe_requests');
    }
};
