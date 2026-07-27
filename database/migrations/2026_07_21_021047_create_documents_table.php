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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->nullable(); // Nomor dokumen (opsional)
            $table->string('title'); // Nama Dokumen (SOP IBT, WI IBT, dll)
            $table->enum('type', ['Policy', 'SOP', 'Work Instruction', 'Form', 'Letter', 'HIRA']); // Kategori Dokumen
            $table->string('file_path'); // Lokasi file PDF/Word disimpan
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade'); // Relasi ke siapa yang upload
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
