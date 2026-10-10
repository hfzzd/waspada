<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature test validasi input untuk endpoint publik:
 *  - POST /api/check
 *  - POST /api/reports
 *
 * Termasuk uji ketahanan terhadap input berbahaya (SQL injection, XSS)
 * yang harus tersimpan dengan aman tanpa merusak basis data.
 */
class ValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset rate limiter antar test agar throttle tidak bocor antar test.
        $this->app['cache']->flush();
    }

    public function test_check_menolak_value_kosong(): void
    {
        $this->postJson('/api/check', ['type' => 'phone', 'value' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('value');
    }

    public function test_check_menolak_value_melebihi_batas(): void
    {
        $this->postJson('/api/check', [
            'type' => 'message',
            'value' => str_repeat('a', 2001),
        ])->assertStatus(422)
            ->assertJsonValidationErrors('value');
    }

    public function test_check_menerima_pesan_panjang_dalam_batas(): void
    {
        $this->postJson('/api/check', [
            'type' => 'message',
            'value' => str_repeat('a', 2000),
        ])->assertOk()
            ->assertJsonPath('data.verdict', 'aman');
    }

    public function test_reports_menyimpan_input_mirip_sql_injection_dengan_aman(): void
    {
        $payload = "' OR '1'='1'; DROP TABLE reports; --";

        $response = $this->postJson('/api/reports', [
            'type' => 'url',
            'value' => $payload,
            'category' => 'phising',
            'description' => 'Percobaan injeksi SQL pada kolom value laporan.',
        ]);

        $response->assertCreated();

        // Nilai tersimpan apa adanya (parameterized query), tabel tetap utuh.
        $this->assertDatabaseHas('reports', ['value' => $payload]);
        $this->assertDatabaseCount('reports', 1);
    }

    public function test_reports_menyimpan_input_xss_dengan_aman(): void
    {
        $xss = '<script>alert(document.cookie)</script>';

        $response = $this->postJson('/api/reports', [
            'type' => 'url',
            'value' => 'https://contoh-penipuan.xyz/login',
            'category' => 'phising',
            'description' => 'Pesan berisi skrip berbahaya: '.$xss.' Mohon ditindaklanjuti segera ya.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('reports', ['value' => 'https://contoh-penipuan.xyz/login']);
    }

    public function test_reports_menolak_evidence_url_tidak_valid(): void
    {
        $this->postJson('/api/reports', [
            'type' => 'phone',
            'value' => '081234567890',
            'category' => 'phising',
            'description' => 'Deskripsi laporan yang cukup panjang untuk lolos validasi.',
            'evidence_url' => 'bukan-sebuah-url',
        ])->assertStatus(422)
            ->assertJsonValidationErrors('evidence_url');
    }

    public function test_reports_menolak_payload_kosong(): void
    {
        $this->postJson('/api/reports', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type', 'value', 'category', 'description']);
    }
}
