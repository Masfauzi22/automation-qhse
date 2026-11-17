<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Data dashboard Hazard Report dari Google Sheet "Hazard Report 2026",
 * tab "DASHBOARD HAZARD REPORT 2026".
 *
 * Urutan sumber data:
 *   1. Cache (10 menit)
 *   2. Google Sheets (live)
 *   3. Data terakhir yang berhasil dibaca (kalau Google sedang tidak bisa diakses)
 *   4. Snapshot manual di bawah (kalau belum pernah berhasil sama sekali)
 *
 * Posisi sel mengikuti tab tersebut. Kalau tabel di spreadsheet dipindah,
 * cukup ubah konstanta RANGE_* di bawah.
 */
class HazardSheetDashboard
{
    // Ringkasan laporan masuk (B5:E13)
    private const RANGE_INTAKE     = 'B5:E13';
    // Follow up hazard report per bulan: Bulan | Open | Close | Diterima | Di-close bulan itu | %
    private const RANGE_MONTHLY    = 'B34:G45';
    // Progress follow up per PIC: Departemen | Open | Close | Total | %
    private const RANGE_PIC        = 'I34:M48';
    // Unsafe condition / action per bulan: Bulan | KTA | % | TTA | % | Total
    private const RANGE_UNSAFE     = 'O34:T45';
    // Department finding: Divisi | Target per bulan | Januari..Desember
    private const RANGE_DIVISIONS  = 'E55:R69';
    // Jadwal follow up PIC: Minggu | Departemen
    private const RANGE_SCHEDULE   = 'T55:U63';

    private const CACHE_KEY      = 'hazard_sheet_dashboard';
    private const LAST_GOOD_KEY  = 'hazard_sheet_dashboard_last_good';
    private const FAILED_KEY     = 'hazard_sheet_dashboard_failed';

    public function __construct(private GoogleSheets $sheets) {}

    public function get(bool $refresh = false): array
    {
        if ($refresh) {
            Cache::forget(self::CACHE_KEY);
            Cache::forget(self::FAILED_KEY);
        }

        if ($this->isValid($cached = Cache::get(self::CACHE_KEY))) {
            return $this->hydrate($cached);
        }

        // Baru saja gagal → langsung pakai cadangan, jangan bikin halaman menunggu lagi
        if (! Cache::has(self::FAILED_KEY)) {
            try {
                $data = $this->fetch();
                Cache::put(self::CACHE_KEY, $data, now()->addMinutes(config('google.hazard_sheet.cache_minutes', 10)));
                Cache::forever(self::LAST_GOOD_KEY, $data);

                return $this->hydrate($data);
            } catch (Throwable $e) {
                Log::warning('Dashboard hazard: gagal membaca Google Sheets. ' . $e->getMessage());
                Cache::put(self::FAILED_KEY, $e->getMessage(), now()->addMinutes(config('google.hazard_sheet.retry_after_failure_minutes', 2)));
            }
        }

        $error = Cache::get(self::FAILED_KEY) ?: 'Google Sheets tidak bisa diakses.';

        if ($this->isValid($lastGood = Cache::get(self::LAST_GOOD_KEY))) {
            return $this->hydrate(array_merge($lastGood, ['source' => 'last_good', 'error' => $error]));
        }

        return $this->hydrate(array_merge($this->build($this->snapshot()), ['source' => 'snapshot', 'error' => $error, 'updated_at' => null]));
    }

    // Cache hanya menyimpan array biasa (Laravel menolak menyimpan objek di cache).
    // Di sini array bulanan & departemen diubah lagi jadi Collection untuk view.
    private function hydrate(array $data): array
    {
        $data['monthly']     = collect($data['monthly']);
        $data['departments'] = collect($data['departments']);

        return $data;
    }

    // Data cache lama / rusak dianggap tidak ada
    private function isValid(mixed $data): bool
    {
        return is_array($data) && is_array($data['monthly'] ?? null) && is_array($data['departments'] ?? null);
    }

    // =================================================================
    // Baca dari Google Sheets
    // =================================================================

