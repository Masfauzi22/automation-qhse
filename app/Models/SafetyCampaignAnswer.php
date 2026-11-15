<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['safety_campaign_attempt_id', 'safety_campaign_question_id', 'safety_campaign_option_id', 'is_correct'])]
class SafetyCampaignAnswer extends Model
{
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(SafetyCampaignAttempt::class, 'safety_campaign_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(SafetyCampaignQuestion::class, 'safety_campaign_question_id');
    }

    public function option()
    {
        return $this->belongsTo(SafetyCampaignOption::class, 'safety_campaign_option_id');
    }
}