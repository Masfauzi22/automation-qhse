<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvSamplingResult extends Model
{
    protected $fillable = [
        'env_sampling_id', 
        'sampling_parameter_id', 
        'result_value'
    ];

    public function parameter()
    {
        return $this->belongsTo(SamplingParameter::class, 'sampling_parameter_id');
    }
}