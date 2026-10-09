<?php

namespace App\Services;

use App\Models\ScamEntity;
use Illuminate\Support\Carbon;

/**
 * Layanan utama pengecekan penipuan.
 *
 * Menerima sebuah nilai (nomor telepon, rekening, URL, dsb), menormalisasinya,
 * mencarinya di basis data entitas penipuan, lalu menggabungkan data basis
 * data dengan penilaian ScamScorer menjadi satu hasil pemeriksaan.
 */
class ScamCheckService
{
    public function __construct(
        protected ScamScorer $scorer
    ) {}

    /**
     * Periksa sebuah nilai terhadap basis data penipuan Waspada.
     *
     * Langkah kerja:
     *  1. Normalisasi nilai masukan (nomor telepon dinormalisasi dengan
     *     PhoneNormalizer, tipe lain di-trim).
     *  2. Cari entitas yang cocok (kolom type + normalized_value).
     *  3. Jika ditemukan, gabungkan data entitas dengan ScamScorer untuk
     *     menghitung ulang skor dan verdict terkini.
     *  4. Jika tidak ditemukan, verdict "aman" dengan skor 0.
     *
     * @param  string  $type   Jenis nilai: phone, bank_account, ewallet,
     *                        url, email, message, other.
     * @param  string  $value  Nilai yang akan diperiksa.
     * @return array{
     *     entity: ?ScamEntity,
     *     verdict: "aman"|"hati_hati"|"bahaya",
     *     risk_score: int,
     *     report_count: int,
     *     explanation: string,
     *     checked_at: string
     * }
     */
    public function check(string $type, string $value): array
    {
        $lookup = $type === 'phone'
            ? PhoneNormalizer::normalize($value)
            : trim($value);

        $entity = ScamEntity::where('type', $type)
            ->where('normalized_value', $lookup)
            ->first();

        if ($entity === null) {
            return $this->result(null, 'aman', 0, 0);
        }

        $signals = [
            'report_count' => (int) $entity->report_count,
            'entity_age_days' => $entity->created_at instanceof Carbon
                ? $entity->created_at->diffInDays(now())
                : 0,
            'danger_keywords' => (array) ($entity->metadata['danger_keywords'] ?? []),
        ];

        $scored = $this->scorer->score($signals);

        return $this->result(
            $entity,
            $scored['verdict'],
            $scored['score'],
            (int) $entity->report_count
        );
    }

    /**
     * Susun struktur hasil pemeriksaan yang konsisten.
     */
    protected function result(
        ?ScamEntity $entity,
        string $verdict,
        int $score,
        int $reportCount
    ): array {
        return [
            'entity' => $entity,
            'verdict' => $verdict,
            'risk_score' => $score,
            'report_count' => $reportCount,
            'explanation' => $this->explanationFor($verdict),
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Penjelasan verdict dalam Bahasa Indonesia (1-2 kalimat).
     */
    protected function explanationFor(string $verdict): string
    {
        return match ($verdict) {
            'bahaya' => 'Nilai ini terindikasi kuat sebagai penipuan berdasarkan laporan komunitas. '
                .'Jangan melakukan transfer, jangan membagikan kode OTP/PIN, dan segera laporkan jika sudah menjadi korban.',
            'hati_hati' => 'Nilai ini memiliki beberapa indikasi mencurigakan, tetapi buktinya belum cukup '
                .'untuk dinyatakan berbahaya. Tetap waspada, verifikasi melalui kanal resmi, dan jangan terburu-buru mengambil keputusan.',
            default => 'Tidak ditemukan indikasi penipuan untuk nilai ini di basis data Waspada. '
                .'Tetap berhati-hati dan laporkan jika menemukan hal yang mencurigakan.',
        };
    }
}
