# 🏗️ Ringkasan Arsitektur Waspada

Dokumen ini menjelaskan bagaimana potongan-potongan Waspada tersusun dan bekerja sama. Ditujukan untuk kontributor baru yang ingin cepat paham sebelum menyentuh kode.

## 🗺️ Module Map

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php              # Base controller Laravel
│   │   └── Api/
│   │       ├── CheckController.php     # POST /api/check (single-action)
│   │       ├── ReportController.php    # POST+GET /api/reports
│   │       └── ThreatFeedController.php# GET /api/threat-feed[/{entity}]
│   ├── Requests/
│   │   ├── CheckRequest.php            # Validasi input pengecekan
│   │   └── ReportRequest.php           # Validasi input laporan
│   └── Middleware/                     # (Laravel bawaan + throttle)
├── Models/
│   ├── ScamEntity.php                  # Entitas ancaman (nomor/URL/rekening…)
│   ├── Report.php                      # Laporan komunitas
│   ├── Feedback.php                    # Umpan balik verdict
│   └── User.php                        # Pengguna (admin/moderator)
├── Services/
│   ├── ScamCheckService.php            # Orkestrasi alur pengecekan
│   ├── ScamScorer.php                  # Skor risiko 0–100 + verdict
│   └── PhoneNormalizer.php             # Normalisasi nomor Indonesia
└── Providers/
    └── AppServiceProvider.php

routes/
├── api.php                             # 5 endpoint publik (lihat docs/API.md)
└── web.php                             # Halaman Blade: beranda, checker, laporan

database/
├── migrations/                         # scam_entities, reports, feedback
├── seeders/ScamEntitySeeder.php         # Data awal (contoh fiktif)
└── data/scam_taxonomy.php               # Taksonomi 10 kategori penipuan

resources/views/                        # Layout + komponen Blade
public/js/waspada.js                    # Interaksi frontend standalone
```

## 🧩 Peran Tiap Komponen

| Komponen | Peran | Catatan |
|----------|-------|---------|
| `CheckController` | Menerima `POST /api/check`, memanggil `ScamCheckService`, mengembalikan JSON verdict | *Single-action controller* (`__invoke`) — endpoint ini hanya melakukan satu hal |
| `ReportController` | `store`: simpan laporan + buat/perbarui entitas + hitung ulang skor. `index`: daftar laporan terverifikasi | Satu-satunya controller yang menulis ke dua tabel sekaligus |
| `ThreatFeedController` | `index`: entitas `bahaya` terurut laporan terbanyak. `show`: detail satu entitas | Read-only, untuk konsumsi publik/ekstensi browser |
| `CheckRequest` / `ReportRequest` | Validasi & otorisasi input | Semua aturan validasi tinggal di sini, **bukan** di controller |
| `ScamEntity` | Satu baris = satu nilai unik (type + normalized_value) + agregatnya (report_count, risk_score, verdict) | Kunci dedup: pasangan `type` + `normalized_value` |
| `Report` | Satu baris = satu laporan mentah dari komunitas (`status`: pending → verified/rejected) | Laporan tidak pernah dihapus — jadi jejak audit |
| `Feedback` | Umpan balik pengguna atas sebuah verdict (akurat/tidak) | Bahan evaluasi kualitas skor di masa depan |
| `ScamCheckService` | Orkestrasi cek: normalisasi → cari entitas → gabung skor → hasil | Tidak tahu soal HTTP — murni logika domain |
| `ScamScorer` | Hitung skor 0–100 dari 3 sinyal + tentukan verdict | Threshold: `≥70` bahaya, `≥30` hati-hati, sisanya aman |
| `PhoneNormalizer` | `0812-…` / `+62…` → `62812…` (digit saja) | Static, tanpa state — gampang di-unit-test |

## 🔄 Alur Cek Nomor (`POST /api/check`)

```
Pengguna / Frontend
       │  { type: "phone", value: "0812-3456-7890" }
       ▼
CheckRequest ──► validasi type & value ──► 422 jika gagal
       │
       ▼
