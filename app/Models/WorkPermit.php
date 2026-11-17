<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'permit_number', 'type', 'status',
    'applicant_id', 'applicant_name', 'applicant_position', 'applicant_department_id',
    'executor_type', 'contractor_company_id', 'owner_department_id',
    'project_name', 'location', 'latitude', 'longitude',
    'supervisor_name', 'checker_id', 'worker_count', 'tools', 'fire_watch_name',
    'work_types', 'work_type_other', 'details',
    'start_date', 'end_date', 'start_time', 'end_time',
    'work_finished_at', 'post_watch_until', 'final_condition',
    'last_note', 'submitted_at', 'activated_at', 'closed_at', 'last_reminded_at',
])]
class WorkPermit extends Model
{
    use SoftDeletes;

    // ---------------------------------------------------------------
    // Status
    // ---------------------------------------------------------------
    public const STATUS_RETURNED               = 'returned';               // dikembalikan untuk revisi
    public const STATUS_PENDING_CHECK          = 'pending_check';          // menunggu diperiksa atasan pengaju
    public const STATUS_PENDING_APPROVAL       = 'pending_approval';       // menunggu approval QHSE
    public const STATUS_ACTIVE                 = 'active';                 // berlaku
    public const STATUS_STOPPED                = 'stopped';                // dihentikan (stop work)
    public const STATUS_PENDING_CLOSE_CHECK    = 'pending_close_check';    // close-out: menunggu diperiksa atasan
    public const STATUS_PENDING_CLOSE_APPROVAL = 'pending_close_approval'; // close-out: menunggu QHSE
    public const STATUS_CLOSED                 = 'closed';
    public const STATUS_EXPIRED                = 'expired';                // lewat masa berlaku, belum close-out

