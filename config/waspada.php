<?php

/**
 * Konfigurasi khusus Waspada.
 *
 * Catatan: env() hanya boleh dipanggil di file config (idiom Laravel) —
 * kode aplikasi membaca via config(), agar bisa di-override di test
 * dan tetap benar saat config:cache.
 */
return [
    'wa_verify_token' => env('WA_VERIFY_TOKEN'),
    'wa_app_secret' => env('WA_APP_SECRET'),
    'wa_phone_number_id' => env('WA_PHONE_NUMBER_ID'),
    'wa_access_token' => env('WA_ACCESS_TOKEN'),
];
