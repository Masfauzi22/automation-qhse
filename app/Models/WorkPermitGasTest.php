<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['work_permit_id', 'work_permit_daily_log_id', 'o2', 'lel', 'h2s', 'co', 'co2', 'tested_by_name', 'tested_at', 'is_safe'])]
class WorkPermitGasTest extends Model
{
    // Kolom gas yang tersedia di tabel
    public const GASES = ['o2', 'lel', 'h2s', 'co', 'co2'];

    protected function casts(): array
    {
        return [
            'o2'        => 'float',
            'lel'       => 'float',
            'h2s'       => 'float',
            'co'        => 'float',
            'co2'       => 'float',
            'tested_at' => 'datetime',
            'is_safe'   => 'boolean',
        ];
    }

    public function permit()
    {
        return $this->belongsTo(WorkPermit::class, 'work_permit_id');
    }

    public function dailyLog()
    {
        return $this->belongsTo(WorkPermitDailyLog::class, 'work_permit_daily_log_id');
    }

    // Cek satu nilai terhadap batas (default: batas umum di config/permits.php)
    public static function isWithinLimit(string $gas, ?float $value, ?array $limits = null): bool
    {
        if ($value === null) {
            return false;
        }
        $limit = ($limits ?? config('permits.gas_limits'))[$gas] ?? null;
        if (! $limit) {
            return true;
        }

        return ($limit['min'] === null || $value >= $limit['min'])
            && ($limit['max'] === null || $value <= $limit['max']);
    }

    // Daftar gas yang di luar batas, mis. ['lel' => 12.0]
    public static function violations(array $values, ?array $limits = null): array
    {
        $limits ??= config('permits.gas_limits');

        return collect($limits)
            ->keys()
            ->filter(fn ($gas) => ! static::isWithinLimit($gas, isset($values[$gas]) ? (float) $values[$gas] : null, $limits))
            ->mapWithKeys(fn ($gas) => [$gas => $values[$gas] ?? null])
            ->all();
    }
}