<?php

namespace App\Services;

/**
 * Normalisasi nomor telepon Indonesia ke format kanonik.
 *
 * Format kanonik adalah digit saja tanpa spasi, tanda baca, atau tanda plus,
 * diawali kode negara "62" (contoh: "0812-3456-7890" menjadi "6281234567890").
 */
class PhoneNormalizer
{
    /**
     * Ubah nomor telepon ke format kanonik Indonesia.
     *
     * Aturan normalisasi:
     *  - Semua karakter non-digit dihapus (spasi, strip, kurung, titik, "+").
     *  - Awalan lokal "08xx..." diubah menjadi "628xx...".
     *  - Awalan "+62..." menjadi "62..." (tanda "+" ikut terhapus).
     *  - Awalan "62..." dibiarkan apa adanya.
     *
     * @param  string  $input  Nomor telepon dalam format bebas.
     * @return string  Nomor dalam format kanonik (hanya digit).
     */
    public static function normalize(string $input): string
    {
        // Hapus semua karakter non-digit, termasuk tanda "+".
        $digits = preg_replace('/\D+/', '', $input) ?? '';

        // Nomor lokal "08xx..." -> "628xx...".
        if (str_starts_with($digits, '08')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }
}