    private function fetch(): array
    {
        $tab   = config('google.hazard_sheet.tab');
        $quote = fn (string $range) => "'" . str_replace("'", "''", $tab) . "'!{$range}";

        $keys = [
            'intake'    => self::RANGE_INTAKE,
            'monthly'   => self::RANGE_MONTHLY,
            'pic'       => self::RANGE_PIC,
            'unsafe'    => self::RANGE_UNSAFE,
            'divisions' => self::RANGE_DIVISIONS,
            'schedule'  => self::RANGE_SCHEDULE,
        ];
        $ranges = array_map($quote, $keys);
        $raw    = $this->sheets->batchGet(config('google.hazard_sheet.spreadsheet_id'), array_values($ranges));
        $get    = fn (string $key) => $raw[$ranges[$key]] ?? [];

        // --- Ringkasan (kolom B = label, kolom E = nilai → indeks 0 dan 3)
        $intake = [];
        foreach ($get('intake') as $row) {
            $label = strtoupper(trim((string) ($row[0] ?? '')));
            if ($label !== '') {
                $intake[$label] = $row[3] ?? null;
            }
        }
        // Baris "DITOLAK– xxx" = alasan penolakan; baris "DITOLAK" saja = totalnya
        $rejectReasons = collect($intake)
            ->filter(fn ($v, $label) => str_starts_with($label, 'DITOLAK') && $label !== 'DITOLAK')
            ->mapWithKeys(fn ($v, $label) => [$this->rejectLabel($label) => $this->int($v)])
            ->filter(fn ($v, $label) => $label !== '')
            ->all();

        // --- Bulanan + unsafe (baris sama urutan bulannya)
        $unsafe  = $get('unsafe');
        $monthly = [];
        foreach ($get('monthly') as $i => $row) {
            $bulan = trim((string) ($row[0] ?? ''));
            if ($bulan === '') {
                continue;
            }
            $monthly[] = [
                $bulan,
                $this->int($row[3] ?? 0),           // total diterima
                $this->int($row[2] ?? 0),           // finding is close
                $this->int($row[4] ?? 0),           // di-close pada bulan tersebut
                $this->int($unsafe[$i][1] ?? 0),    // unsafe condition (KTA)
                $this->int($unsafe[$i][3] ?? 0),    // unsafe action (TTA)
                $this->int($row[1] ?? 0),           // finding still open (versi spreadsheet)
            ];
        }

        // --- Per PIC
        $departments = [];
        foreach ($get('pic') as $row) {
            $nama = trim((string) ($row[0] ?? ''));
            if ($nama === '' || strtoupper($nama) === 'TOTAL') {
                continue;
            }
            $departments[] = [$nama, $this->int($row[1] ?? 0), $this->int($row[2] ?? 0)];
        }

        // --- Department finding vs target
        $divisions = [];
        foreach ($get('divisions') as $row) {
            $nama = trim((string) ($row[0] ?? ''));
            if ($nama === '') {
                continue;
            }
            $divisions[] = [
                'nama'     => $nama,
                'target'   => is_numeric($row[1] ?? null) ? (int) $row[1] : null,
                'bulan'    => array_map(fn ($i) => $this->intOrNull($row[$i] ?? null), range(2, 13)),
                'is_total' => str_starts_with(strtoupper($nama), 'TOTAL'),
            ];
        }

        // --- Jadwal follow up PIC (W1..W4) + catatan meeting
        $schedule = [];
        $notes    = [];
        foreach ($get('schedule') as $row) {
            $a = trim((string) ($row[0] ?? ''));
            $b = trim((string) ($row[1] ?? ''));
            if (preg_match('/^W\d$/i', $a)) {
                $schedule[] = ['minggu' => strtoupper($a), 'pic' => $b];
            } elseif ($a !== '' && ! str_starts_with(strtoupper($a), 'NOTE') && $b !== '') {
                $notes[] = ['siapa' => $a, 'kapan' => $b];
            }
        }

        return array_merge($this->build([
            'monthly'     => $monthly,
            'departments' => $departments,
            'intake'      => [
                'masuk'    => $this->intOrNull($intake['TOTAL LAPORAN MASUK'] ?? null),
                'diterima' => $this->intOrNull($intake['DITERIMA'] ?? null),
                'ditolak'  => $this->intOrNull($intake['DITOLAK'] ?? null),
                'alasan'   => $rejectReasons,
            ],
            'divisions'   => $divisions,
            'schedule'    => $schedule,
            'notes'       => $notes,
        ]), ['source' => 'google', 'error' => null, 'updated_at' => now()->toIso8601String()]);
    }

    // =================================================================
    // Susun struktur yang dipakai view (sama dengan versi manual sebelumnya)
    // =================================================================

