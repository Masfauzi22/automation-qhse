<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Batas waktu awal disimpan terpisah, supaya rekap bisa membedakan
        // "tepat waktu", "selesai setelah perpanjangan", dan "terlambat"
        Schema::table('hazard_reports', function (Blueprint $table) {
            $table->date('original_due_date')->nullable()->after('due_date');
            $table->unsignedTinyInteger('extension_count')->default(0)->after('original_due_date');
        });

        // Laporan lama: batas awal = batas yang sekarang
        DB::table('hazard_reports')
            ->whereNull('original_due_date')
            ->whereNotNull('due_date')
            ->update(['original_due_date' => DB::raw('due_date')]);

        // Riwayat update progres / perpanjangan dari PIC
        Schema::create('hazard_report_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hazard_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);                    // extension | extension_rejected
            $table->text('obstacle')->nullable();          // kendala
            $table->text('temporary_action')->nullable();  // tindakan pengendalian sementara
            $table->text('note')->nullable();              // catatan HSE (saat perpanjangan ditolak)
            $table->date('old_due_date')->nullable();
            $table->date('new_due_date')->nullable();
            $table->timestamps();
        });

        // Foto tindakan sementara disimpan di tabel foto yang sama, ditautkan ke update-nya
        Schema::table('hazard_report_photos', function (Blueprint $table) {
            $table->foreignId('hazard_report_update_id')
                ->nullable()
                ->after('hazard_report_id')
                ->constrained('hazard_report_updates')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hazard_report_photos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hazard_report_update_id');
        });

        Schema::dropIfExists('hazard_report_updates');

        Schema::table('hazard_reports', function (Blueprint $table) {
            $table->dropColumn(['original_due_date', 'extension_count']);
        });
    }
};