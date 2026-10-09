# 🛡️ Waspada

**Waspada** adalah platform intelijen penipuan (*scam intelligence*) open-source untuk Indonesia.

Pengguna dapat mengecek pesan WhatsApp/SMS, URL, nomor telepon, dan nomor rekening bank/e-wallet — lalu menerima verdict **AMAN / HATI-HATI / BAHAYA** beserta penjelasan dalam Bahasa Indonesia. Laporan dari komunitas dikurasi lewat moderasi dan dipublikasikan sebagai feed intelijen ancaman terbuka.

## Stack

- **Backend:** Laravel 11 (PHP ^8.2), MySQL / SQLite (dev)
- **Frontend:** Blade + Tailwind CSS + Alpine.js (PWA)
- **Autentikasi API:** Laravel Sanctum
- **Testing:** PHPUnit
- **Build:** Vite

## Cara Menjalankan (Development)

```bash
# 1. Install dependensi PHP
composer install

# 2. Salin file environment
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Siapkan database (default: SQLite)
touch database/database.sqlite
php artisan migrate

# 5. Install dependensi frontend & jalankan Vite
npm install
npm run dev

# 6. Jalankan server development
php artisan serve
```

Buka http://localhost:8000 🎉

## Struktur Tim

Lihat `docs/PROJECT_BRIEF.md` untuk pembagian tugas per divisi (20 agent).

## Lisensi

MIT — bebas dipakai untuk kebaikan. 💚
