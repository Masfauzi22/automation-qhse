<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'location', 'ph_level', 'debit_level', 'naoh_amount', 'notes'
    ];

    // Relasi ke petugas yang mencatat
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}