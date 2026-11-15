<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Data perusahaan (user bisa merujuk ke perusahaan)
            CompanySeeder::class,

            // 2. Role dan akun awal
            RolePermissionSeeder::class,

            // 3. Permission menu & submenu dari config/menus.php
            MenuPermissionSeeder::class,

            // 4. Master kategori bahaya & prioritas Hazard Report
            HazardMasterSeeder::class,

            // 5. Master data sampling lingkungan
            SamplingSeeder::class,
        ]);
    }
}