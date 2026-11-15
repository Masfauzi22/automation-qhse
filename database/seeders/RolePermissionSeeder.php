<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus cache permission Spatie supaya role baru langsung terbaca
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ---------------------------------------------------------------
        // 1. Role utama
        // ---------------------------------------------------------------
        foreach (['superadmin', 'qhse', 'pekerja', 'medis', 'ga', 'pic_dept'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // ---------------------------------------------------------------
        // 2. Akun awal
        //    company    → nama perusahaan (dibuat di CompanySeeder)
        //    department → nama departemen (dibuat otomatis kalau belum ada)
        //    nik        → ID karyawan, harus unik
        // ---------------------------------------------------------------
        $accounts = [
            [
                'email'      => 'admin@gmail.com',
                'name'       => 'Muhammad Fauzi',
                'role'       => 'superadmin',
                'company'    => 'PT Elnusa Petrofin',
                'department' => 'HSSE',
                'nik'        => 'EPN-0001',
            ],
            [
                'email'      => 'qhse@gmail.com',
                'name'       => 'Tim QHSE',
                'role'       => 'qhse',
                'company'    => 'PT Elnusa Petrofin',
                'department' => 'HSSE',
                'nik'        => 'EPN-0002',
            ],
            [
                'email'      => 'pekerja@gmail.com',
                'name'       => 'Karyawan Biasa',
                'role'       => 'pekerja',
                'company'    => 'PT Angkasa Pura Supports',
                'department' => 'MAINTENANCE',
                'nik'        => 'APS-0001',
            ],
            [
                'email'      => 'klinik@gmail.com',
                'name'       => 'Dokter Klinik',
                'role'       => 'medis',
                'company'    => 'PT Elnusa Petrofin',
                'department' => 'MEDICAL',
                'nik'        => 'EPN-0003',
            ],
            [
                'email'      => 'ga@gmail.com',
                'name'       => 'Tim General Affairs',
                'role'       => 'ga',
                'company'    => 'PT Elnusa Petrofin',
                'department' => 'GENERAL AFFAIR',
                'nik'        => 'EPN-0004',
            ],
            [
                'email'      => 'pic@gmail.com',
                'name'       => 'Atasan / PIC Departemen',
                'role'       => 'pic_dept',
                'company'    => 'PT Angkasa Pura Supports',
                'department' => 'MAINTENANCE',
                'nik'        => 'APS-0002',
            ],
        ];

        foreach ($accounts as $data) {
            $companyId    = $this->companyId($data['company']);
            $departmentId = $this->departmentId($data['department']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'            => $data['name'],
                    'password'        => Hash::make('password'),
                    'company_id'      => $companyId,
                    'department_id'   => $departmentId,
                    'employee_number' => $this->nikIfFree($data['nik']),
                ]
            );

            // User yang sudah ada: hanya isi kolom yang masih kosong,
            // supaya data yang sudah kamu ubah manual tidak tertimpa.
            $missing = array_filter([
                'company_id'      => $user->company_id ? null : $companyId,
                'department_id'   => $user->department_id ? null : $departmentId,
                'employee_number' => $user->employee_number ? null : $this->nikIfFree($data['nik'], $user->id),
            ]);
            if ($missing) {
                $user->update($missing);
            }

            $user->assignRole($data['role']);
        }
    }

    // Cari perusahaan berdasarkan nama; buat kalau belum ada
    private function companyId(string $name): int
    {
        return Company::firstOrCreate(['name' => $name])->id;
    }

    // Cari departemen berdasarkan nama (tidak peduli huruf besar/kecil); buat kalau belum ada
    private function departmentId(string $name): int
    {
        $department = Department::whereRaw('LOWER(name) = ?', [strtolower($name)])->first();

        if (! $department) {
            $department = new Department();   // tanpa mass assignment, aman walau 'name' belum fillable
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