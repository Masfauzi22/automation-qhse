<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles; 

// UBAH: 'department' diganti menjadi 'department_id' karena sekarang menggunakan tabel relasi
// TAMBAH: 'company_id' dan 'employee_number' untuk data pelapor Hazard Report
#[Fillable(['name', 'email', 'password', 'department_id', 'position', 'company_id', 'employee_number'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles; 

    // ---------------------------------------------------------------
    // Jabatan (berbeda dengan role: role = menu yang boleh dibuka,
    // jabatan = posisi di struktur organisasi, dipakai untuk alur Permit to Work)
    // ---------------------------------------------------------------
    public const POSITIONS = [
        'staff'        => ['label' => 'Staff / Operator', 'rank' => 0],
        'foreman'      => ['label' => 'Foreman',          'rank' => 1],
        'supervisor'   => ['label' => 'Supervisor',       'rank' => 2],
        'section_head' => ['label' => 'Section Head',     'rank' => 3],
    ];

    // Jabatan yang boleh mengajukan Permit to Work
    public const PERMIT_APPLICANT_POSITIONS = ['foreman', 'supervisor'];

    // Jabatan minimal untuk memeriksa Permit to Work
    public const PERMIT_CHECKER_MIN_RANK = 2; // Supervisor

    public static function positionRank(?string $position): int
    {
        return self::POSITIONS[$position]['rank'] ?? 0;
    }

    public function positionLabel(): string
    {
        return self::POSITIONS[$this->position]['label'] ?? '-';
    }

    public function canApplyPermit(): bool
    {
        return in_array($this->position, self::PERMIT_APPLICANT_POSITIONS, true);
    }

    // Boleh memeriksa permit dari pengaju dengan jabatan & departemen tertentu?
    // Aturan: departemen sama, jabatan lebih tinggi, minimal Supervisor.
    public function canCheckPermitFrom(?string $applicantPosition, ?int $applicantDepartmentId): bool
    {
        $myRank = self::positionRank($this->position);

        return $this->department_id
            && (int) $this->department_id === (int) $applicantDepartmentId
            && $myRank >= self::PERMIT_CHECKER_MIN_RANK
            && $myRank > self::positionRank($applicantPosition);
    }

    // Jabatan pengaju yang bisa diperiksa user ini, mis. Section Head → ['foreman', 'supervisor']
    public function checkablePositions(): array
    {
        $myRank = self::positionRank($this->position);
        if ($myRank < self::PERMIT_CHECKER_MIN_RANK) {
            return [];
        }

        return collect(self::POSITIONS)
            ->filter(fn ($p, $key) => $p['rank'] < $myRank && in_array($key, self::PERMIT_APPLICANT_POSITIONS, true))
            ->keys()
            ->all();
    }

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

    // Relasi ke Tabel Departemen
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    // Relasi ke Tabel Perusahaan (BARU)
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}