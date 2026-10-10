# 🛡️ Waspada

**Waspada** adalah platform intelijen penipuan (*scam intelligence*) open-source untuk Indonesia.

Pengguna dapat mengecek nomor telepon, nomor rekening bank/e-wallet, URL, alamat email, dan isi pesan WhatsApp/SMS — lalu menerima verdict **AMAN / HATI-HATI / BAHAYA** beserta penjelasan dalam Bahasa Indonesia. Laporan dari komunitas dikurasi lewat moderasi dan dipublikasikan sebagai *threat feed* terbuka yang bisa dikonsumsi aplikasi lain maupun ekstensi browser.

> Misi kami sederhana: bikin satu tempat rujukan yang bisa dipercaya sebelum kamu transfer uang atau klik tautan yang mencurigakan. 💚

## ✨ Fitur

- **Pengecekan instan** — tempel nomor/URL/rekening/pesan, dapatkan verdict + penjelasan dalam hitungan detik (`POST /api/check`).
- **Laporan komunitas** — siapa pun bisa melaporkan modus penipuan baru lengkap dengan kategori dan bukti (`POST /api/reports`).
- **Threat feed publik** — daftar entitas berbahaya dalam format JSON terbuka, siap dikonsumsi ekstensi browser atau aplikasi pihak ketiga (`GET /api/threat-feed`).
- **Penjelasan berbahasa Indonesia** — setiap verdict disertai alasan yang mudah dipahami, bukan sekadar skor angka.
- **Taksonomi penipuan Indonesia** — 10 kategori modus penipuan lokal yang terdokumentasi (lihat `docs/TAKSONOMI_PENIPUAN.md`).

## 🧰 Stack

| Lapisan      | Teknologi                              |
|--------------|----------------------------------------|
| Backend      | Laravel 11 (PHP ^8.2), MySQL / SQLite  |
| Frontend     | Blade + Tailwind CSS + Alpine.js (PWA)  |
| Autentikasi  | Laravel Sanctum                        |
| Testing      | PHPUnit                                |
| Build aset   | Vite                                   |
| Deployment   | Docker + Docker Compose                |

## ✅ Prasyarat

