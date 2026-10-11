<?php

namespace App\Services;

use App\Http\Requests\ReportRequest;
use App\Models\Report;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Logika bot WhatsApp Waspada.
 *
 *  - Teks biasa  -> dicek lewat ScamCheckService, balas verdict.
 *  - "/lapor"    -> dibuatkan Report memakai aturan validasi yang sama
 *                   dengan ReportRequest (endpoint /api/reports).
 */
class WaBotService
{
    public function __construct(
        protected ScamCheckService $checker
    ) {}

    /**
     * Tangani satu pesan teks masuk dari nomor WhatsApp $from.
     */
    public function handleIncoming(string $from, string $text): void
    {
        $text = trim($text);

        if (str_starts_with(mb_strtolower($text), '/lapor')) {
            $this->handleReport($from, $text);

            return;
        }

        $this->handleCheck($from, $text);
    }

    /**
     * Perlakukan teks sebagai query pengecekan, balas verdict + alasan
     * singkat dalam Bahasa Indonesia.
     */
    protected function handleCheck(string $from, string $text): void
    {
        $result = $this->checker->check('message', $text);

        $label = match ($result['verdict']) {
            'bahaya' => '🔴 BAHAYA',
            'hati_hati' => '🟡 HATI-HATI',
            default => '🟢 AMAN',
        };

        $reason = $result['report_count'] > 0
            ? "Dilaporkan {$result['report_count']}x oleh komunitas."
            : 'Belum ada laporan untuk pesan ini di basis data Waspada.';

        $reply = "Hasil cek Waspada:\n{$label}\n{$reason}\n\n"
            .'Balas /lapor untuk melaporkan penipuan.';

        $this->sendText($from, $this->truncate($reply));
    }

    /**
     * Buat laporan komunitas dari perintah "/lapor".
     *
     * Format: /lapor <kategori> | <deskripsi>
     * Contoh: /lapor phising | Saya diminta transfer oleh nomor tak dikenal
     */
    protected function handleReport(string $from, string $text): void
    {
        $rest = trim(mb_substr($text, mb_strlen('/lapor')));

        $parts = array_map('trim', explode('|', $rest, 2));

        $validator = Validator::make(
            [
                'type' => 'message',
                'value' => $text,
                'category' => $parts[0] ?? '',
                'description' => $parts[1] ?? '',
                'reporter_contact' => $from,
            ],
            (new ReportRequest)->rules()
        );

        if ($validator->fails()) {
            $this->sendText($from, $this->truncate(
                "Format lapor belum tepat.\n\n"
                ."Ketik:\n/lapor <kategori> | <deskripsi>\n\n"
                .'Kategori: phising, investasi_bodong, love_scam, pinjol_ilegal, '
                .'undangan_apk, hadiah_palsu, jual_beli, lainnya. '
                .'Deskripsi minimal 10 karakter.'
            ));

            return;
        }

        $data = $validator->validated();

        Report::create([
            'type' => $data['type'],
            'value' => $data['value'],
            'category' => $data['category'],
            'description' => $data['description'],
            'reporter_contact' => $data['reporter_contact'] ?? null,
            'status' => 'pending',
        ]);

        $this->sendText($from, $this->truncate(
            'Terima kasih! Laporanmu sudah kami terima dan akan '
            .'diverifikasi oleh tim moderator. 🙏'
        ));
    }

    /**
     * Kirim pesan teks via WhatsApp Cloud API.
     *
     * Tidak pernah melempar exception — kegagalan hanya dicatat ke log
     * agar webhook Meta tetap menjawab 200.
     */
    public function sendText(string $to, string $text): void
    {
        try {
            $response = Http::timeout(10)
                ->withToken((string) config('waspada.wa_access_token'))
                ->post(
                    'https://graph.facebook.com/v21.0/'.config('waspada.wa_phone_number_id').'/messages',
                    [
                        'messaging_product' => 'whatsapp',
                        'to' => $to,
                        'type' => 'text',
                        'text' => ['body' => $text],
                    ]
                );

            if ($response->failed()) {
                Log::warning('WaBotService: gagal mengirim pesan WhatsApp', [
                    // Nomor HP adalah PII — catat versi tersamar saja (W08).
                    'to' => self::maskPhone($to),
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('WaBotService: exception saat mengirim pesan WhatsApp', [
                'to' => self::maskPhone($to),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Samarkan nomor HP untuk log (W08): hanya awalan + 2 digit akhir.
     */
    protected static function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 6) {
            return '****';
        }

        return substr($phone, 0, 4).'****'.substr($phone, -2);
    }

    /**
     * Pangkas balasan agar tetap ringkas (maks ~300 karakter).
     */
    protected function truncate(string $text, int $limit = 300): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit - 1).'…';
    }
}
