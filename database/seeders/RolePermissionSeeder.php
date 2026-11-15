<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Role, jabatan, dan akun awal dengan nama orang yang realistis (fiktif) untuk testing & demo.
 * Semua password: password
 * Email memakai domain .test supaya tidak ada email nyasar ke orang sungguhan
 * saat SMTP sudah aktif. Ganti ke email kantor asli sebelum dipakai produksi.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ---------------------------------------------------------------
        // 1. Role (menu yang boleh dibuka)
        // ---------------------------------------------------------------
        //   superadmin : pengelola sistem
        //   qhse       : tim QHSE (validasi hazard, approval permit)
        //   pic_dept   : PIC departemen (follow up hazard)
        //   pekerja    : karyawan (lapor hazard, mengajukan permit kalau jabatannya Foreman/Supervisor)
        //   medis, ga  : klinik & general affairs
        //
        // Jabatan (position) terpisah dari role, dipakai untuk alur Permit to Work:
        //   Foreman → diperiksa Supervisor / Section Head, Supervisor → diperiksa Section Head, lalu QHSE.
        foreach (['superadmin', 'qhse', 'pic_dept', 'pekerja', 'medis', 'ga'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ---------------------------------------------------------------
        // 2. Perusahaan
        // ---------------------------------------------------------------
        foreach (['PT Elnusa Petrofin', 'PT Angkasa Pura Supports'] as $name) {
            Company::firstOrCreate(['name' => $name]);
        }

        // ---------------------------------------------------------------
        // 3. Akun: [nama, email, role, jabatan, perusahaan, departemen, NIK]
        // ---------------------------------------------------------------
        $accounts = [
            // ---- Superadmin
            ['Muhammad Fauzi',  'fauzi@aqhse.test',  'superadmin', 'staff',        'PT Elnusa Petrofin',       'HSSE',           'EPN-0001'],

            // ---- Tim QHSE
            ['Rina Marlina',    'rina@aqhse.test',   'qhse',       'supervisor',   'PT Elnusa Petrofin',       'HSSE',           'EPN-0002'],
            ['Dimas Prasetyo',  'dimas@aqhse.test',  'qhse',       'staff',        'PT Elnusa Petrofin',       'HSSE',           'EPN-0003'],

            // ---- MAINTENANCE: Section Head → Supervisor → Foreman → Staff
            ['Budi Santoso',    'budi@aqhse.test',   'pic_dept',   'section_head', 'PT Elnusa Petrofin',       'MAINTENANCE',    'EPN-0010'],
            ['Hendra Gunawan',  'hendra@aqhse.test', 'pic_dept',   'supervisor',   'PT Elnusa Petrofin',       'MAINTENANCE',    'EPN-0020'],
            ['Nazam',           'nazam@aqhse.test',  'pekerja',    'foreman',      'PT Angkasa Pura Supports', 'MAINTENANCE',    'APS-0001'],
            ['Rudi Hartono',    'rudi@aqhse.test',   'pekerja',    'staff',        'PT Angkasa Pura Supports', 'MAINTENANCE',    'APS-0003'],

            // ---- OPERATION
            ['Agus Wijaya',     'agus@aqhse.test',   'pic_dept',   'section_head', 'PT Elnusa Petrofin',       'OPERATION',      'EPN-0011'],
            ['Siti Rahmawati',  'siti@aqhse.test',   'pic_dept',   'supervisor',   'PT Elnusa Petrofin',       'OPERATION',      'EPN-0021'],
            ['Ismul',           'ismul@aqhse.test',  'pekerja',    'foreman',      'PT Angkasa Pura Supports', 'OPERATION',      'APS-0002'],

            // ---- Klinik & GA
            ['dr. Ayu Lestari', 'ayu@aqhse.test',    'medis',      'staff',        'PT Elnusa Petrofin',       'MEDICAL',        'EPN-0030'],
            ['Fajar Nugroho',   'fajar@aqhse.test',  'ga',         'staff',        'PT Elnusa Petrofin',       'GENERAL AFFAIR', 'EPN-0040'],
        ];

        foreach ($accounts as [$name, $email, $role, $position, $company, $department, $nik]) {
            $companyId    = Company::where('name', $company)->value('id');
            $departmentId = $this->departmentId($department);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'            => $name,
                    'password'        => Hash::make('password'),
                    'position'        => $position,
                    'company_id'      => $companyId,
                    'department_id'   => $departmentId,
                    'employee_number' => $this->nikIfFree($nik),
                ]
            );

            // User yang sudah ada: hanya isi kolom yang masih kosong
            $missing = array_filter([
                'position'        => $user->position ? null : $position,
                'company_id'      => $user->company_id ? null : $companyId,
                'department_id'   => $user->department_id ? null : $departmentId,
                'employee_number' => $user->employee_number ? null : $this->nikIfFree($nik, $user->id),
            ]);
            if ($missing) {
                $user->update($missing);
            }

            $user->syncRoles([$role]);
        }
    }

    // Cari departemen (tidak peduli huruf besar/kecil); buat kalau belum ada
    private function departmentId(string $name): int
    {
        $department = Department::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if (! $department) {
            $department = new Department();
            $department->name = $name;
            $department->save();
        }

        return $department->id;
    }

    // NIK hanya dipakai kalau belum dimiliki user lain (kolomnya unik)
    private function nikIfFree(string $nik, ?int $exceptUserId = null): ?string
    {
        $taken = User::where('employee_number', $nik)
            ->when($exceptUserId, fn ($q) => $q->where('id', '!=', $exceptUserId))
            ->exists();

        return $taken ? null : $nik;
    }
}