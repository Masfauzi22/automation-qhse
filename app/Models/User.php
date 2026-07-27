<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; // <-- Import sudah benar di sini

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles; // <-- HasRoles ditambahkan di dalam class ini

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function simpers()
    {
        return $this->hasMany(Simper::class);
    }

    // Relasi ke Fit to Work
    public function fitToWorks()
    {
        return $this->hasMany(FitToWork::class);
    }

    // Relasi ke Permit to Work
    public function permitToWorks()
    {
        return $this->hasMany(PermitToWork::class);
    }

    // Relasi ke PPE Request
    public function ppeRequests()
    {
        return $this->hasMany(PpeRequest::class);
    }
}