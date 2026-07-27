<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('env_sampling_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('env_sampling_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sampling_parameter_id')->constrained()->cascadeOnDelete();
            
            // Tipe string agar bisa menampung angka desimal maupun kata seperti "<0.03"
            $table->string('result_value')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('env_sampling_results');
    }
};