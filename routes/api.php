<?php

use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Waspada API (akan diimplementasikan oleh tim backend-api)
//
//   POST /api/check        Cek pesan/URL/nomor telepon/rekening -> verdict
//                          AMAN / HATI-HATI / BAHAYA + penjelasan (id)
//   POST /api/reports      Kirim laporan penipuan dari komunitas
//   GET  /api/reports      Daftar laporan komunitas (lolos moderasi)
//   GET  /api/threat-feed  Feed intelijen ancaman publik (JSON, untuk
//                          integrasi pihak ketiga & ekstensi browser)
// ---------------------------------------------------------------------------
