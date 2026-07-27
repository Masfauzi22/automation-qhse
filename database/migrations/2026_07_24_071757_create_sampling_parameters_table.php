<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sampling_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: Total Dissolved Solid
            $table->string('unit')->nullable(); // Contoh: mg/L
            $table->string('method')->nullable(); // Contoh: SNI 6989.27-2019
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sampling_parameters');
    }
};