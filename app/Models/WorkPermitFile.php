<?php

namespace App\Models;

use App\Support\Uploads;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['work_permit_id', 'kind', 'item_key', 'work_permit_daily_log_id', 'path', 'original_name', 'uploaded_by'])]
class WorkPermitFile extends Model
{
    public const KIND_JSA      = 'jsa';
    public const KIND_CHECK    = 'check';     // foto bukti butir checklist
    public const KIND_DAILY    = 'daily';     // foto saat aktivasi harian
    public const KIND_CLOSEOUT = 'closeout';  // foto kondisi akhir

    public function permit()
    {
        return $this->belongsTo(WorkPermit::class, 'work_permit_id');
    }

    public function url(): string
    {
        return Uploads::url($this->path) ?? '';
    }

    public function isImage(): bool
    {
        return (bool) preg_match('/\.(jpe?g|png|webp)$/i', $this->path);
    }
}