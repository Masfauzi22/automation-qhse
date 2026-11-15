<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'safety_campaign_id', 'user_id', 'department_id',
    'first_viewed_at', 'completed_at',
    'first_attempt_score', 'first_attempt_total', 'attempts_count',
    'comment', 'commented_at',
])]
class SafetyCampaignResponse extends Model
{
    protected function casts(): array
    {
        return [
            'first_viewed_at' => 'datetime',
            'completed_at'    => 'datetime',
            'commented_at'    => 'datetime',
            'first_attempt_score' => 'integer',
            'first_attempt_total' => 'integer',
            'attempts_count'      => 'integer',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(SafetyCampaign::class, 'safety_campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attempts()
    {
        return $this->hasMany(SafetyCampaignAttempt::class)->orderBy('attempt_no');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    // Lulus di percobaan pertama (semua benar)
    public function passedFirstAttempt(): bool
    {
        return $this->first_attempt_total !== null
            && $this->first_attempt_score === $this->first_attempt_total;
    }
}