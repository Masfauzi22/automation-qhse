<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SamplingSubject;
use App\Models\SamplingParameter;

class SamplingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kita buat Master Subject (Kategori)
        $rawWater = SamplingSubject::create(['name' => 'RAW WATER']);
        $wasteWater = SamplingSubject::create(['name' => 'WASTE WATER']); // Contoh tambahan

        // 2. Kita siapkan Master Parameter berdasarkan dokumen Sucofindo
        $parameters = [
            // Physical
            ['name' => 'Temperature (in situ)', 'unit' => '°C', 'method' => 'SNI 06.6989.23-2005'],
            ['name' => 'Total Dissolved Solid', 'unit' => 'mg/L', 'method' => 'SNI 6989.27-2019'],
            ['name' => 'Total Suspended Solid', 'unit' => 'mg/L', 'method' => 'SNI 6989.3-2019'],
            
            // Inorganic Chemical
            ['name' => 'pH (in situ)', 'unit' => '-', 'method' => 'SNI 6989.11-2019'],
            ['name' => 'BOD 5 day 20°C', 'unit' => 'mg/L', 'method' => 'SNI 6989.72-2009'],
            ['name' => 'COD by K2Cr2O7', 'unit' => 'mg/L', 'method' => 'SNI 6989.2-2019'],
            ['name' => 'Dissolved Oxygen (in situ)', 'unit' => 'mg/L', 'method' => 'APHA 4500-O-G'],
            ['name' => 'Total Phosphate as P', 'unit' => 'mg/L', 'method' => 'SNI 6989.31:2021'],
            ['name' => 'Nitrate (NO3) as N', 'unit' => 'mg/L', 'method' => 'SNI 6989.79.2011'],
            ['name' => 'Iron Dissolved (Fe)', 'unit' => 'mg/L', 'method' => 'SNI 6989.84-2019'],
            ['name' => 'Chloride (Cl-)', 'unit' => 'mg/L', 'method' => 'APHA 4500-Cl-D'],
            ['name' => 'Sulfate (SO4)', 'unit' => 'mg/L', 'method' => 'SNI 6989.20-2019'],
            
            // Microbiological
            ['name' => 'Fecal Coli', 'unit' => 'MPN/100 mL', 'method' => 'APHA 9223 B'],
            ['name' => 'Total Coliform', 'unit' => 'MPN/100 mL', 'method' => 'APHA 9223 B'],
        ];

        // 3. Masukkan ke database dan langsung "jodohkan" dengan RAW WATER
        foreach ($parameters as $param) {
            $createdParam = SamplingParameter::create($param);
            
            // Hubungkan Parameter ini ke Subject 'RAW WATER' di tabel pivot
            $rawWater->parameters()->attach($createdParam->id);
            
            // (Opsional) Misalnya Waste Water juga butuh pH dan COD
            if (in_array($param['name'], ['pH (in situ)', 'COD by K2Cr2O7'])) {
                $wasteWater->parameters()->attach($createdParam->id);
            }
        }
    }
}