    public const STATUSES = [
        self::STATUS_RETURNED               => ['label' => 'Dikembalikan',           'class' => 'bg-orange-50 text-orange-700 border-orange-200'],
        self::STATUS_PENDING_CHECK          => ['label' => 'Menunggu Pemeriksaan',   'class' => 'bg-gray-100 text-gray-700 border-gray-200'],
        self::STATUS_PENDING_APPROVAL       => ['label' => 'Menunggu Approval QHSE', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
        self::STATUS_ACTIVE                 => ['label' => 'Aktif',                  'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        self::STATUS_STOPPED                => ['label' => 'Dihentikan',             'class' => 'bg-red-50 text-red-700 border-red-200'],
        self::STATUS_PENDING_CLOSE_CHECK    => ['label' => 'Close-out: Pemeriksaan', 'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        self::STATUS_PENDING_CLOSE_APPROVAL => ['label' => 'Close-out: QHSE',        'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        self::STATUS_CLOSED                 => ['label' => 'Closed',                 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
        self::STATUS_EXPIRED                => ['label' => 'Kedaluwarsa',            'class' => 'bg-red-600 text-white border-red-600'],
    ];

    // Status yang masih boleh diubah oleh pengaju:
    // dikembalikan untuk revisi, atau sudah diajukan tapi belum diperiksa atasan
    public const EDITABLE_STATUSES = [self::STATUS_RETURNED, self::STATUS_PENDING_CHECK];

    // Status permit lain yang boleh dikaitkan (mis. permit Pekerjaan Panas untuk butir 8 Ketinggian)
    public const LINKABLE_STATUSES = [
        self::STATUS_PENDING_CHECK, self::STATUS_PENDING_APPROVAL, self::STATUS_ACTIVE, self::STATUS_STOPPED,
    ];

    // Cache permit terkait supaya tidak query berulang
    private ?WorkPermit $linkedPermitCache = null;
    private bool $linkedPermitLoaded = false;

    // Kotak tanda tangan di form: [fase][kotak] => action di work_permit_logs
    // "Disetujui" dan "Diketahui" sama-sama diisi oleh approval QHSE.
    public const SIGNATURES = [
        'open'  => ['checked' => 'checked',       'approved' => 'approved',       'acknowledged' => 'approved'],
        'close' => ['checked' => 'close_checked', 'approved' => 'close_approved', 'acknowledged' => 'close_approved'],
    ];

    protected function casts(): array
    {
        return [
            'work_types'       => 'array',
            'details'          => 'array',
            'latitude'         => 'float',
            'longitude'        => 'float',
            'worker_count'     => 'integer',
            'start_date'       => 'date',
            'end_date'         => 'date',
            'work_finished_at' => 'datetime',
            'post_watch_until' => 'datetime',
            'submitted_at'     => 'datetime',
            'activated_at'     => 'datetime',
            'closed_at'        => 'datetime',
            'last_reminded_at' => 'datetime',
        ];
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------
    public function applicant()        { return $this->belongsTo(User::class, 'applicant_id'); }
    public function applicantDepartment() { return $this->belongsTo(Department::class, 'applicant_department_id'); }
    public function checker()          { return $this->belongsTo(User::class, 'checker_id'); }
    public function contractor()       { return $this->belongsTo(Company::class, 'contractor_company_id'); }
    public function ownerDepartment()  { return $this->belongsTo(Department::class, 'owner_department_id'); }
    public function checks()           { return $this->hasMany(WorkPermitCheck::class)->orderBy('id'); }
    public function gasTests()         { return $this->hasMany(WorkPermitGasTest::class)->orderBy('tested_at'); }
    public function dailyLogs()        { return $this->hasMany(WorkPermitDailyLog::class)->orderBy('work_date'); }
    public function files()            { return $this->hasMany(WorkPermitFile::class); }

    // Urut waktu dulu: di TiDB id tidak selalu berurutan sesuai waktu
    public function logs()             { return $this->hasMany(WorkPermitLog::class)->orderBy('created_at')->orderBy('id'); }

    public function certificate()      { return $this->hasOne(WorkPermitCertificate::class); }

    public function jsaFile()
    {
        return $this->hasOne(WorkPermitFile::class)->where('kind', WorkPermitFile::KIND_JSA)->latestOfMany();
    }

    // ---------------------------------------------------------------
    // Konfigurasi jenis permit (config/permits.php)
    // ---------------------------------------------------------------
    public static function typeConfig(string $type): array
    {
        return config("permits.types.{$type}") ?? abort(404, 'Jenis permit tidak dikenal.');
    }

    public function config(): array
    {
        return static::typeConfig($this->type);
    }

    public function typeLabel(): string
    {
        return $this->config()['label'];
    }

    // Teks / contoh isian khusus jenis permit (config 'texts')
    public function text(string $key, string $default = ''): string
    {
        return $this->config()['texts'][$key] ?? $default;
    }

    // Label petugas pengawas di lapangan (Fire Watch / Pengawas)
    public static function watcherConfig(array $config): array
    {
        return ($config['watcher'] ?? []) + [
            'label' => 'Nama Fire Watch', 'short' => 'Fire watch', 'placeholder' => '',
        ];
    }

    public function watcher(): array
    {
        return static::watcherConfig($this->config());
    }

    // Lama pengawasan setelah pekerjaan selesai (menit); 0 = tidak ada
    public function postWatchMinutes(): int
    {
        return (int) ($this->config()['post_watch_minutes'] ?? 0);
    }

    // Butir checklist pertama dengan 'requires' tertentu: [key, item] atau null
    public static function itemRequiring(array $config, string $requires): ?array
    {
        foreach ($config['checklist'] as $key => $item) {
            if (($item['requires'] ?? null) === $requires) {
                return [$key, $item];
            }
        }

        return null;
    }

    // Butir checklist yang mengaitkan permit lain: [key, item] atau null
    public static function linkedItemOf(array $config): ?array
    {
        return static::itemRequiring($config, 'linked_permit');
    }

    // Batas aman gas untuk jenis ini (Ruang Terbatas punya batas sendiri)
    public static function gasLimitsFor(array $config): array
    {
        return $config['gas_limits'] ?? config('permits.gas_limits');
    }

    public function gasLimits(): array
    {
        return static::gasLimitsFor($this->config());
    }

    // JSA wajib kalau checklist jenis ini punya butir JSA; selain itu opsional
    public static function jsaRequiredFor(array $config): bool
    {
        return static::itemRequiring($config, 'jsa') !== null;
    }

    // Pengukuran gas saat pengajuan (bukan pengukuran ulang harian)
    public function openGasTest(): ?WorkPermitGasTest
    {
        return $this->gasTests->whereNull('work_permit_daily_log_id')->last();
    }

    // Wajib ukur gas ulang setiap "Aktifkan Hari Ini"
    public function needsDailyGasTest(): bool
    {
        return (bool) ($this->config()['daily_gas_test'] ?? false);
    }

    // "Keterangan lain yang diperlukan"
    public function extraNotes(): array
    {
        return $this->details['extra_notes'] ?? [];
    }

    // Permit lain yang dikaitkan (mis. permit Pekerjaan Panas)
    public function linkedPermit(): ?WorkPermit
    {
        if (! $this->linkedPermitLoaded) {
            $id = $this->details['linked_permit_id'] ?? null;
            $this->linkedPermitCache  = $id ? static::withTrashed()->find($id) : null;
            $this->linkedPermitLoaded = true;
        }

        return $this->linkedPermitCache;
    }

    // Alasan permit terkait belum memenuhi syarat untuk hari ini; null = aman / tidak ada
    public function linkedPermitBlocker(): ?string
    {
        $linked = $this->linkedPermit();
        if (! $linked) {
            return null;
        }

        $ok = ! $linked->trashed()
            && $linked->status === self::STATUS_ACTIVE
            && $linked->dayNumberFor(today()) !== null;

        return $ok ? null : "Permit {$linked->typeLabel()} terkait ({$linked->permit_number}) belum Aktif atau tidak berlaku hari ini. Aktifkan permit tersebut dulu.";
    }

    // ---------------------------------------------------------------
    // Tampilan
    // ---------------------------------------------------------------
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->status]['class'] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    }

    public function applicantPositionLabel(): string
    {
        return User::POSITIONS[$this->applicant_position]['label'] ?? '-';
    }

    public function isEditable(): bool
    {
        return in_array($this->status, self::EDITABLE_STATUSES, true);
    }

    // Label tombol ubah untuk pengaju
    public function editLabel(): string
    {
        return $this->status === self::STATUS_RETURNED ? 'Revisi & Ajukan Ulang' : 'Edit Pengajuan';
    }

    // ---------------------------------------------------------------
    // Pemeriksa (atasan pengaju)
    // ---------------------------------------------------------------

    // Calon pemeriksa untuk dropdown: departemen sama, jabatan lebih tinggi dari pengaju, minimal Supervisor
    public static function checkerCandidates(?int $departmentId, ?string $applicantPosition, ?int $excludeUserId = null)
    {
        $applicantRank = User::positionRank($applicantPosition);
        $positions = collect(User::POSITIONS)
            ->filter(fn ($p) => $p['rank'] > $applicantRank && $p['rank'] >= User::PERMIT_CHECKER_MIN_RANK)
            ->keys()
            ->all();

        if (! $departmentId || ! $positions) {
            return collect();
        }

        return User::with('department')
            ->where('department_id', $departmentId)
            ->whereIn('position', $positions)
            ->when($excludeUserId, fn ($q) => $q->where('id', '!=', $excludeUserId))
            ->orderBy('name')
            ->get()
            ->sortBy(fn (User $u) => User::positionRank($u->position)) // Supervisor dulu, lalu Section Head
            ->values();
    }

    // Boleh memeriksa permit ini?
    //   - pemeriksa yang dipilih pengaju, atau
    //   - atasan si pemeriksa di departemen yang sama (mengambil alih kalau pemeriksa berhalangan)
    // Permit lama tanpa pemeriksa terpilih: semua atasan yang memenuhi syarat.
    public function canBeCheckedBy(User $user): bool
    {
        if ((int) $this->applicant_id === (int) $user->id
            || ! $user->canCheckPermitFrom($this->applicant_position, $this->applicant_department_id)) {
            return false;
        }

        if (! $this->checker_id || (int) $this->checker_id === (int) $user->id) {
            return true;
        }

        $checker = $this->checker;

        // Akun pemeriksa sudah tidak ada / pindah departemen → atasan mana pun yang memenuhi syarat
        if (! $checker || (int) $checker->department_id !== (int) $this->applicant_department_id) {
            return true;
        }

        return User::positionRank($user->position) > User::positionRank($checker->position);
    }

    // Yang memeriksa sekarang adalah atasan pengganti, bukan pemeriksa yang dipilih
    public function isTakeoverBy(User $user): bool
    {
        return $this->checker_id && (int) $this->checker_id !== (int) $user->id && $this->canBeCheckedBy($user);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function workTypeLabels(): array
    {
        $options = $this->config()['work_types'];

        return collect($this->work_types ?? [])
            ->map(fn ($k) => $k === 'lain' && $this->work_type_other ? 'Lain-lain: ' . $this->work_type_other : ($options[$k] ?? $k))
            ->all();
    }

    // Jumlah hari berlaku (1–7)
    public function durationDays(): int
    {
        return (int) $this->start_date->diffInDays($this->end_date) + 1;
    }

    // Day ke berapa untuk tanggal tertentu, null kalau di luar masa berlaku
    public function dayNumberFor(Carbon $date): ?int
    {
        $date = $date->copy()->startOfDay();
        if ($date->lt($this->start_date) || $date->gt($this->end_date)) {
            return null;
        }

        return (int) $this->start_date->diffInDays($date) + 1;
    }

    // Batas jam kerja hari ini (Carbon), mis. hari ini 17:00
    public function endTimeToday(): Carbon
    {
        return today()->setTimeFromTimeString((string) $this->end_time);
    }

    // Log aktivasi untuk tanggal tertentu (default hari ini)
    public function dailyLogFor(?Carbon $date = null): ?WorkPermitDailyLog
    {
        $date = ($date ?? today())->toDateString();
        $logs = $this->relationLoaded('dailyLogs') ? $this->dailyLogs : $this->dailyLogs()->get();

        return $logs->first(fn ($l) => $l->work_date->toDateString() === $date);
    }

    // Alasan kenapa belum bisa "Aktifkan Hari Ini"; null = bisa
    public function activationBlocker(): ?string
    {
        return match (true) {
            $this->status !== self::STATUS_ACTIVE      => 'Permit belum / tidak dalam status Aktif.',
            $this->dayNumberFor(today()) === null      => 'Hari ini di luar masa berlaku permit.',
            $this->dailyLogFor() !== null              => 'Permit sudah diaktifkan hari ini.',
            now()->gt($this->endTimeToday())           => 'Jam kerja permit hari ini sudah lewat.',
            $this->linkedPermitBlocker() !== null      => $this->linkedPermitBlocker(),
            default                                    => null,
        };
    }

    // Boleh mengisi close-out (pekerjaan selesai)
    public function canFinish(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_EXPIRED], true)
            && $this->dailyLogs()->exists();
    }

    // Tanda tangan digital (log terakhir untuk action tsb setelah pengajuan terakhir)
    public function signature(string $action): ?WorkPermitLog
    {
        $logs = $this->relationLoaded('logs') ? $this->logs : $this->logs()->get();

        $lastSubmit = $logs->where('action', 'submitted')->last();
        $lastFinish = $logs->where('action', 'finished')->last();

        // Tanda tangan fase "open" hanya dihitung setelah pengajuan terakhir,
        // fase "close" hanya setelah pekerjaan dinyatakan selesai terakhir.
        $after = str_starts_with($action, 'close_') ? $lastFinish : $lastSubmit;

        return $logs
            ->filter(fn ($l) => $l->action === $action && (! $after || $l->created_at->gte($after->created_at)))
            ->last();
    }

    // ---------------------------------------------------------------
    // Scope
    // ---------------------------------------------------------------
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // ---------------------------------------------------------------
    // Nomor permit: PTW/IBT/HW/2026/11/0001 (urutan reset tiap bulan per jenis)
    // ---------------------------------------------------------------
    public static function generateNumber(string $type): string
    {
        $prefix = config('permits.number_prefix', 'PTW/IBT') . '/' . static::typeConfig($type)['code'] . '/' . now()->format('Y/m') . '/';

        $last = static::withTrashed()
            ->where('permit_number', 'like', $prefix . '%')
            ->orderByDesc('permit_number')
            ->value('permit_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}