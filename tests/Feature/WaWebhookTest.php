<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Feature test untuk webhook WhatsApp Cloud API:
 *  - GET  /api/wa/webhook (verifikasi langganan Meta)
 *  - POST /api/wa/webhook (pesan masuk, verifikasi HMAC-SHA256)
 */
class WaWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset rate limiter antar test agar throttle tidak bocor antar test.
        $this->app['cache']->flush();

        // Inject kredensial WA via config (idiom Laravel) — putenv() tidak
        // bisa menimpa $_SERVER yang sudah diisi .env saat boot.
        config([
            'waspada.wa_verify_token' => 'test-verify-token',
            'waspada.wa_app_secret' => 'test-app-secret',
            'waspada.wa_phone_number_id' => 'test-phone-id',
            'waspada.wa_access_token' => 'test-access-token',
        ]);

        Http::fake();
    }

    /**
     * Bangun header signature HMAC-SHA256 yang valid untuk sebuah payload.
     *
     * @return array{0: array, 1: array} [payload, headers]
     */
    private function signedRequest(array $payload): array
    {
        $body = json_encode($payload);

        return [
            $payload,
            ['X-Hub-Signature-256' => 'sha256='.hash_hmac('sha256', $body, 'test-app-secret')],
        ];
    }

    /**
     * Payload pesan teks masuk dari Meta.
     */
    private function textMessagePayload(string $body): array
    {
        return [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'messages' => [[
                            'from' => '6281234567890',
                            'type' => 'text',
                            'text' => ['body' => $body],
                        ]],
                    ],
                ]],
            ]],
        ];
    }

    public function test_verify_token_benar_mengembalikan_challenge(): void
    {
        $response = $this->getJson('/api/wa/webhook?'.http_build_query([
            'hub.mode' => 'subscribe',
            'hub.verify_token' => 'test-verify-token',
            'hub.challenge' => 'challenge-123',
        ]));

        $response->assertOk();
        $this->assertSame('challenge-123', $response->getContent());
    }

    public function test_verify_token_salah_mengembalikan_403(): void
    {
        $response = $this->getJson('/api/wa/webhook?'.http_build_query([
            'hub.mode' => 'subscribe',
            'hub.verify_token' => 'token-ngawur',
            'hub.challenge' => 'challenge-123',
        ]));

        $response->assertForbidden();
    }

    public function test_pesan_teks_valid_membalas_hasil_cek(): void
    {
        [$payload, $headers] = $this->signedRequest(
            $this->textMessagePayload('apakah pesan ini penipuan?')
        );

        $response = $this->postJson('/api/wa/webhook', $payload, $headers);

        $response->assertOk();

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v21.0/test-phone-id/messages'
                && $request['to'] === '6281234567890'
                && $request['type'] === 'text'
                && str_contains($request['text']['body'], 'AMAN');
        });
    }

    public function test_signature_salah_mengembalikan_401(): void
    {
        $response = $this->postJson(
            '/api/wa/webhook',
            $this->textMessagePayload('halo'),
            ['X-Hub-Signature-256' => 'sha256=signaturepalsu']
        );

        $response->assertUnauthorized();

        Http::assertNothingSent();
    }

    public function test_payload_status_saja_dilewati_tanpa_balasan(): void
    {
        [$payload, $headers] = $this->signedRequest([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'statuses' => [[
                            'id' => 'wamid.xxx',
                            'status' => 'delivered',
                        ]],
                    ],
                ]],
            ]],
        ]);

        $response = $this->postJson('/api/wa/webhook', $payload, $headers);

        $response->assertOk();

        Http::assertNothingSent();
    }

    public function test_perintah_lapor_membuat_laporan_dan_membalas_konfirmasi(): void
    {
        [$payload, $headers] = $this->signedRequest(
            $this->textMessagePayload(
                '/lapor phising | Saya menerima pesan yang meminta transfer uang ke rekening tak dikenal'
            )
        );

        $response = $this->postJson('/api/wa/webhook', $payload, $headers);

        $response->assertOk();

        $this->assertDatabaseHas('reports', [
            'type' => 'message',
            'category' => 'phising',
            'status' => 'pending',
            'reporter_contact' => '6281234567890',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request['text']['body'], 'Terima kasih');
        });
    }

    public function test_perintah_lapor_format_salah_membalas_petunjuk(): void
    {
        [$payload, $headers] = $this->signedRequest(
            $this->textMessagePayload('/lapor phising')
        );

        $response = $this->postJson('/api/wa/webhook', $payload, $headers);

        $response->assertOk();

        $this->assertDatabaseCount('reports', 0);

        Http::assertSent(function ($request) {
            return str_contains($request['text']['body'], 'Format lapor');
        });
    }
}
