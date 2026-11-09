<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitToWork extends Model
{
    use HasFactory;

    // Pilihan rekomendasi dokter
    public const FIT_STATUSES = [
        'Fit'           => 'Fit',
        'Fit_With_Note' => 'Fit With Note',
        'Unfit'         => 'Tidak Bisa Bekerja',
    ];

    // Daftarkan semua kolom agar bisa diisi (Mass Assignment)
    protected $fillable = [
        'user_id', 'age', 'sleep_hours', 'job_type',
        'q1_is_fit', 'q2_accustomed', 'q3_vision_issue',
        'q4_asthma', 'q5_epilepsy', 'q6_trained', 'notes',
        'blood_pressure', 'body_temp', 'spo2', 'heart_rate', 'respiration_rate',
        'is_recommended', 'fit_status', 'medical_notes',
        'status', 'medical_id', 'qhse_id',
    ];

    protected $casts = [
        'q1_is_fit'       => 'boolean',
        'q2_accustomed'   => 'boolean',
        'q3_vision_issue' => 'boolean',
        'q4_asthma'       => 'boolean',
        'q5_epilepsy'     => 'boolean',
        'q6_trained'      => 'boolean',
        'is_recommended'  => 'boolean',
    ];

    // Label rekomendasi dokter, mis. "Fit With Note". Bisa dipakai: $ftw->fit_status_label
    public function getFitStatusLabelAttribute(): ?string
    {
        return self::FIT_STATUSES[$this->fit_status] ?? null;
    }

    // Relasi ke Pemohon (Pekerja)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke Pemeriksa (Dokter/Medis)
    public function medical()
    {
        return $this->belongsTo(User::class, 'medical_id');
    }

    // Relasi ke Penyetuju (QHSE)
    public function qhse()
    {
        return $this->belongsTo(User::class, 'qhse_id');
    }
}