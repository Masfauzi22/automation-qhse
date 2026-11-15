<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jabatan user: staff | foreman | supervisor | section_head
        Schema::table('users', function (Blueprint $table) {
            $table->string('position', 30)->nullable()->after('department_id');
        });

        // Data pengaju disalin saat pengajuan, untuk menentukan siapa atasannya
        Schema::table('work_permits', function (Blueprint $table) {
            $table->string('applicant_position', 30)->nullable()->after('applicant_name');
            $table->foreignId('applicant_department_id')->nullable()->after('applicant_position')
                ->constrained('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_permits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('applicant_department_id');
            $table->dropColumn('applicant_position');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};