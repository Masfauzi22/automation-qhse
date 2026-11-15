<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'report_number',
    'reporter_id', 'reporter_name', 'reporter_employee_number', 'reporter_company_id', 'reporter_department_id',
    'finding_date', 'hazard_category_id', 'hazard_priority_id',
    'location', 'latitude', 'longitude', 'location_accuracy', 'description', 'potential_hazard', 'recommendation', 'pic_department_id',
    'status',
    'validated_by', 'validated_at', 'rejection_reason', 'duplicate_of_id', 'validation_note',
    'due_date', 'original_due_date', 'extension_count',
    'closing_submitted_by', 'closing_submitted_at', 'closing_note', 'verification_note',
    'closed_by', 'closed_at',
    'last_reminded_at',
])]
class HazardReport extends Model
{
    use SoftDeletes;

    // ---------------------------------------------------------------
    // Status laporan
    // ---------------------------------------------------------------
    public const STATUS_PENDING_VALIDATION   = 'pending_validation';   // baru disubmit, menunggu HSE
    public const STATUS_REJECTED             = 'rejected';             // ditolak HSE
    public const STATUS_OPEN                 = 'open';                 // divalidasi, menunggu PIC
    public const STATUS_IN_PROGRESS          = 'in_progress';          // PIC sudah update progres / minta perpanjangan
    public const STATUS_PENDING_VERIFICATION = 'pending_verification'; // PIC sudah upload bukti, menunggu cek HSE
    public const STATUS_CLOSED               = 'closed';               // selesai

