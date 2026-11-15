<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'PT Angkasa Pura Supports', 'is_contractor' => false],
            ['name' => 'PT Elnusa Petrofin',       'is_contractor' => false],
        ];

        foreach ($companies as $company) {
            // updateOrCreate: aman dijalankan berkali-kali, tidak membuat data dobel
            Company::updateOrCreate(
                ['name' => $company['name']],
                ['is_contractor' => $company['is_contractor']]
            );
        }
    }
}