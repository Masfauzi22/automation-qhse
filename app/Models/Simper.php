<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Simper extends Model
{
    use HasFactory;

    // Daftarkan semua kolom yang diizinkan untuk diisi data (Mass Assignment)
    protected $fillable = [
        'user_id',
        'name',
        'nrp',
        'company',
        'department',
        'position',
        'simper_number',
        'photo_path',
        'status',
        'blood_type',
        'police_license',
        'authorized_area',
        'issue_date',
        'valid_until',
        'equipments', 
        
        // --- Tambahan Kolom Baru untuk Approval PIC & Ujian ---
        'pic_status',
        'pic_id',
        'pic_note',
        'written_test_score',
        'practical_test_status',
    ];

    // Konversi tipe data otomatis saat disimpan/diambil dari database
    protected $casts = [
        'equipments' => 'array', 
        'issue_date' => 'date',  
        'valid_until' => 'date',
        'written_test_score' => 'integer', // Pastikan nilai tes tulis terbaca sebagai angka
    ];

    // Relasi ke User (Siapa yang mengajukan SIMPER ini)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke User (Siapa Atasan/PIC Dept yang memberikan approval)
    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
}