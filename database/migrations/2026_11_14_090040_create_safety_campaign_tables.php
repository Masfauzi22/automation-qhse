<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---------------- Campaign ----------------
        Schema::create('safety_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->date('start_date');
            $table->date('end_date')->nullable();                 // kosong = tidak ada batas
            $table->boolean('target_all')->default(true);         // true = semua karyawan
            $table->boolean('is_published')->default(false);      // false = draft
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Departemen target (dipakai kalau target_all = false)
        Schema::create('safety_campaign_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safety_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->unique(['safety_campaign_id', 'department_id'], 'scd_campaign_dept_unique');
        });

        // Foto / poster
        Schema::create('safety_campaign_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safety_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ---------------- Soal ----------------
        Schema::create('safety_campaign_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safety_campaign_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->text('explanation')->nullable();              // pembahasan, tampil setelah menjawab
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('safety_campaign_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safety_campaign_question_id')->constrained()->cascadeOnDelete();
            $table->string('option_text', 500);
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // ---------------- Respon karyawan ----------------
        // Satu baris per karyawan per campaign
        Schema::create('safety_campaign_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safety_campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete(); // disalin saat pertama dibuka
            $table->timestamp('first_viewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();         // lulus soal / konfirmasi baca
            $table->unsignedTinyInteger('first_attempt_score')->nullable();
            $table->unsignedTinyInteger('first_attempt_total')->nullable();
            $table->unsignedSmallInteger('attempts_count')->default(0);
            $table->text('comment')->nullable();
            $table->timestamp('commented_at')->nullable();
            $table->timestamps();
            $table->unique(['safety_campaign_id', 'user_id'], 'scr_campaign_user_unique');
        });

        // Setiap kali karyawan mengerjakan soal
        Schema::create('safety_campaign_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safety_campaign_response_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('attempt_no');
            $table->unsignedTinyInteger('score');
            $table->unsignedTinyInteger('total');
            $table->boolean('passed')->default(false);
            $table->timestamps();
        });

        // Jawaban per soal di setiap percobaan
        Schema::create('safety_campaign_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('safety_campaign_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('safety_campaign_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('safety_campaign_option_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safety_campaign_answers');
        Schema::dropIfExists('safety_campaign_attempts');
        Schema::dropIfExists('safety_campaign_responses');
        Schema::dropIfExists('safety_campaign_options');
        Schema::dropIfExists('safety_campaign_questions');
        Schema::dropIfExists('safety_campaign_images');
        Schema::dropIfExists('safety_campaign_department');
        Schema::dropIfExists('safety_campaigns');
    }
};