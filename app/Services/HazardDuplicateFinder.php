<?php

namespace App\Services;

use App\Models\HazardReport;
use Illuminate\Support\Collection;

/**
 * Mencari laporan lain yang kemungkinan melaporkan bahaya yang sama.
 *
 * Skor 0–100 dari gabungan:
 *  - jarak titik GPS          maks 40
 *  - kemiripan teks lokasi    maks 25
 *  - kemiripan deskripsi      maks 25
 *  - kategori sama            +5
 *  - departemen PIC sama      +5
 *  - pelapor sama, selisih ≤ 2 hari  +10 (laporan dobel), skor tetap maks 100
 *
 * Pemakaian: app(HazardDuplicateFinder::class)->find($report);
 */
class HazardDuplicateFinder
{
    // Kata umum yang tidak membedakan satu laporan dengan yang lain
    private const STOPWORDS = [
        'yang', 'di', 'ke', 'dari', 'dan', 'atau', 'ada', 'dengan', 'untuk', 'pada', 'ini', 'itu',
        'sudah', 'belum', 'tidak', 'karena', 'saat', 'akan', 'bisa', 'dapat', 'agar', 'oleh', 'juga',
        'sangat', 'masih', 'sedang', 'terdapat', 'adanya', 'para', 'serta', 'tersebut', 'banyak',
        'sekitar', 'dekat', 'sebelah', 'depan', 'belakang', 'samping', 'atas', 'bawah', 'dalam', 'luar',
        'area', 'lokasi', 'tempat', 'bagian', 'kondisi', 'temuan', 'pekerja', 'orang', 'the', 'and',
    ];

    public function find(HazardReport $report, ?int $limit = null): Collection
    {
        $cfg      = config('hazard.duplicate');
        $limit  ??= $cfg['max_results'];
        $window   = $cfg['window_days'];
        $baseDate = $report->finding_date ?? $report->created_at;

        $candidates = HazardReport::query()
            ->with(['category', 'picDepartment', 'findingPhotos'])
            ->whereKeyNot($report->getKey())
            ->where('status', '!=', HazardReport::STATUS_REJECTED)   // laporan yang ditolak tidak dibandingkan
            ->whereBetween('finding_date', [
                $baseDate->copy()->subDays($window)->toDateString(),
                $baseDate->copy()->addDays($window)->toDateString(),
            ])
            ->latest()
            ->limit($cfg['max_candidates'])
            ->get();

        $locTokens  = $this->tokens($report->location);
        $descTokens = $this->tokens($report->description . ' ' . $report->potential_hazard);

        return $candidates
            ->map(function (HazardReport $other) use ($report, $locTokens, $descTokens) {
                $reasons = [];
                $score   = 0;

                // 1. Jarak GPS
                $distance = null;
                if ($report->hasCoordinates() && $other->hasCoordinates()) {
                    $distance = $this->distanceMeters($report->latitude, $report->longitude, $other->latitude, $other->longitude);
                    $points = match (true) {
                        $distance <= 15  => 40,
                        $distance <= 30  => 32,
                        $distance <= 60  => 20,
                        $distance <= 100 => 10,
                        default          => 0,
                    };
                    $score += $points;
                    if ($points > 0) {
                        $reasons[] = 'Jarak ±' . round($distance) . ' m';
                    }
                }

                // 2. Teks lokasi
                $locSim = max(
                    $this->tokenSimilarity($locTokens, $this->tokens($other->location)),
                    $this->charSimilarity($report->location, $other->location),
                );
                $score += (int) round($locSim * 25);
                if ($locSim >= 0.5) {
                    $reasons[] = 'Lokasi mirip ' . round($locSim * 100) . '%';
                }

                // 3. Deskripsi + potensi bahaya
                $descSim = $this->tokenSimilarity($descTokens, $this->tokens($other->description . ' ' . $other->potential_hazard));
                $score += (int) round($descSim * 25);
                if ($descSim >= 0.4) {
                    $reasons[] = 'Deskripsi mirip ' . round($descSim * 100) . '%';
                }

                // 4. Kategori & PIC
                if ((int) $other->hazard_category_id === (int) $report->hazard_category_id) {
                    $score += 5;
                    $reasons[] = 'Kategori sama';
                }
                if ((int) $other->pic_department_id === (int) $report->pic_department_id) {
                    $score += 5;
                    $reasons[] = 'PIC sama';
                }

                // 5. Pelapor yang sama mengirim dua kali
                if ((int) $other->reporter_id === (int) $report->reporter_id
                    && abs($other->created_at->diffInDays($report->created_at)) <= 2) {
                    $score += 10;
                    $reasons[] = 'Pelapor sama';
                }

                return (object) [
                    'report'   => $other,
                    'score'    => min(100, $score),
                    'distance' => $distance,
                    'reasons'  => $reasons,
                ];
            })
            ->filter(fn ($c) => $c->score >= config('hazard.duplicate.min_score'))
            ->sortByDesc('score')
            ->take($limit)
            ->values();
    }

    // Label & warna untuk tampilan
    public static function level(int $score): array
    {
        return match (true) {
            $score >= 70 => ['Sangat mirip', 'border-red-200 bg-red-50 text-red-700'],
            $score >= 50 => ['Mirip', 'border-amber-200 bg-amber-50 text-amber-700'],
            default      => ['Mungkin mirip', 'border-gray-200 bg-gray-50 text-gray-600'],
        };
    }

    // ---------------------------------------------------------------
    // Bantuan
    // ---------------------------------------------------------------

    // "Kabel terkelupas di Gudang-B!" → ['kabel', 'terkelupas', 'gudang']
    private function tokens(?string $text): array
    {
        $text  = mb_strtolower((string) $text);
        $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);

        $words = array_map(function ($w) {
            // buang akhiran -nya (tangganya → tangga), selama kata dasarnya masih cukup panjang
            return (mb_strlen($w) > 5 && str_ends_with($w, 'nya')) ? mb_substr($w, 0, -3) : $w;
        }, $words);

        return array_values(array_unique(array_filter(
            $words,
            fn ($w) => (mb_strlen($w) >= 3 || is_numeric($w)) && ! in_array($w, self::STOPWORDS, true)
        )));
    }

    // Koefisien Dice antar kumpulan kata, toleran salah ketik 1 huruf untuk kata ≥ 5 huruf
    private function tokenSimilarity(array $a, array $b): float
    {
        if (! $a || ! $b) {
            return 0.0;
        }

        $matches = 0;
        foreach ($a as $wa) {
            foreach ($b as $wb) {
                if ($wa === $wb || (mb_strlen($wa) >= 5 && mb_strlen($wb) >= 5 && levenshtein($wa, $wb) <= 1)) {
                    $matches++;
                    break;
                }
            }
        }

        return min(1.0, (2 * $matches) / (count($a) + count($b)));
    }

    // Kemiripan huruf per huruf untuk teks pendek (lokasi), 0–1
    private function charSimilarity(?string $a, ?string $b): float
    {
        $a = trim(preg_replace('/\s+/', ' ', mb_strtolower((string) $a)));
        $b = trim(preg_replace('/\s+/', ' ', mb_strtolower((string) $b)));

        if ($a === '' || $b === '') {
            return 0.0;
        }

        $max = max(strlen($a), strlen($b));

        return 1 - (levenshtein($a, $b) / $max);
    }

    // Jarak dua titik (meter), rumus haversine
    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $h    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $r * asin(min(1, sqrt($h)));
    }
}