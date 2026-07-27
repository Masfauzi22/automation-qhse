<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvSampling extends Model
{
    // Ini dia kunci agar Laravel mengizinkan datanya disave!
    protected $fillable = [
        'sampling_subject_id', 
        'user_id', 
        'sampling_date', 
        'sampling_time', 
        'sample_mark', 
        'coordinate', 
        'description', 
        'attachment_file_path'
    ];

    public function subject()
    {
        return $this->belongsTo(SamplingSubject::class, 'sampling_subject_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results()
    {
        return $this->hasMany(EnvSamplingResult::class);
    }
}