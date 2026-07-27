<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nama tabel dipersingkat jadi 'subject_parameter' agar MySQL tidak protes
        Schema::create('subject_parameter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sampling_subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sampling_parameter_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_parameter');
    }
};