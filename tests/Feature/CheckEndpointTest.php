<?php

namespace Tests\Feature;

use App\Models\ScamEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test untuk endpoint POST /api/check.
 *
 * Memastikan pengecekan nomor telepon, rekening, dan tipe lain
 * mengembalikan verdict yang benar beserta penjelasannya.
 */
class CheckEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset rate limiter antar test agar throttle tidak bocor antar test.
        $this->app['cache']->flush();
    }

    public function test_check_mengembalikan_aman_untuk_nomor_tidak_dikenal(): void
    {
        $response = $this->postJson('/api/check', [
            'type' => 'phone',
            'value' => '081234567890',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.verdict', 'aman')
            ->assertJsonPath('data.verdict_label', 'AMAN')
            ->assertJsonPath('data.risk_score', 0)
            ->assertJsonPath('data.report_count', 0);
    }

    public function test_check_mendeteksi_nomor_penipuan_yang_terdaftar(): void
    {
        ScamEntity::create([
            'type' => 'phone',
            'value' => '0812-3456-7890',
            'normalized_value' => '6281234567890',
            'verdict' => 'bahaya',
            'risk_score' => 75,
            'report_count' => 8,
            'metadata' => ['danger_keywords' => ['transfer', 'otp', 'verifikasi', 'hadiah', 'klik']],
        ]);

        $response = $this->postJson('/api/check', [
            'type' => 'phone',
            'value' => '0812-3456-7890',
        ]);

        // Skor dihitung ulang: min(8*8,40)=40 + umur<7 hari=15 + 5 kata kunci*4=20 => 75 => bahaya.
        $response->assertOk()
            ->assertJsonPath('data.verdict', 'bahaya')
            ->assertJsonPath('data.verdict_label', 'BAHAYA')
            ->assertJsonPath('data.risk_score', 75)
            ->assertJsonPath('data.report_count', 8)
            ->assertJsonStructure(['data' => ['explanation', 'checked_at']]);
    }

    public function test_check_menormalisasi_format_internasional(): void
    {
        ScamEntity::create([
            'type' => 'phone',
            'value' => '0812-3456-7890',
            'normalized_value' => '6281234567890',
            'verdict' => 'bahaya',
            'risk_score' => 75,
            'report_count' => 8,
            'metadata' => ['danger_keywords' => ['transfer', 'otp', 'verifikasi', 'hadiah', 'klik']],
        ]);

        $response = $this->postJson('/api/check', [
            'type' => 'phone',
            'value' => '+62 812-3456-7890',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.verdict', 'bahaya')
            ->assertJsonPath('data.report_count', 8);
    }

    public function test_check_menolak_tipe_tidak_valid(): void
    {
        $response = $this->postJson('/api/check', [
            'type' => 'alien',
            'value' => '0812',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('type');
    }

    public function test_check_menolak_value_kosong(): void
    {
        $response = $this->postJson('/api/check', [
            'type' => 'phone',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('value');
    }

    public function test_check_tipe_rekening_bank_dengan_spasi(): void
    {
        ScamEntity::create([
            'type' => 'bank_account',
            'value' => '1234567890',
            'normalized_value' => '1234567890',
            'verdict' => 'hati_hati',
            'risk_score' => 0,
            'report_count' => 2,
            'metadata' => ['danger_keywords' => ['transfer']],
        ]);

        $response = $this->postJson('/api/check', [
            'type' => 'bank_account',
            'value' => ' 1234567890 ',
        ]);

        // Skor: 2*8=16 + umur<7 hari=15 + 1 kata kunci*4=4 => 35 => hati_hati.
        $response->assertOk()
            ->assertJsonPath('data.verdict', 'hati_hati')
            ->assertJsonPath('data.verdict_label', 'HATI-HATI')
            ->assertJsonPath('data.risk_score', 35)
            ->assertJsonPath('data.report_count', 2);
    }
}
