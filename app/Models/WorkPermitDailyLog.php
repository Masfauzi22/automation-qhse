<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['work_permit_id', 'day_number', 'work_date', 'user_id', 'user_name', 'confirmations', 'note'])]
class WorkPermitDailyLog extends Model
{
    protected function casts(): array
    {
        return [
            'work_date'     => 'date',
            'confirmations' => 'array',
            'day_number'    => 'integer',
        ];
    }

    public function permit()
    {
        return $this->belongsTo(WorkPermit::class, 'work_permit_id');
    }

    public function photos()
    {
        return $this->hasMany(WorkPermitFile::class, 'work_permit_daily_log_id');
    }
}