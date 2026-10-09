<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScamEntity extends Model
{
    /** @use HasFactory<\Database\Factories\ScamEntityFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'value',
        'normalized_value',
        'verdict',
        'risk_score',
        'report_count',
        'first_reported_at',
        'last_reported_at',
        'metadata',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'first_reported_at' => 'datetime',
            'last_reported_at' => 'datetime',
        ];
    }

    /**
     * Laporan komunitas yang merujuk ke entitas ini.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Entitas yang sudah terkonfirmasi lewat laporan komunitas.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('report_count', '>', 0);
    }

    /**
     * Entitas dengan verdict berbahaya.
     */
    public function scopeDangerous(Builder $query): Builder
    {
        return $query->where('verdict', 'bahaya');
    }

    /**
     * Label verdict dalam Bahasa Indonesia untuk tampilan.
     */
    public function getVerdictLabelAttribute(): string
    {
        return match ($this->verdict) {
            'aman' => 'Aman',
            'hati_hati' => 'Hati-hati',
            'bahaya' => 'Bahaya',
            default => 'Tidak diketahui',
        };
    }
}
