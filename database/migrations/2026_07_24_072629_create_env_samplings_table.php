<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('env_samplings', function (Blueprint $table) {
            $table->id();
            // Karena dibuat hari ini, tabel sampling_subjects sudah ter-create duluan, jadi relasi ini aman!
            $table->foreignId('sampling_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); 
            
            $table->date('sampling_date');
            $table->time('sampling_time')->nullable();
            $table->string('sample_mark'); 
            $table->string('coordinate')->nullable();
            $table->text('description')->nullable();
            $table->string('attachment_file_path')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('env_samplings');
    }
};