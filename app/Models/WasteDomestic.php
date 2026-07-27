<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteDomestic extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'collection_date', 
        'location', 
        'organic_volume', 
        'anorganic_volume', 
        'residue_volume', 
        'notes'
    ];

    // Hapus bagian $casts checklist_data karena kolomnya sudah kita buang dari database

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}