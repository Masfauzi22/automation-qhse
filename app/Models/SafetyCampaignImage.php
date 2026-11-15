<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use App\Support\Uploads;

#[Fillable(['safety_campaign_id', 'path', 'original_name', 'sort_order'])]
class SafetyCampaignImage extends Model
{
    public function campaign()
    {
        return $this->belongsTo(SafetyCampaign::class, 'safety_campaign_id');
    }

    // URL foto untuk <img src="...">: file lokal (laptop) atau Supabase (Vercel), mengikuti FILESYSTEM_DISK
    public function url(): string
    {
        return Uploads::url($this->path) ?? '';
    }
}