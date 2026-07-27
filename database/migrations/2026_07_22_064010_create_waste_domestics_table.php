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
        Schema::create('waste_domestics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('collection_date');
            $table->string('location'); // Akan kita batasi di Controller
            $table->decimal('organic_volume', 8, 2)->default(0);
            $table->decimal('anorganic_volume', 8, 2)->default(0);
            
            // Tambahan kolom residu
            $table->decimal('residue_volume', 8, 2)->default(0); 
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_domestics');
    }
};
