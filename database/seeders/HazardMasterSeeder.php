<?php

namespace Database\Seeders;

use App\Models\HazardCategory;
use App\Models\HazardPriority;
use Illuminate\Database\Seeder;

class HazardMasterSeeder extends Seeder
{
    public function run(): void
    {
        // SEMENTARA: ganti dengan pilihan dari Google Form setelah dapat dari HSE
        $categories = [
            ['name' => 'Unsafe Condition', 'description' => 'Kondisi tidak aman di area kerja', 'sort_order' => 1],
            ['name' => 'Unsafe Action',    'description' => 'Tindakan tidak aman oleh pekerja',  'sort_order' => 2],
        ];

        foreach ($categories as $category) {
            HazardCategory::updateOrCreate(['name' => $category['name']], $category);
        }

        // SEMENTARA: ganti dengan pilihan dari Google Form setelah dapat dari HSE
        // sla_days = batas waktu follow up PIC (hari) sejak divalidasi HSE
        $priorities = [
            ['name' => 'Tinggi', 'sla_days' => 1, 'color' => 'red',     'sort_order' => 1],
            ['name' => 'Sedang', 'sla_days' => 3, 'color' => 'amber',   'sort_order' => 2],
            ['name' => 'Rendah', 'sla_days' => 7, 'color' => 'emerald', 'sort_order' => 3],
        ];

        foreach ($priorities as $priority) {
            HazardPriority::updateOrCreate(['name' => $priority['name']], $priority);
        }
    }
}