    private function build(array $in): array
    {
        $pct = fn (int $part, int $whole) => $whole > 0 ? round($part / $whole * 100, 1) : null;

        $monthly = collect($in['monthly'])->map(fn ($r) => [
            'bulan'            => $r[0],
            'diterima'         => $r[1],
            'close'            => $r[2],
            'open'             => $r[6] ?? max($r[1] - $r[2], 0),
            'close_bulan_ini'  => $r[3],
            'pct_close'        => $pct($r[2], $r[1]),
            'unsafe_condition' => $r[4],
            'unsafe_action'    => $r[5],
        ]);

        // Diurutkan dari temuan open terbanyak, supaya yang perlu dikejar ada di atas
        $departments = collect($in['departments'])->map(fn ($r) => [
            'nama'  => $r[0],
            'open'  => $r[1],
            'close' => $r[2],
            'total' => $r[1] + $r[2],
            'pct'   => $pct($r[2], $r[1] + $r[2]),
        ])->sortByDesc('open')->values();

        $totalDiterima  = $monthly->sum('diterima');
        $totalClose     = $monthly->sum('close');
        $totalOpen      = $monthly->sum('open');
        $totalCondition = $monthly->sum('unsafe_condition');
        $totalAction    = $monthly->sum('unsafe_action');

        $intake = $in['intake'] ?? [];

        return [
            'tahun'        => config('google.hazard_sheet.tahun', 2026),
            'target_close' => config('google.hazard_sheet.target_close', 85),
            // Disimpan sebagai array supaya aman di cache; diubah jadi Collection di hydrate()
            'monthly'      => $monthly->values()->all(),
            'departments'  => $departments->all(),
            'summary'      => [
                'diterima'         => $totalDiterima,
                'close'            => $totalClose,
                'open'             => $totalOpen,
                'close_bulan_ini'  => $monthly->sum('close_bulan_ini'),
                'pct_close'        => $pct($totalClose, $totalDiterima),
                'unsafe_condition' => $totalCondition,
                'unsafe_action'    => $totalAction,
                'pct_condition'    => $pct($totalCondition, $totalCondition + $totalAction),
                'pct_action'       => $pct($totalAction, $totalCondition + $totalAction),
            ],
            // Data tambahan dari spreadsheet
            'intake'       => [
                'masuk'    => $intake['masuk'] ?? null,
                'diterima' => $intake['diterima'] ?? $totalDiterima,
                'ditolak'  => $intake['ditolak'] ?? null,
                'alasan'   => $intake['alasan'] ?? [],
            ],
            'divisions'    => $in['divisions'] ?? [],
            'schedule'     => $in['schedule'] ?? [],
            'notes'        => $in['notes'] ?? [],
        ];
    }

    // "DITOLAK– DUPLIKAT" → "Duplikat"
    private function rejectLabel(string $label): string
    {
        $text = trim(preg_replace('/^DITOLAK\s*[-–—]\s*/u', '', $label));

        return $text === '' ? '' : ucfirst(mb_strtolower($text));
    }

    private function int(mixed $v): int
    {
        return is_numeric($v) ? (int) round((float) $v) : 0;
    }

    private function intOrNull(mixed $v): ?int
    {
        return is_numeric($v) ? (int) round((float) $v) : null;
    }

    // =================================================================
    // Snapshot manual (cadangan kalau Google belum pernah berhasil dibaca)
    // =================================================================

    private function snapshot(): array
    {
        return [
            // [bulan, diterima, close, di-close bulan itu, unsafe condition, unsafe action, open]
            'monthly' => [
                ['Januari',   114, 110,  20,  99, 15,  4],
                ['Februari',  117, 113,  48,  87, 30,  4],
                ['Maret',     134, 124,  32, 127,  7, 10],
                ['April',     163, 157,  39, 112, 51,  6],
                ['Mei',       156, 133, 194, 144, 12, 23],
                ['Juni',      151, 133,  91, 137, 14, 18],
                ['Juli',      157, 122, 204, 147, 10, 35],
                ['Agustus',   107,  90, 165,  86, 21, 17],
                ['September', 145,  91, 113, 127, 18, 54],
                ['Oktober',   137,  60, 179, 117, 20, 77],
                ['November',   48,  14,  62,  45,  3, 34],
                ['Desember',    0,   0,   0,   0,  0,  0],
            ],
            // [departemen, open, close]
            'departments' => [
                ['BC/MTC', 16, 66], ['SHIFT/MTC', 13, 33], ['ELEKTRIK/MTC', 22, 129], ['FABRIKASI/MTC', 49, 175],
                ['PLANNER/MTC', 3, 12], ['MEWS/MTC', 34, 49], ['INFRA/ MTC', 26, 124], ['OPERATION', 20, 94],
                ['QHSE', 17, 102], ['SFC/GA', 6, 76], ['WH/LOGISTIC', 2, 4], ['TCU', 63, 199],
                ['OTHER', 8, 32], ['FUEL OPERATION', 0, 3], ['ELNUSA', 3, 48],
            ],
            'intake' => [
                'masuk' => 1545, 'diterima' => 1429, 'ditolak' => 116,
                'alasan' => ['Duplikat' => 70, 'Tidak masuk kriteria' => 33, 'Deskripsi tidak jelas' => 7, 'Sudah aman' => 6],
            ],
            'divisions' => [],
            'schedule'  => [
                ['minggu' => 'W1', 'pic' => 'Fabrikasi'],
                ['minggu' => 'W2', 'pic' => 'Infra, MEWS'],
                ['minggu' => 'W3', 'pic' => 'BC, Shift, Planner'],
                ['minggu' => 'W4', 'pic' => 'Elektrik, SFC/GA, Logistic'],
            ],
            'notes' => [],
        ];
    }

    // Untuk tampilan: "diperbarui 23:40"
    public static function updatedLabel(array $data): ?string
    {
        return ! empty($data['updated_at']) ? Carbon::parse($data['updated_at'])->translatedFormat('d M Y, H:i') : null;
    }
}