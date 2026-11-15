<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'is_contractor'])]
class Company extends Model
{
    protected function casts(): array
    {
        return [
            'is_contractor' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}