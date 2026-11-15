<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permit to Work (semua jenis: pekerjaan panas, ketinggian, penggalian, ruang terbatas).
 * Tabel lama `permit_to_works` (kosong) tidak dipakai lagi dan tidak disentuh.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---------------- Permit (kepala form) ----------------
        Schema::create('work_permits', function (Blueprint $table) {
            $table->id();
            $table->string('permit_number', 40)->nullable()->unique(); // dibuat saat diajukan
            $table->string('type', 30)->index();                       // hot_work, height_work, ...
            $table->string('status', 30)->index();

            // Pengaju (data disalin saat pengajuan)
            $table->foreignId('applicant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('applicant_name');

            // Pelaksanaan Pekerjaan
            $table->string('executor_type', 20);                      // internal | contractor
            $table->foreignId('contractor_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('owner_department_id')->constrained('departments'); // departemen pemilik pekerjaan
            $table->string('project_name');
            $table->string('location');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('supervisor_name');                       // Nama Spv. Pekerja
            $table->unsignedSmallInteger('worker_count');
            $table->text('tools');                                   // Alat yang digunakan
            $table->string('fire_watch_name')->nullable();

            // Jenis pekerjaan & data khusus per jenis permit
            $table->json('work_types')->nullable();
            $table->string('work_type_other')->nullable();
            $table->json('details')->nullable();

            // Waktu pemberlakuan
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');

            // Close-out
            $table->dateTime('work_finished_at')->nullable();
            $table->dateTime('post_watch_until')->nullable();
            $table->text('final_condition')->nullable();

            // Catatan terakhir (dikembalikan / dihentikan)
            $table->text('last_note')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('activated_at')->nullable();   // diketahui QHSE → berlaku
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_reminded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'end_date']);
        });

        // ---------------- Jawaban checklist ----------------
        Schema::create('work_permit_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_permit_id')->constrained()->cascadeOnDelete();
            $table->string('item_key', 50);
            $table->text('item_text');                // disalin, supaya PDF lama tetap sesuai walau config berubah
            $table->string('answer', 10);              // yes | na
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->unique(['work_permit_id', 'item_key']);
        });

        // ---------------- Hasil ukur gas ----------------
        Schema::create('work_permit_gas_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_permit_id')->constrained()->cascadeOnDelete();
            $table->decimal('o2', 5, 2)->nullable();
            $table->decimal('lel', 5, 2)->nullable();
            $table->decimal('h2s', 7, 2)->nullable();
            $table->decimal('co', 7, 2)->nullable();
            $table->string('tested_by_name');
            $table->dateTime('tested_at');
            $table->boolean('is_safe')->default(true);
            $table->timestamps();
        });

        // ---------------- Aktivasi harian (kolom Day 1–7) ----------------
        Schema::create('work_permit_daily_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_permit_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_number');
            $table->date('work_date');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name');
            $table->json('confirmations')->nullable();
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->unique(['work_permit_id', 'work_date']);
        });

        // ---------------- Riwayat & tanda tangan digital ----------------
        // action: submitted, checked, approved, acknowledged, returned, stopped, resumed,
        //         finished, close_checked, close_approved, close_acknowledged, close_returned, expired
        Schema::create('work_permit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_permit_id')->constrained()->cascadeOnDelete();
            $table->string('action', 30);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_title')->nullable();  // jabatan saat tanda tangan, mis. "Superintendent Maintenance"
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['work_permit_id', 'action']);
        });

        // ---------------- File: JSA, foto checklist, foto harian, foto close-out ----------------
        Schema::create('work_permit_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_permit_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);                 // jsa | check | daily | closeout
            $table->string('item_key', 50)->nullable(); // untuk foto checklist
            $table->foreignId('work_permit_daily_log_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_permit_files');
        Schema::dropIfExists('work_permit_logs');
        Schema::dropIfExists('work_permit_daily_logs');
        Schema::dropIfExists('work_permit_gas_tests');
        Schema::dropIfExists('work_permit_checks');
        Schema::dropIfExists('work_permits');
    }
};