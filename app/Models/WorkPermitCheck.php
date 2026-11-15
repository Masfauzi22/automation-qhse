<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['work_permit_id', 'item_key', 'item_text', 'answer', 'note'])]
class WorkPermitCheck extends Model
{
    public const ANSWER_YES = 'yes';
    public const ANSWER_NA  = 'na';   // tidak berlaku

    public function permit()
    {
        return $this->belongsTo(WorkPermit::class, 'work_permit_id');
    }

    public function isYes(): bool
    {
        return $this->answer === self::ANSWER_YES;
    }
}