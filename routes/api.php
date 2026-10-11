<?php

use App\Http\Controllers\Api\CheckController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ThreatFeedController;
use App\Http\Controllers\Api\WaWebhookController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------------------
// Waspada API
//
//   POST /api/check                 Cek nomor/rekening/URL/pesan -> verdict
//                                   AMAN / HATI-HATI / BAHAYA + penjelasan.
//   POST /api/reports               Kirim laporan penipuan dari komunitas.
//   GET  /api/reports               Daftar laporan komunitas terverifikasi.
//   GET  /api/threat-feed           Feed intelijen ancaman publik (JSON).
//   GET  /api/threat-feed/{entity}  Detail satu entitas ancaman.
// ---------------------------------------------------------------------------

Route::post('/check', CheckController::class)
    ->middleware('throttle:30,60');

Route::post('/reports', [ReportController::class, 'store'])
    ->middleware('throttle:10,60');

Route::get('/reports', [ReportController::class, 'index']);

Route::get('/threat-feed', [ThreatFeedController::class, 'index'])
    ->middleware('throttle:120,60');

Route::get('/threat-feed/{entity}', [ThreatFeedController::class, 'show'])
    ->middleware('throttle:120,60');

// ---------------------------------------------------------------------------
// WhatsApp bot (Meta Cloud API)
//
//   GET  /api/wa/webhook : verifikasi langganan webhook (hub.challenge).
//   POST /api/wa/webhook : terima pesan masuk, diverifikasi dengan
//                          X-Hub-Signature-256 (HMAC-SHA256).
// ---------------------------------------------------------------------------

Route::get('/wa/webhook', [WaWebhookController::class, 'verify'])
    ->middleware('throttle:120,1');

Route::post('/wa/webhook', [WaWebhookController::class, 'receive'])
    ->middleware('throttle:120,1');