CheckController::__invoke
       │
       ▼
ScamCheckService::check()
       │
       ├─► PhoneNormalizer::normalize() → "6281234567890"
       │       (tipe lain: trim saja)
       │
       ├─► ScamEntity::where(type, normalized_value)->first()
       │
       ├─── Tidak ketemu ──► verdict "aman", skor 0
       │
       └─── Ketemu ──► ScamScorer::score()
                              │  sinyal: report_count,
                              │  umur entitas, kata kunci berbahaya
                              ▼
                       skor 0–100 + verdict
       │
       ▼
Response JSON: { data: { verdict, verdict_label, risk_score,
                         report_count, explanation, checked_at } }
```

## 📝 Alur Lapor (`POST /api/reports`)

```
Pelapor
       │  { type, value, category, description, ... }
       ▼
ReportRequest ──► validasi (kategori valid? deskripsi ≥10 char?) ──► 422 jika gagal
       │
       ▼
ReportController::store
       │
       ├─► 1. Report::create([... 'status' => 'pending' ])
       │
       ├─► 2. ScamEntity::firstOrCreate(type + normalized_value)
       │        → entitas baru mulai dari skor 0 / verdict "aman"
       │
       ├─► 3. $entity->increment('report_count')
       │
       ├─► 4. ScamScorer::extractDangerKeywords() dari deskripsi
       │
       ├─► 5. ScamScorer::score(report_count, umur, keywords)
       │        → skor & verdict baru
       │
       └─► 6. $entity->update([risk_score, verdict])
       │
       ▼
Response JSON 201: { data: { id, type, value, category,
                             status: "pending", created_at } }

Catatan: laporan berstatus "pending" sampai dimoderasi menjadi
"verified". Namun skor entitas langsung terpengaruh — inilah yang
membuat sistem responsif terhadap modus baru.
```

## 💡 Keputusan Desain (dan alasannya)

1. **Logika di Service, bukan Controller.**
   Controller hanya validasi + panggil service + format response. Akibatnya logika inti (`ScamCheckService`, `ScamScorer`) bisa di-unit-test tanpa menyentuh HTTP sama sekali.

2. **Entitas dideduplikasi via `normalized_value`.**
   Nomor `0812-3456-7890`, `+62 812-3456-7890`, dan `6281234567890` adalah orang yang sama — normalisasi kanonik mencegah satu penipu tercatat sebagai tiga entitas berbeda.

3. **Skor dihitung ulang setiap ada laporan baru.**
   Alih-alih skor statis, setiap laporan memicu `ScamScorer` ulang. Sistem "belajar" dari komunitas secara real-time tanpa perlu cron/job tambahan.

4. **Verdict tiga tingkat, bukan biner.**
   `HATI-HATI` memberi ruang untuk kasus abu-abu (mis. 1–2 laporan) tanpa menuduh sembarangan — penting untuk kepercayaan publik.

5. **Threat feed read-only & terpisah dari data pelapor.**
   `ThreatFeedController` hanya membaca `ScamEntity` berverdict bahaya; identitas/kontak pelapor tidak pernah terekspos. Privasi pelapor adalah fitur, bukan tambahan.

6. **Validasi terpusat di Form Request.**
   Aturan validasi hidup di `CheckRequest`/`ReportRequest`, sehingga controller tetap ramping dan aturan yang sama bisa dipakai ulang.

7. **Penjelasan berbahasa Indonesia sebagai first-class output.**
   `explanation` bukan sekadar log debug — ia tampil ke pengguna akhir. Setiap perubahan skor idealnya diiringi penjelasan yang masuk akal dibaca manusia.

## 🔮 Arah Pengembangan

- Moderasi admin (antrean `pending` → `verified`/`rejected`) — halaman admin sudah ada sebagai placeholder.
- Bobot skor per kategori & decay waktu (laporan lama bobotnya menurun).
- Notifikasi / langganan entitas (lapor balik jika nomor yang kamu cek berubah verdict).
- Ekstensi browser yang mengonsumsi `GET /api/threat-feed`.
