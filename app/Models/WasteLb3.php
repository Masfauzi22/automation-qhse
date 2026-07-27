<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteLb3 extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'waste_type', 'quantity', 'unit', 'source_location', 
        'status', 'signature_path', 'receiver_name', 'received_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}