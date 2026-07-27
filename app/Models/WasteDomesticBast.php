<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteDomesticBast extends Model
{
    protected $fillable = [
        'month_year', 'location', 'bast_file_path', 'status', 
        'uploaded_by', 'validated_by'
    ];
}
