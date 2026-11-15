<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'sla_days', 'color', 'sort_order', 'is_active'])]
class HazardPriority extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sla_days'  => 'integer',
        ];
    }

    public function reports()
    {
        return $this->hasMany(HazardReport::class);
    }

    // Pemakaian: HazardPriority::active()->get()
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }

    // Class Tailwind untuk badge prioritas
    public function badgeClass(): string
    {
        return match ($this->color) {
            'red'     => 'bg-red-50 text-red-700 border-red-200',
            'amber'   => 'bg-amber-50 text-amber-700 border-amber-200',
            'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'blue'    => 'bg-blue-50 text-blue-700 border-blue-200',
            default   => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }

    // Warna pin di peta (hex), mengikuti kolom color
    public function markerColor(): string
    {
        return match ($this->color) {
            'red'     => '#dc2626',
            'amber'   => '#f59e0b',
            'emerald' => '#10b981',
            'blue'    => '#2563eb',
            default   => '#6b7280',
        };
    }

    // Kecepatan animasi sinyal: makin berbahaya makin cepat berdenyut
    public function pulseSpeed(): string
    {
        return match ($this->color) {
            'red'   => '1s',
            'amber' => '1.8s',
            default => '2.8s',
        };
    }
}