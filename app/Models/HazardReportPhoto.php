<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use App\Support\Uploads;

#[Fillable(['hazard_report_id', 'hazard_report_update_id', 'type', 'path', 'original_name', 'uploaded_by'])]
class HazardReportPhoto extends Model
{
    public const TYPE_FINDING  = 'finding';  // foto bukti temuan
    public const TYPE_CLOSING  = 'closing';  // foto bukti perbaikan / closing
    public const TYPE_PROGRESS = 'progress'; // foto tindakan sementara saat minta perpanjangan

    public function report()
    {
        return $this->belongsTo(HazardReport::class, 'hazard_report_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // URL foto untuk <img src="...">: file lokal (laptop) atau Supabase (Vercel), mengikuti FILESYSTEM_DISK
    public function url(): string
    {
        return Uploads::url($this->path) ?? '';
    }
}