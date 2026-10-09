<?php

namespace App\Services;

/**
 * Penghitung skor risiko penipuan (scam) untuk sebuah entitas.
 *
 * Skor dihitung dari 0 sampai 100 berdasarkan tiga sinyal:
 *  1. Jumlah laporan komunitas (report_count) — semakin banyak laporan,
 *     semakin besar bobotnya.
 *  2. Umur entitas (entity_age_days) — entitas yang masih sangat baru
 *     namun sudah dilaporkan dianggap lebih mencurigakan.
 *  3. Kata kunci berbahaya (danger_keywords) — daftar kata/frasa yang
 *     umum dipakai dalam modus penipuan di Indonesia.
 *
 * Hasil berupa skor numerik beserta verdict: "aman", "hati_hati", atau "bahaya".
 */
class ScamScorer
{
    /**
     * Ambang batas verdict "bahaya".
     */
    public const BAHAYA_THRESHOLD = 70;

    /**
     * Ambang batas verdict "hati_hati".
     */
    public const HATI_HATI_THRESHOLD = 30;

    /**
     * Kata kunci yang umum muncul dalam pesan/modus penipuan di Indonesia.
     *
     * @var string[]
     */
    protected array $dangerKeywords = [
        'transfer', 'otp', 'pin', 'password', 'verifikasi',
        'hadiah', 'gratis', 'undian', 'pemenang', 'giveaway',
        'investasi', 'cuan', 'profit', 'trading',
        'klik', 'tautan', 'link', 'apk', 'unduh',
        'm-banking', 'e-wallet', 'blokir', 'tunggakan',
        'kurir', 'paket', 'pajak', 'tilang', 'polisi', 'kejaksaan',
        'pinjaman', 'cair', 'bansos', 'bantuan',
    ];

    /**
     * Hitung skor risiko dari sekumpulan sinyal.
     *
     * Struktur $signals yang didukung:
     *  - "report_count"     => (int) jumlah laporan komunitas.
     *  - "entity_age_days"  => (int) umur entitas dalam hari.
     *  - "danger_keywords"  => (string[]|int) daftar kata kunci berbahaya
     *                          yang cocok, atau jumlahnya.
     *
     * Komposisi skor (maksimal 100):
     *  - Jumlah laporan : +8 per laporan, maksimal 40 poin.
     *  - Umur entitas   : < 7 hari +15 poin, < 30 hari +8 poin.
     *  - Kata kunci     : +4 per kata kunci, maksimal 20 poin.
     *
     * Verdict ditentukan dari skor akhir:
     *  - skor >= 70        => "bahaya"
     *  - 30 <= skor < 70   => "hati_hati"
     *  - skor < 30         => "aman"
     *
     * @param  array  $signals  Sinyal-sinyal penilai risiko.
     * @return array{score: int, verdict: "aman"|"hati_hati"|"bahaya"}
     */
    public function score(array $signals): array
    {
        $score = 0;

        // 1. Jumlah laporan komunitas: +8 per laporan, maksimal 40 poin.
        $reportCount = (int) ($signals['report_count'] ?? 0);
        $score += min($reportCount * 8, 40);

        // 2. Umur entitas: entitas baru yang sudah dilaporkan lebih mencurigakan.
        $ageDays = (int) ($signals['entity_age_days'] ?? 0);
        if ($ageDays < 7) {
            $score += 15;
        } elseif ($ageDays < 30) {
            $score += 8;
        }

        // 3. Kata kunci berbahaya: +4 per kata kunci, maksimal 20 poin.
        $keywords = $signals['danger_keywords'] ?? [];
        $keywordCount = is_array($keywords) ? count($keywords) : (int) $keywords;
        $score += min($keywordCount * 4, 20);

        // Batasi skor pada rentang 0-100.
        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'verdict' => $this->verdictFor($score),
        ];
    }

    /**
     * Tentukan verdict dari sebuah skor.
     *
     * @param  int  $score  Skor risiko 0-100.
     * @return "aman"|"hati_hati"|"bahaya"
     */
    public function verdictFor(int $score): string
    {
        if ($score >= self::BAHAYA_THRESHOLD) {
            return 'bahaya';
        }

        if ($score >= self::HATI_HATI_THRESHOLD) {
            return 'hati_hati';
        }

        return 'aman';
    }

    /**
     * Pindai sebuah teks dan kembalikan kata kunci berbahaya yang ditemukan.
     *
     * Berguna untuk menilai isi pesan/laporan sebelum disimpan.
     *
     * @param  string  $text  Teks yang akan dipindai.
     * @return string[]  Kata kunci berbahaya yang cocok (unik).
     */
    public function extractDangerKeywords(string $text): array
    {
        $lowered = mb_strtolower($text);
        $matched = [];

        foreach ($this->dangerKeywords as $keyword) {
            if (str_contains($lowered, $keyword)) {
                $matched[] = $keyword;
            }
        }

        return array_values(array_unique($matched));
    }
}
