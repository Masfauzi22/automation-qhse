<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title', 'message', 'start_date', 'end_date',
    'target_all', 'is_published', 'published_at', 'created_by',
])]
class SafetyCampaign extends Model
{
    use SoftDeletes;

    // Status dihitung dari is_published + tanggal, tidak disimpan di database
    public const STATUSES = [
        'draft'     => ['label' => 'Draft',      'class' => 'bg-gray-100 text-gray-700 border-gray-200'],
        'scheduled' => ['label' => 'Terjadwal',  'class' => 'bg-blue-50 text-blue-700 border-blue-200'],
        'active'    => ['label' => 'Aktif',      'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
        'ended'     => ['label' => 'Berakhir',   'class' => 'bg-slate-100 text-slate-500 border-slate-200'],
    ];

    protected function casts(): array
    {
        return [
            'start_date'   => 'date',
            'end_date'     => 'date',
            'target_all'   => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // ---------------------------------------------------------------
    // Relasi
    // ---------------------------------------------------------------
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'safety_campaign_department');
    }

    public function images()
    {
        return $this->hasMany(SafetyCampaignImage::class)->orderBy('sort_order');
    }

    public function questions()
    {
        return $this->hasMany(SafetyCampaignQuestion::class)->orderBy('sort_order');
    }

    public function responses()
    {
        return $this->hasMany(SafetyCampaignResponse::class);
    }

    // ---------------------------------------------------------------
    // Status
    // ---------------------------------------------------------------
    public function statusKey(): string
    {
        if (! $this->is_published) {
            return 'draft';
        }
        if ($this->start_date->isAfter(today())) {
            return 'scheduled';
        }
        if ($this->end_date && $this->end_date->isBefore(today())) {
            return 'ended';
        }

        return 'active';
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->statusKey()]['label'];
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->statusKey()]['class'];
    }

    // Sudah lewat tanggal selesai
    public function isEnded(): bool
    {
        return $this->end_date !== null && $this->end_date->isBefore(today());
    }

    public function hasQuiz(): bool
    {
        return $this->relationLoaded('questions')
            ? $this->questions->isNotEmpty()
            : $this->questions()->exists();
    }

    /** Apakah campaign ini ditujukan untuk user tersebut */
    public function isTargetedTo(User $user): bool
    {
        if ($this->target_all) {
            return true;
        }

        return $user->department_id
            && $this->departments()->whereKey($user->department_id)->exists();
    }

    /**
     * Soal dikunci kalau sudah ada yang mengerjakan,
     * supaya rekap jawaban tidak kacau karena soalnya berubah.
     */
    public function questionsLocked(): bool
    {
        return $this->responses()->where('attempts_count', '>', 0)->exists();
    }

    // ---------------------------------------------------------------
    // Scope query
    // ---------------------------------------------------------------

    // Sudah terbit, sudah mulai, belum berakhir
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereDate('start_date', '<=', today())
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()));
    }

    // Untuk semua karyawan, atau departemen user termasuk target
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(function ($q) use ($user) {
            $q->where('target_all', true)
              ->orWhereHas('departments', fn ($d) => $d->where('departments.id', $user->department_id ?? 0));
        });
    }

    // Campaign aktif untuk user ini yang belum dia selesaikan (untuk Dashboard & badge sidebar)
    public function scopePendingFor(Builder $query, User $user): Builder
    {
        return $query->active()
            ->visibleTo($user)
            ->whereDoesntHave('responses', fn ($r) => $r->where('user_id', $user->id)->whereNotNull('completed_at'));
    }
}