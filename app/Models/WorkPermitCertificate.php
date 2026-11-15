<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'work_permit_id', 'certificate_number', 'verify_token',
    'incident_free', 'incident_note', 'issued_by', 'issued_by_name', 'issued_at',
])]
class WorkPermitCertificate extends Model
{
    protected function casts(): array
    {
        return [
            'incident_free' => 'boolean',
            'issued_at'     => 'datetime',
        ];
    }

    public function permit()
    {
        return $this->belongsTo(WorkPermit::class, 'work_permit_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // Link verifikasi publik (isi QR code)
    public function verifyUrl(): string
    {
        return route('permit-certificate.verify', $this->verify_token);
    }

    public static function newToken(): string
    {
        return Str::random(32);
    }

    // CERT/PTW/IBT/2026/11/0001 (urutan reset tiap bulan)
    public static function generateNumber(): string
    {
        $prefix = config('permits.certificate.number_prefix', 'CERT/PTW/IBT') . '/' . now()->format('Y/m') . '/';

        $last = static::where('certificate_number', 'like', $prefix . '%')
            ->orderByDesc('certificate_number')
            ->value('certificate_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}