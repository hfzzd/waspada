<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test rate limiting endpoint publik.
 *
 * Batas throttle didefinisikan di routes/api.php:
 *  - POST /api/check    : 30 permintaan per menit
 *  - POST /api/reports  : 10 permintaan per menit
 *  - GET  /api/threat-feed : 120 permintaan per menit
 */
class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset rate limiter antar test agar throttle tidak bocor antar test.
        $this->app['cache']->flush();
    }

    public function test_endpoint_reports_dibatasi_10_per_menit(): void
    {
        $payload = [
            'type' => 'phone',
            'value' => '081234567890',
            'category' => 'phising',
            'description' => 'Laporan uji rate limit yang cukup panjang.',
        ];

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/reports', $payload)->assertCreated();
        }

        // Permintaan ke-11 dalam satu menit harus ditolak.
        $this->postJson('/api/reports', $payload)->assertStatus(429);
    }

    public function test_endpoint_check_dibatasi_30_per_menit(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->postJson('/api/check', [
                'type' => 'phone',
                'value' => '081234567890',
            ])->assertOk();
        }

        // Permintaan ke-31 dalam satu menit harus ditolak.
        $this->postJson('/api/check', [
            'type' => 'phone',
            'value' => '081234567890',
        ])->assertStatus(429);
    }
}
