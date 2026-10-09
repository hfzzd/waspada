<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    /**
     * Nama tabel (bentuk jamak "feedback" tetap "feedback").
     *
     * @var string
     */
    protected $table = 'feedback';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'report_id',
        'was_helpful',
        'comment',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'was_helpful' => 'boolean',
        ];
    }

    /**
     * Laporan yang diberi umpan balik.
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
