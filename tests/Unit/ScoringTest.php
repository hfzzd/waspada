<?php

namespace Tests\Unit;

use App\Services\PhoneNormalizer;
use App\Services\ScamScorer;
use PHPUnit\Framework\TestCase;

/**
 * Unit test logika skoring penipuan (ScamScorer) dan
 * normalisasi nomor HP Indonesia (PhoneNormalizer).
 *
 * Komposisi skor (maks 100):
 *  - Jumlah laporan : +8 per laporan, maksimal 40 poin.
 *  - Umur entitas   : < 7 hari +15 poin, < 30 hari +8 poin.
 *  - Kata kunci     : +4 per kata kunci, maksimal 20 poin.
 *
 * Verdict: skor >= 70 => bahaya, 30-69 => hati_hati, < 30 => aman.
 */
class ScoringTest extends TestCase
{
    private ScamScorer $scorer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scorer = new ScamScorer();
    }

    public function test_skor_bertambah_dengan_jumlah_laporan(): void
    {
        $sedikit = $this->scorer->score(['report_count' => 1, 'entity_age_days' => 60, 'danger_keywords' => []]);
        $banyak = $this->scorer->score(['report_count' => 5, 'entity_age_days' => 60, 'danger_keywords' => []]);

        $this->assertSame(8, $sedikit['score']);  // 1*8 + 0 + 0
        $this->assertSame(40, $banyak['score']);  // min(5*8,40)=40 + 0 + 0
        $this->assertGreaterThan($sedikit['score'], $banyak['score']);
    }

    public function test_kontribusi_laporan_dibatasi_40_poin(): void
    {
        $result = $this->scorer->score(['report_count' => 100, 'entity_age_days' => 60, 'danger_keywords' => []]);

        $this->assertSame(40, $result['score']);
    }

    public function test_entitas_baru_mendapat_bonus_umur(): void
    {
        $baru = $this->scorer->score(['report_count' => 0, 'entity_age_days' => 3, 'danger_keywords' => []]);
        $agakLama = $this->scorer->score(['report_count' => 0, 'entity_age_days' => 10, 'danger_keywords' => []]);
        $lama = $this->scorer->score(['report_count' => 0, 'entity_age_days' => 60, 'danger_keywords' => []]);

        $this->assertSame(15, $baru['score']);
        $this->assertSame(8, $agakLama['score']);
        $this->assertSame(0, $lama['score']);
    }

    public function test_kontribusi_kata_kunci_dibatasi_20_poin(): void
    {
        $result = $this->scorer->score([
            'report_count' => 0,
            'entity_age_days' => 60,
            'danger_keywords' => ['a', 'b', 'c', 'd', 'e', 'f', 'g'],
        ]);

        $this->assertSame(20, $result['score']); // min(7*4,20)=20
    }

    public function test_verdict_mengikuti_ambang_batas(): void
    {
        $this->assertSame('bahaya', $this->scorer->verdictFor(100));
        $this->assertSame('bahaya', $this->scorer->verdictFor(70));
        $this->assertSame('hati_hati', $this->scorer->verdictFor(69));
        $this->assertSame('hati_hati', $this->scorer->verdictFor(30));
        $this->assertSame('aman', $this->scorer->verdictFor(29));
        $this->assertSame('aman', $this->scorer->verdictFor(0));
    }

    public function test_skor_menghasilkan_verdict_bahaya_untuk_kasus_ekstrem(): void
    {
        $result = $this->scorer->score([
            'report_count' => 10,
            'entity_age_days' => 2,
            'danger_keywords' => ['transfer', 'otp', 'hadiah', 'klik', 'apk'],
        ]);

        // 40 + 15 + 20 = 75
        $this->assertSame(75, $result['score']);
        $this->assertSame('bahaya', $result['verdict']);
    }

    public function test_extract_danger_keywords_menemukan_kata_kunci(): void
    {
        $matched = $this->scorer->extractDangerKeywords(
            'Tolong TRANSFER kode OTP kamu untuk verifikasi hadiah undian!'
        );

        $this->assertContains('transfer', $matched);
        $this->assertContains('otp', $matched);
        $this->assertContains('verifikasi', $matched);
        $this->assertContains('hadiah', $matched);
        $this->assertContains('undian', $matched);
    }

    public function test_extract_danger_keywords_tidak_duplikat_dan_case_insensitive(): void
    {
        $matched = $this->scorer->extractDangerKeywords('TRANSFER transfer Transfer');

        $this->assertSame(['transfer'], $matched);
    }

    public function test_extract_danger_keywords_kosong_untuk_teks_bersih(): void
    {
        $this->assertSame([], $this->scorer->extractDangerKeywords('Halo, apa kabar? Sampai jumpa besok.'));
    }

    /**
     * @dataProvider nomorIndonesiaProvider
     */
    public function test_normalisasi_nomor_hp_indonesia(string $input, string $expected): void
    {
        $this->assertSame($expected, PhoneNormalizer::normalize($input));
    }

    public static function nomorIndonesiaProvider(): array
    {
        return [
            'format lokal dengan strip' => ['0812-3456-7890', '6281234567890'],
            'format internasional plus' => ['+62 812 3456 7890', '6281234567890'],
            'format internasional tanpa plus' => ['6281234567890', '6281234567890'],
            'format dengan kurung dan titik' => ['(0812) 3456.7890', '6281234567890'],
            'tanpa pemisah' => ['081234567890', '6281234567890'],
        ];
    }
}
