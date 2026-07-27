<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamplingSubject extends Model
{
    // 1. Buka gembok agar form bisa menyimpan 'name' ke database
    protected $fillable = ['name'];

    // 2. Relasi ke tabel parameters (Many-to-Many lewat pivot yang kemarin kita buat)
    public function parameters()
    {
        return $this->belongsToMany(SamplingParameter::class, 'subject_parameter');
    }

    // 3. Relasi ke riwayat/transaksi sampling (agar tidak bisa dihapus sembarangan jika sudah dipakai)
    public function envSamplings()
    {
        return $this->hasMany(EnvSampling::class);
    }
}