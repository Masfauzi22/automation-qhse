<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['safety_campaign_id', 'question', 'explanation', 'sort_order'])]
class SafetyCampaignQuestion extends Model
{
    public function campaign()
    {
        return $this->belongsTo(SafetyCampaign::class, 'safety_campaign_id');
    }

    public function options()
    {
        return $this->hasMany(SafetyCampaignOption::class)->orderBy('sort_order');
    }

    public function correctOption()
    {
        return $this->hasOne(SafetyCampaignOption::class)->where('is_correct', true);
    }

    public function answers()
    {
        return $this->hasMany(SafetyCampaignAnswer::class);
    }
}