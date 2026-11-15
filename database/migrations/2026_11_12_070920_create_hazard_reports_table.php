<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hazard_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number', 30)->unique(); // HZR/2026/11/0001

            // ---- Pelapor (disalin saat submit, supaya rekap lama tidak berubah kalau user pindah dept) ----
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_name');
            $table->string('reporter_employee_number', 50)->nullable();
            $table->foreignId('reporter_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('reporter_department_id')->nullable()->constrained('departments')->nullOnDelete();

            // ---- Isi laporan ----
            $table->date('finding_date');                       // tanggal temuan (boleh mundur)
            $table->foreignId('hazard_category_id')->constrained('hazard_categories')->restrictOnDelete();
            $table->foreignId('hazard_priority_id')->constrained('hazard_priorities')->restrictOnDelete();
            $table->string('location');
            $table->text('description');
            $table->text('potential_hazard');
            $table->text('recommendation');
            $table->foreignId('pic_department_id')->constrained('departments')->restrictOnDelete();

            // ---- Status ----
            $table->string('status', 30)->default('pending_validation')->index();

            // ---- Validasi HSE ----
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->string('rejection_reason', 50)->nullable();
            $table->text('validation_note')->nullable();
            $table->date('due_date')->nullable();               // batas waktu follow up PIC

            // ---- Closing oleh PIC ----
            $table->foreignId('closing_submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closing_submitted_at')->nullable();
            $table->text('closing_note')->nullable();

            // ---- Closed final (diverifikasi HSE) ----
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();

            // ---- Reminder email ----
            $table->timestamp('last_reminded_at')->nullable();

            $table->timestamps();   // created_at = tanggal submit asli (tidak bisa diubah pelapor)
            $table->softDeletes();  // laporan yang dihapus masih bisa dipulihkan
        });

        // Foto temuan dan foto closing
        Schema::create('hazard_report_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_report_id')->constrained('hazard_reports')->cascadeOnDelete();
            $table->string('type', 20);                         // finding | closing
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hazard_report_photos');
        Schema::dropIfExists('hazard_reports');
    }
};