- PHP **^8.2** dengan ekstensi: `mbstring`, `pdo`, `pdo_mysql`/`pdo_sqlite`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- [Composer](https://getcomposer.org/) 2.x
- [Node.js](https://nodejs.org/) 18+ dan npm
- MySQL 8+ (produksi) atau SQLite (development cepat)
- (Opsional) Docker + Docker Compose untuk menjalankan via container

## 🚀 Setup Lokal

```bash
# 1. Install dependensi PHP
composer install

# 2. Salin file environment lalu sesuaikan
cp .env.example .env
# -> atur DB_CONNECTION, DB_DATABASE, dst. Untuk coba-coba cepat,
#    biarkan default SQLite.

# 3. Generate application key
php artisan key:generate

# 4. Siapkan database
touch database/database.sqlite   # jika memakai SQLite
php artisan migrate

# 5. (Opsional) Isi data awal: taksonomi + contoh entitas fiktif
php artisan db:seed

# 6. Install dependensi frontend & jalankan Vite
npm install
npm run dev

# 7. Jalankan server development
php artisan serve
```

Buka http://localhost:8000 🎉

### Menjalankan test

```bash
php artisan test
```

## 🐳 Menjalankan dengan Docker

Cara paling ringkas untuk menjalankan seluruh stack (aplikasi + MySQL):

```bash
# Build & jalankan semua service
docker compose up --build

# Atau berjalan di background
docker compose up -d --build
```

Service yang tersedia:

| Service | Isi                              | Port        |
|---------|----------------------------------|-------------|
| `app`   | Aplikasi Laravel (PHP-FPM/Nginx) | `8000`      |
| `db`    | MySQL 8                          | `3306`      |

Variabel environment database diatur lewat file `.env` (lihat `.env.example`). Untuk menghentikan: `docker compose down`.

## 🌐 Deployment Singkat

1. **Siapkan server** dengan Docker terinstal (atau PHP 8.2 + MySQL bila tanpa Docker).
2. **Clone repo & konfigurasi environment:**
   ```bash
   git clone https://github.com/hfzzd/waspada.git
   cd waspada
   cp .env.example .env
   # Isi APP_KEY, DB_*, dan APP_URL yang benar, set APP_ENV=production, APP_DEBUG=false
   ```
3. **Jalankan migrasi & optimasi:**
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
4. **Pastikan storage & cache bisa ditulis** web server (`storage/`, `bootstrap/cache/`).
5. **Aktifkan HTTPS** (wajib untuk PWA dan keamanan data pelapor) — gunakan reverse proxy (Nginx/Caddy) dengan sertifikat Let's Encrypt.

> 💡 Untuk checklist deployment yang lebih rinci (backup DB, queue, monitoring), lihat `docs/ARSITEKTUR.md`.

## 🗂️ Struktur Direktori

```
waspada/
├── app/
│   ├── Http/Controllers/Api/   # CheckController, ReportController, ThreatFeedController
│   ├── Http/Requests/          # CheckRequest, ReportRequest (validasi)
│   ├── Models/                 # ScamEntity, Report, Feedback, User
│   └── Services/               # ScamCheckService, ScamScorer, PhoneNormalizer
├── routes/
│   ├── api.php                 # Endpoint publik + threat feed
│   └── web.php                 # Halaman Blade (beranda, checker, laporan)
├── database/
│   ├── migrations/             # Skema scam_entities, reports, feedback
│   ├── seeders/                # Data awal taksonomi & contoh entitas
│   └── data/scam_taxonomy.php  # Taksonomi 10 kategori penipuan
├── resources/views/            # Layout & komponen Blade
├── public/js/waspada.js        # Interaksi frontend (dark mode, checker)
├── docs/                       # Dokumentasi proyek (kamu sedang membacanya 😄)
└── tests/                      # PHPUnit (unit & feature)
```

## 🏗️ Arsitektur Singkat

Waspada mengikuti pola MVC Laravel yang ramping: **Model** (`ScamEntity`, `Report`, `Feedback`) menyimpan entitas ancaman dan laporan komunitas; **Service** (`ScamCheckService`, `ScamScorer`, `PhoneNormalizer`) berisi seluruh logika inti — normalisasi nomor Indonesia ke format kanonik `62…`, penghitungan skor risiko 0–100 dari tiga sinyal (jumlah laporan, umur entitas, kata kunci berbahaya), dan penentuan verdict; **Controller API** hanya menjadi lapisan tipis validasi + serialisasi JSON, sehingga logika bisnis tetap terpusat dan mudah diuji. Laporan komunitas masuk berstatus `pending`, memperbarui `report_count` entitas terkait, lalu skor/verdict dihitung ulang otomatis. Ringkasan lengkap ada di `docs/ARSITEKTUR.md`.

## 📚 Dokumentasi

| Dokumen | Isi |
|---------|-----|
| `docs/API.md` | Dokumentasi endpoint publik & threat feed (parameter, contoh JSON, rate limit) |
| `docs/ARSITEKTUR.md` | Module map, peran komponen, alur cek & lapor, keputusan desain |
| `docs/TAKSONOMI_PENIPUAN.md` | 10 kategori modus penipuan di Indonesia |
| `docs/PROJECT_BRIEF.md` | Brief proyek & pembagian tugas divisi |
| `CONTRIBUTING.md` | Cara berkontribusi (aturan commit kecil, PR, code review) |

## 🤝 Berkontribusi

Kami senang banget kalau kamu mau ikut membantu! Baca dulu `CONTRIBUTING.md` — intinya: bikin perubahan dalam **commit kecil yang logis**, tulis pesan commit yang jelas, dan ajukan PR untuk direview. 💪

## Struktur Tim

Lihat `docs/PROJECT_BRIEF.md` untuk pembagian tugas per divisi (20 agent).

## 📄 Lisensi

MIT — bebas dipakai untuk kebaikan. 💚
