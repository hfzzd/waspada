# WASPADA — Project Brief

> Platform intelijen penipuan (scam) open-source untuk Indonesia.

## Visi
Satu tempat terpercaya bagi masyarakat Indonesia untuk **memeriksa** pesan/URL/nomor
telepon/rekening yang mencurigakan **sebelum** menjadi korban penipuan, sekaligus
berkontribusi melaporkan penipuan baru agar orang lain terlindungi.

## MVP (Fase 1) — Web Checker + Laporan Komunitas
- [x] Scaffold proyek Laravel 11
- [ ] Skema database: `scam_entities`, `reports`, `feedback`
- [ ] Endpoint `POST /api/check` — verdict AMAN / HATI-HATI / BAHAYA + penjelasan
- [ ] Endpoint `POST /api/reports` — kirim laporan komunitas
- [ ] Endpoint `GET /api/threat-feed` — feed publik entitas berbahaya
- [ ] Halaman: landing, checker, komunitas, admin moderasi
- [ ] Normalisasi nomor HP Indonesia (+62, 08xx, dst.)
- [ ] Skor + deduplikasi laporan
- [ ] Docker + docker-compose + CI GitHub Actions
- [ ] README, dokumentasi API, panduan kontribusi

## Pasca-MVP (Fase 2)
- Ekstensi browser (peringatan situs phishing)
- Bot WhatsApp untuk cek cepat
- PWA + mode gelap/terang
- Peta hotspot penipuan

## Arsitektur
```
waspada/
├── app/
│   ├── Http/Controllers/Api/   # CheckController, ReportController, ThreatFeedController
│   ├── Models/                 # ScamEntity, Report, Feedback
│   └── Services/               # ScamCheckService, PhoneNormalizer, ScamScorer
├── database/migrations/
├── resources/views/            # Blade + Tailwind + Alpine.js
├── routes/api.php
├── routes/web.php
└── tests/
```

## Aturan Main
1. Commit kecil & sering — satu langkah logis per commit.
2. Semua commit atas nama pemilik repo (Hafizd Ammar).
3. Bahasa Indonesia untuk UI & dokumentasi pengguna.
4. Jangan pernah commit secret / API key.
5. Setiap endpoint publik wajib rate-limit.
