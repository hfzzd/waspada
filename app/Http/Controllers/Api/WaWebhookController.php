<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WaBotService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Webhook Meta WhatsApp Cloud API.
 *
 *  - GET  /api/wa/webhook : verifikasi langganan webhook oleh Meta
 *                           (mengembalikan hub.challenge sebagai teks polos).
 *  - POST /api/wa/webhook : menerima event pesan masuk. Setiap request
 *                           diverifikasi dengan X-Hub-Signature-256
 *                           (HMAC-SHA256 dari raw body + WA_APP_SECRET).
 */
class WaWebhookController extends Controller
{
    /**
     * Verifikasi webhook oleh Meta: cocokkan hub.verify_token dengan
     * token yang kita daftarkan (WA_VERIFY_TOKEN).
     */
    public function verify(Request $request): Response
    {
        // Fail-closed (W09): token kosong = tolak, jangan pernah echo challenge.
        $expected = (string) config('waspada.wa_verify_token');
        if ($expected === ''
            || $request->query('hub.mode') !== 'subscribe'
            || ! hash_equals($expected, (string) $request->query('hub.verify_token'))
        ) {
            return response('Forbidden', 403);
        }

        return response((string) $request->query('hub.challenge'), 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Terima event dari Meta. Hanya pesan teks yang diproses; status
     * pengiriman dan tipe pesan lain dilewati dalam diam.
     */
    public function receive(Request $request, WaBotService $bot): Response
    {
        if (! $this->validSignature($request)) {
            return response('Unauthorized', 401);
        }

        $value = $request->input('entry.0.changes.0.value', []);

        foreach ($value['messages'] ?? [] as $message) {
            if (($message['type'] ?? null) !== 'text') {
                continue;
            }

            $from = $message['from'] ?? null;
            $text = $message['text']['body'] ?? null;

            if ($from && $text !== null) {
                $bot->handleIncoming($from, $text);
            }
        }

        return response('OK', 200);
    }

    /**
     * Verifikasi header X-Hub-Signature-256 terhadap raw body request.
     */
    protected function validSignature(Request $request): bool
    {
        $header = (string) $request->header('X-Hub-Signature-256', '');

        if (! str_starts_with($header, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac(
            'sha256',
            $request->getContent(),
            (string) config('waspada.wa_app_secret')
        );

        return hash_equals($expected, $header);
    }
}
