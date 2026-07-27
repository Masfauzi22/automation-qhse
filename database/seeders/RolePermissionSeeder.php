<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Role Utama
        $roleSuperadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $roleQhse       = Role::firstOrCreate(['name' => 'qhse']);
        $rolePekerja    = Role::firstOrCreate(['name' => 'pekerja']); 
        $roleMedis      = Role::firstOrCreate(['name' => 'medis']);
        $roleGa         = Role::firstOrCreate(['name' => 'ga']);
        $rolePicDept    = Role::firstOrCreate(['name' => 'pic_dept']); // <-- Tambahan Role PIC Dept

        // 2. Buat Akun Superadmin
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@gmail.com'], 
            [
                'name' => 'Muhammad Fauzi',
                'password' => Hash::make('password'), 
            ]
        );
        $superadmin->assignRole($roleSuperadmin);

        // 3. Buat Akun Tim QHSE
        $qhse = User::firstOrCreate(
            ['email' => 'qhse@gmail.com'],
            ['name' => 'Tim QHSE', 'password' => Hash::make('password')]
        );
        $qhse->assignRole($roleQhse);

        // 4. Buat Akun Pekerja
        $pekerja = User::firstOrCreate(
            ['email' => 'pekerja@gmail.com'],
            ['name' => 'Karyawan Biasa', 'password' => Hash::make('password')]
        );
        $pekerja->assignRole($rolePekerja); 

        // 5. Buat Akun Medis / Dokter
        $medis = User::firstOrCreate(
            ['email' => 'klinik@gmail.com'],
            ['name' => 'Dokter Klinik', 'password' => Hash::make('password')]
        );
        $medis->assignRole($roleMedis);

        // 6. Buat Akun Tim GA
        $ga = User::firstOrCreate(
            ['email' => 'ga@gmail.com'],
            ['name' => 'Tim General Affairs', 'password' => Hash::make('password')]
        );
        $ga->assignRole($roleGa);

        // 7. Buat Akun PIC Dept / Atasan (BARU)
        $picDept = User::firstOrCreate(
            ['email' => 'pic@gmail.com'],
            ['name' => 'Atasan / PIC Departemen', 'password' => Hash::make('password')]
        );
        $picDept->assignRole($rolePicDept);
    }
}