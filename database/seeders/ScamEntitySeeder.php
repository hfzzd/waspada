<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Contoh data entitas penipuan untuk pengembangan & demo.
 *
 * SEMUA data di bawah ini FIKTIF: nomor, rekening, dan URL dibuat dengan
 * pola yang jelas bukan milik siapa pun. URL memakai TLD .example yang
 * dicadangkan dan tidak dapat di-resolve.
 */
class ScamEntitySeeder extends Seeder
{
    public function run(): void
    {
        $taxonomy = require database_path('data/scam_taxonomy.php');
        $keywordsOf = function (string $category) use ($taxonomy): array {
            foreach ($taxonomy as $item) {
                if ($item['category'] === $category) {
                    return $item['keywords'];
                }
            }
            return [];
        };

        $entities = [
            [
                'type' => 'phone',
                'value' => '628120011001',
                'normalized_value' => '+628120011001',
                'verdict' => 'bahaya',
                'risk_score' => 95,
                'report_count' => 214,
                'category' => 'undangan_apk',
                'matched_keywords' => ['.apk', 'undangan', 'pernikahan'],
                'note' => 'Penyebar file "Undangan Pernikahan.apk" via WhatsApp.',
            ],
            [
                'type' => 'phone',
                'value' => '628120011002',
                'normalized_value' => '+628120011002',
                'verdict' => 'bahaya',
                'risk_score' => 88,
                'report_count' => 167,
                'category' => 'pinjol_ilegal',
                'matched_keywords' => ['tanpa jaminan', 'cair 5 menit', 'cukup KTP'],
                'note' => 'SMS blast penawaran pinjol ilegal tanpa syarat.',
            ],
            [
                'type' => 'phone',
                'value' => '628120011003',
                'normalized_value' => '+628120011003',
                'verdict' => 'bahaya',
                'risk_score' => 82,
                'report_count' => 143,
                'category' => 'hadiah_palsu',
                'matched_keywords' => ['selamat', 'pemenang', 'klaim hadiah', 'biaya admin'],
                'note' => 'Mengaku dari e-wallet, korban diminta bayar biaya admin.',
            ],
            [
                'type' => 'phone',
                'value' => '628120011004',
                'normalized_value' => '+628120011004',
                'verdict' => 'hati_hati',
                'risk_score' => 64,
                'report_count' => 58,
                'category' => 'lowongan_kerja_palsu',
                'matched_keywords' => ['gaji', 'kerja dari HP', 'biaya training'],
                'note' => 'Tawaran kerja admin online, pola mirip loker palsu.',
            ],
            [
                'type' => 'bank_account',
                'value' => 'BCA 8210011223',
                'normalized_value' => 'bca:8210011223',
                'verdict' => 'bahaya',
                'risk_score' => 91,
                'report_count' => 189,
                'category' => 'investasi_bodong',
                'matched_keywords' => ['profit', 'dijamin', 'grup vip'],
                'note' => 'Rekening penampung setoran grup trading palsu.',
            ],
            [
                'type' => 'bank_account',
                'value' => 'BRI 002101122334455',
                'normalized_value' => 'bri:002101122334455',
                'verdict' => 'bahaya',
                'risk_score' => 87,
                'report_count' => 132,
                'category' => 'love_scam',
                'matched_keywords' => ['sayang', 'transfer dulu', 'darurat'],
                'note' => 'Rekening tujuan love scam berkedok biaya operasi.',
            ],
            [
                'type' => 'bank_account',
                'value' => 'Mandiri 8900011223345',
                'normalized_value' => 'mandiri:8900011223345',
                'verdict' => 'hati_hati',
                'risk_score' => 58,
                'report_count' => 41,
                'category' => 'jual_beli',
                'matched_keywords' => ['transfer dulu', 'harga miring', 'DP dulu'],
                'note' => 'Penjual gadget harga miring, minta transfer di muka.',
            ],
            [
                'type' => 'bank_account',
                'value' => 'BCA 8210011334',
                'normalized_value' => 'bca:8210011334',
                'verdict' => 'bahaya',
                'risk_score' => 90,
                'report_count' => 176,
                'category' => 'arisan_online',
                'matched_keywords' => ['arisan online', 'slot', 'cair'],
                'note' => 'Bandar arisan online yang kabur membawa dana.',
            ],
            [
                'type' => 'ewallet',
                'value' => 'DANA 628120011005',
                'normalized_value' => 'dana:+628120011005',
                'verdict' => 'bahaya',
                'risk_score' => 93,
                'report_count' => 201,
                'category' => 'petugas_palsu',
                'matched_keywords' => ['pegawai bank', 'dana verifikasi', 'dibekukan'],
                'note' => 'Akun DANA tujuan "dana verifikasi" petugas bank palsu.',
            ],
            [
                'type' => 'ewallet',
                'value' => 'OVO 628120011006',
                'normalized_value' => 'ovo:+628120011006',
                'verdict' => 'hati_hati',
                'risk_score' => 61,
                'report_count' => 37,
                'category' => 'hadiah_palsu',
                'matched_keywords' => ['giveaway', 'mencairkan'],
                'note' => 'Tujuan transfer "pajak hadiah" giveaway mencurigakan.',
            ],
            [
                'type' => 'ewallet',
                'value' => 'GoPay 628120011007',
                'normalized_value' => 'gopay:+628120011007',
                'verdict' => 'bahaya',
                'risk_score' => 79,
                'report_count' => 96,
                'category' => 'jual_beli',
                'matched_keywords' => ['transfer dulu', 'jangan lewat rekber'],
                'note' => 'Penjual tiket konser palsu, transaksi di luar platform.',
            ],
            [
                'type' => 'url',
                'value' => 'http://bca-verifikasi-aman.example/login',
                'normalized_value' => 'bca-verifikasi-aman.example/login',
                'verdict' => 'bahaya',
                'risk_score' => 96,
                'report_count' => 243,
                'category' => 'phising',
                'matched_keywords' => ['verifikasi akun', 'klik link', 'login ulang', 'http://'],
                'note' => 'Halaman phising tiruan internet banking.',
            ],
            [
                'type' => 'url',
                'value' => 'https://dana-undian-resmi.example/klaim',
                'normalized_value' => 'dana-undian-resmi.example/klaim',
                'verdict' => 'bahaya',
                'risk_score' => 84,
                'report_count' => 118,
                'category' => 'hadiah_palsu',
                'matched_keywords' => ['undian', 'klaim hadiah', 'terpilih'],
                'note' => 'Situs undian palsu mengatasnamakan e-wallet.',
            ],
            [
                'type' => 'url',
                'value' => 'http://jne-paket-tertahan.example/bayar',
                'normalized_value' => 'jne-paket-tertahan.example/bayar',
                'verdict' => 'bahaya',
                'risk_score' => 89,
                'report_count' => 154,
                'category' => 'phising',
                'matched_keywords' => ['biaya admin', 'klik link', 'http://'],
                'note' => 'Phising berkedok biaya admin paket tertahan.',
            ],
            [
                'type' => 'url',
                'value' => 'https://shopee-giveaway-menang.example',
                'normalized_value' => 'shopee-giveaway-menang.example',
                'verdict' => 'hati_hati',
                'risk_score' => 55,
                'report_count' => 22,
                'category' => 'hadiah_palsu',
                'matched_keywords' => ['giveaway', 'selamat'],
                'note' => 'Domain baru, pola giveaway, laporan masih sedikit.',
            ],
            [
                'type' => 'phone',
                'value' => '628120011008',
                'normalized_value' => '+628120011008',
                'verdict' => 'aman',
                'risk_score' => 8,
                'report_count' => 3,
                'category' => null,
                'matched_keywords' => [],
                'note' => 'Kontrol negatif: nomor kurir resmi, tidak ada laporan valid.',
            ],
        ];

        $now = now();
        $rows = [];
        foreach ($entities as $entity) {
            $rows[] = [
                'type' => $entity['type'],
                'value' => $entity['value'],
                'normalized_value' => $entity['normalized_value'],
                'verdict' => $entity['verdict'],
                'risk_score' => $entity['risk_score'],
                'report_count' => $entity['report_count'],
                'metadata' => json_encode([
                    'category' => $entity['category'],
                    'matched_keywords' => $entity['matched_keywords'],
                    'taxonomy_keywords' => $entity['category']
                        ? $keywordsOf($entity['category'])
                        : [],
                    'note' => $entity['note'],
                    'seed' => true,
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('scam_entities')->insert($rows);
    }
}
