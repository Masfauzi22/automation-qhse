<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FitToWork extends Model
{
    use HasFactory;

    // Daftarkan semua kolom agar bisa diisi (Mass Assignment)
    protected $fillable = [
        'user_id', 'age', 'sleep_hours', 'job_type',
        'q1_is_fit', 'q2_accustomed', 'q3_vision_issue', 
        'q4_asthma', 'q5_epilepsy', 'q6_trained', 'notes',
        'blood_pressure', 'body_temp', 'spo2', 'heart_rate', 'respiration_rate',
        'is_recommended', 'status', 'medical_id', 'qhse_id'
    ];

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