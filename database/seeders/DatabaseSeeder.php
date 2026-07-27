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
        // Panggil RolePermissionSeeder di sini
        $this->call([
            RolePermissionSeeder::class,
        ]);

        $this->call([
            SamplingSeeder::class,
        ]);
    }
}