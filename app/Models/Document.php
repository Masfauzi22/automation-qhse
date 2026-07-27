<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    // Kolom yang diizinkan untuk diisi data
    protected $fillable = [
        'document_number', 
        'title', 
        'type', 
        'file_path', 
        'uploaded_by'
    ];

    // Relasi: 1 Dokumen dimiliki oleh 1 User (Uploader)
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}