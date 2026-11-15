<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'hazard' => $this->hazardReportData(),
        ]);
    }

    /**
     * Data Hazard Report 2026.
     *
     * SEMENTARA: angka disalin manual dari Google Sheet "Hazard Report 2026"
     * (tab DASHBOARD HAZARD REPORT 2026). Setelah fitur Hazard Report jadi,
     * $monthlyRaw dan $departmentRaw cukup diganti dengan query ke tabel
     * hazard_reports. Bagian view tidak perlu diubah.
     */
    private function hazardReportData(): array
    {
        $pct = fn (int $part, int $whole) => $whole > 0 ? round($part / $whole * 100, 1) : null;

        // [bulan, total diterima, finding is close, di-close pada bulan tersebut, unsafe condition, unsafe action]
        $monthlyRaw = [
            ['Januari',   114, 109,  20,  99, 15],
            ['Februari',  117, 113,  48,  87, 30],
            ['Maret',     134, 123,  32, 127,  7],
            ['April',     163, 156,  39, 112, 51],
            ['Mei',       156, 133, 194, 144, 12],
            ['Juni',      151, 131,  91, 137, 14],
            ['Juli',      157, 120, 204, 147, 10],
            ['Agustus',   107,  89, 165,  86, 21],
            ['September', 145,  85, 113, 127, 18],
            ['Oktober',   135,  47, 179, 115, 20],
            ['November',   19,   6,  19,  18,  1],
            ['Desember',    0,   0,   0,   0,  0],
        ];

        // [departemen, open, close]
        $departmentRaw = [
            ['BC/MTC',          15,  66],
            ['SHIFT/MTC',       16,  33],
            ['ELEKTRIK/MTC',    22, 129],
            ['FABRIKASI/MTC',   36, 175],
            ['PLANNER/MTC',      0,  12],
            ['MEWS/MTC',        33,  49],
            ['INFRA/MTC',       50, 103],
            ['OPERATION',       18,  93],
            ['QHSE',            13, 102],
            ['SFC/GA',           5,  74],
            ['WH/LOGISTIC',      2,   4],
            ['TCU',             70, 188],
            ['OTHER',            6,  32],
            ['FUEL OPERATION',   0,   3],
            ['ELNUSA',           0,  48],
        ];

        $monthly = collect($monthlyRaw)->map(fn ($r) => [
            'bulan'           => $r[0],
            'diterima'        => $r[1],
            'close'           => $r[2],
            'open'            => $r[1] - $r[2],
            'close_bulan_ini' => $r[3],
            'pct_close'       => $pct($r[2], $r[1]),
            'unsafe_condition'=> $r[4],
            'unsafe_action'   => $r[5],
        ]);

        // Diurutkan dari temuan open terbanyak, supaya yang perlu dikejar ada di atas
        $departments = collect($departmentRaw)->map(fn ($r) => [
            'nama'  => $r[0],
            'open'  => $r[1],
            'close' => $r[2],
            'total' => $r[1] + $r[2],
            'pct'   => $pct($r[2], $r[1] + $r[2]),
        ])->sortByDesc('open')->values();

        $totalDiterima  = $monthly->sum('diterima');
        $totalClose     = $monthly->sum('close');
        $totalCondition = $monthly->sum('unsafe_condition');
        $totalAction    = $monthly->sum('unsafe_action');

        return [
            'tahun'        => 2026,
            'target_close' => 85, // target % close, konfirmasi ke tim HSE
            'monthly'     => $monthly,
            'departments' => $departments,
            'summary'     => [
                'diterima'          => $totalDiterima,
                'close'             => $totalClose,
                'open'              => $totalDiterima - $totalClose,
                'close_bulan_ini'   => $monthly->sum('close_bulan_ini'),
                'pct_close'         => $pct($totalClose, $totalDiterima),
                'unsafe_condition'  => $totalCondition,
                'unsafe_action'     => $totalAction,
                'pct_condition'     => $pct($totalCondition, $totalDiterima),
                'pct_action'        => $pct($totalAction, $totalDiterima),
            ],
        ];
    }
}