    public const STATUSES = [
        self::STATUS_PENDING_VALIDATION   => ['label' => 'Menunggu Validasi',   'class' => 'bg-gray-100 text-gray-700 border-gray-200'],
        self::STATUS_REJECTED             => ['label' => 'Ditolak',             'class' => 'bg-red-50 text-red-700 border-red-200'],
        self::STATUS_OPEN                 => ['label' => 'Open - Follow Up PIC', 'class' => 'bg-amber-50 text-amber-700 border-amber-200'],
        self::STATUS_IN_PROGRESS          => ['label' => 'Dalam Proses',        'class' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
        self::STATUS_PENDING_VERIFICATION => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
        self::STATUS_CLOSED               => ['label' => 'Closed',              'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
    ];

    protected function casts(): array
    {
        return [
            'finding_date'         => 'date',
            'latitude'             => 'float',
            'longitude'            => 'float',
            'location_accuracy'    => 'integer',
            'due_date'             => 'date',
            'original_due_date'    => 'date',
            'extension_count'      => 'integer',
            'validated_at'         => 'datetime',
            'closing_submitted_at' => 'datetime',
            'closed_at'            => 'datetime',
            'last_reminded_at'     => 'datetime',
        ];
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reporterCompany()
    {
        return $this->belongsTo(Company::class, 'reporter_company_id');
    }

    public function reporterDepartment()
    {
        return $this->belongsTo(Department::class, 'reporter_department_id');
    }

    public function category()
    {
        return $this->belongsTo(HazardCategory::class, 'hazard_category_id');
    }

    public function priority()
    {
        return $this->belongsTo(HazardPriority::class, 'hazard_priority_id');
    }

    public function picDepartment()
    {
        return $this->belongsTo(Department::class, 'pic_department_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function closingSubmitter()
    {
        return $this->belongsTo(User::class, 'closing_submitted_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Laporan asli, kalau laporan ini ditolak karena duplikat
    public function duplicateOf()
    {
        return $this->belongsTo(HazardReport::class, 'duplicate_of_id');
    }

    // Laporan lain yang ditandai sebagai duplikat dari laporan ini
    public function duplicates()
    {
        return $this->hasMany(HazardReport::class, 'duplicate_of_id');
    }

    public function photos()
    {
        return $this->hasMany(HazardReportPhoto::class);
    }

    public function findingPhotos()
    {
        return $this->hasMany(HazardReportPhoto::class)->where('type', HazardReportPhoto::TYPE_FINDING);
    }

    public function closingPhotos()
    {
        return $this->hasMany(HazardReportPhoto::class)->where('type', HazardReportPhoto::TYPE_CLOSING);
    }

    // Riwayat update progres / perpanjangan, urut dari yang paling lama.
    // Urut pakai created_at dulu: di TiDB, id auto increment tidak selalu berurutan sesuai waktu.
    public function updates()
    {
        return $this->hasMany(HazardReportUpdate::class)->orderBy('created_at')->orderBy('id');
    }

    // ---------------------------------------------------------------
    // Bantuan tampilan
    // ---------------------------------------------------------------
    public function statusLabel(): string
    {
        return self::STATUSES[$this->status]['label'] ?? $this->status;
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->status]['class'] ?? 'bg-gray-100 text-gray-700 border-gray-200';
    }

    public function rejectionReasonLabel(): ?string
    {
        return $this->rejection_reason
            ? (config('hazard.reject_reasons')[$this->rejection_reason] ?? $this->rejection_reason)
            : null;
    }

    // Status saat PIC masih harus bertindak
    public const FOLLOW_UP_STATUSES = [self::STATUS_OPEN, self::STATUS_IN_PROGRESS];

    // Lewat batas waktu dan belum selesai
    public function isOverdue(): bool
    {
        return in_array($this->status, self::FOLLOW_UP_STATUSES, true)
            && $this->due_date !== null
            && $this->due_date->isBefore(today());
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function isExtended(): bool
    {
        return $this->extension_count > 0;
    }

    // Masih boleh minta perpanjangan
    public function canExtend(): bool
    {
        return $this->extension_count < config('hazard.max_extensions', 2);
    }

    // ---------------------------------------------------------------
    // Scope query
    // ---------------------------------------------------------------
    // HazardReport::overdue()->get() → laporan open / dalam proses yang lewat batas waktu
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereIn('status', self::FOLLOW_UP_STATUSES)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', today());
    }

    // ---------------------------------------------------------------
    // Statistik laporan per pelapor
    // ---------------------------------------------------------------
    // HazardReport::reporterStats($userId) → total, bulan ini, valid, ditolak, dst.
    public static function reporterStats(?int $userId): array
    {
        $row = static::query()
            ->where('reporter_id', $userId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as this_month,
                SUM(CASE WHEN created_at >= ? AND status <> ? THEN 1 ELSE 0 END) as this_month_counted,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as closed
            ', [
                now()->startOfMonth()->toDateTimeString(),
                now()->startOfMonth()->toDateTimeString(), self::STATUS_REJECTED,
                self::STATUS_REJECTED,
                self::STATUS_PENDING_VALIDATION,
                self::STATUS_CLOSED,
            ])
            ->first();

        $total    = (int) ($row->total ?? 0);
        $rejected = (int) ($row->rejected ?? 0);
        $pending  = (int) ($row->pending ?? 0);
        $valid    = $total - $rejected - $pending;   // sudah divalidasi HSE dan tidak ditolak
        $target   = (int) config('hazard.monthly_target', 0);
        $counted  = (int) ($row->this_month_counted ?? 0);

        return [
            'total'              => $total,
            'this_month'         => (int) ($row->this_month ?? 0),
            'this_month_counted' => $counted,          // bulan ini, tanpa yang ditolak (untuk target)
            'valid'              => $valid,
            'rejected'           => $rejected,
            'pending'            => $pending,
            'closed'             => (int) ($row->closed ?? 0),
            // Persen laporan diterima dari yang sudah diputuskan HSE
            'acceptance_rate'    => ($valid + $rejected) > 0 ? (int) round($valid / ($valid + $rejected) * 100) : null,
            'target'             => $target,
            'target_met'         => $target > 0 && $counted >= $target,
        ];
    }

    // ---------------------------------------------------------------
    // Nomor laporan otomatis: HZR/2026/11/0001 (urutan reset tiap bulan)
    // ---------------------------------------------------------------
    public static function generateNumber(): string
    {
        $prefix = config('hazard.number_prefix', 'HZR') . '/' . now()->format('Y/m') . '/';

        $last = static::withTrashed()
            ->where('report_number', 'like', $prefix . '%')
            ->orderByDesc('report_number')
            ->value('report_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}