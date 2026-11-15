<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['work_permit_id', 'action', 'user_id', 'user_name', 'user_title', 'note'])]
class WorkPermitLog extends Model
{
    // Label untuk riwayat
    public const ACTIONS = [
        'submitted'      => 'Diajukan',
        'checked'        => 'Diperiksa atasan',
        'approved'       => 'Disetujui & diketahui QHSE — permit berlaku',
        'returned'       => 'Dikembalikan untuk revisi',
        'stopped'        => 'Pekerjaan dihentikan (Stop Work)',
        'resumed'        => 'Pekerjaan dilanjutkan',
        'finished'       => 'Pekerjaan selesai, menunggu close-out',
        'close_checked'  => 'Close-out diperiksa atasan',
        'close_approved' => 'Close-out disetujui QHSE — permit Closed',
        'close_returned' => 'Close-out dikembalikan',
        'expired'        => 'Masa berlaku habis',
    ];


    public function permit()
    {
        return $this->belongsTo(WorkPermit::class, 'work_permit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    public function isNegative(): bool
    {
        return in_array($this->action, ['returned', 'stopped', 'close_returned', 'expired'], true);
    }
}