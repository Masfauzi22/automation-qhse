<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hazard_report_id', 'user_id', 'type',
    'obstacle', 'temporary_action', 'note',
    'old_due_date', 'new_due_date',
])]
class HazardReportUpdate extends Model
{
    public const TYPE_EXTENSION          = 'extension';          // PIC minta perpanjangan
    public const TYPE_EXTENSION_REJECTED = 'extension_rejected'; // HSE menolak perpanjangan

    protected function casts(): array
    {
        return [
            'old_due_date' => 'date',
            'new_due_date' => 'date',
        ];
    }

    public function report()
    {
        return $this->belongsTo(HazardReport::class, 'hazard_report_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(HazardReportPhoto::class, 'hazard_report_update_id');
    }

    public function isExtension(): bool
    {
        return $this->type === self::TYPE_EXTENSION;
    }
}