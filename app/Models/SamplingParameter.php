<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamplingParameter extends Model
{
    protected $fillable = [
        'name', 
        'unit', 
        'method'
    ];

    public function subjects()
    {
        return $this->belongsToMany(SamplingSubject::class, 'subject_parameter');
    }

    public function envSamplingResults()
    {
        return $this->hasMany(EnvSamplingResult::class);
    }
}