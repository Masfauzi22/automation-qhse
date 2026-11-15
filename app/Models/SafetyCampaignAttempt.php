<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['safety_campaign_response_id', 'attempt_no', 'score', 'total', 'passed'])]
class SafetyCampaignAttempt extends Model
{
    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
        ];
    }

    public function response()
    {
        return $this->belongsTo(SafetyCampaignResponse::class, 'safety_campaign_response_id');
    }

    public function answers()
    {
        return $this->hasMany(SafetyCampaignAnswer::class);
    }
}