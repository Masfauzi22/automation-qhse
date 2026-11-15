<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['safety_campaign_question_id', 'option_text', 'is_correct', 'sort_order'])]
class SafetyCampaignOption extends Model
{
    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function question()
    {
        return $this->belongsTo(SafetyCampaignQuestion::class, 'safety_campaign_question_id');
    }
}