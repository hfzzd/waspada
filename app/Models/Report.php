<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    /** @use HasFactory<\Database\Factories\ReportFactory> */
    use HasFactory;

    /**
     * Kategori penipuan yang dikenali.
     *
     * @var list<string>
     */
    public const CATEGORIES = [
        'phising',
        'investasi_bodong',
        'love_scam',
        'pinjol_ilegal',
        'undangan_apk',
        'hadiah_palsu',
        'jual_beli',
        'lainnya',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'scam_entity_id',
        'reporter_name',
        'reporter_contact',
        'type',
        'value',
        'category',
        'description',
        'evidence_url',
        'status',
    ];

    /**
     * Entitas scam yang dilaporkan (bisa null bila belum terpetakan).
     */
    public function scamEntity(): BelongsTo
    {
        return $this->belongsTo(ScamEntity::class);
    }

    /**
     * Laporan yang masih menunggu moderasi.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Laporan yang sudah diverifikasi moderator.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }
}
