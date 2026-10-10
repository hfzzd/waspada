# Checklist E2E — Happy Path Waspada

Checklist pengujian end-to-end manual untuk alur utama aplikasi Waspada.
Jalankan berurutan di browser (Chrome/Firefox) dengan server lokal menyala
(`php artisan serve` → http://localhost:8000).

## Alur Utama

1. **Buka beranda** — Akses `GET /`. Halaman beranda tampil tanpa error,
   navigasi (Beranda, Cek, Komunitas) terlihat dan bisa diklik.

2. **Cek nomor valid → verdict muncul** — Buka halaman `/cek`, pilih jenis
   "Nomor HP / Telepon", masukkan nomor contoh `081234567890`, klik
   "Periksa". Badge verdict (AMAN / HATI-HATI / BAHAYA) muncul di area hasil
   beserta skor risiko dan penjelasan.

3. **Cek nomor invalid → error ramah** — Di halaman `/cek`, submit form
   dengan kolom isi dikosongkan. Pesan error validasi yang ramah tampil
   (bukan halaman error 500 / halaman kosong).

4. **Submit laporan → muncul di daftar** — Buka halaman `/komunitas`, klik
   "Lapor Penipuan", isi formulir (jenis: Nomor HP, kategori: Phising,
   nilai: `081234567890`, deskripsi minimal 10 karakter), lalu kirim.
   Muncul notifikasi sukses. Setelah laporan diverifikasi admin, laporan
   tampil di daftar Laporan Komunitas.

5. **Buka threat feed** — Akses `GET /api/threat-feed` langsung di browser
   atau via halaman terkait. Respons JSON berisi daftar entitas ancaman
   (bukan error). Buka salah satu detail `GET /api/threat-feed/{entity}`
   dan pastikan datanya tampil.

6. **Toggle dark mode** — Klik tombol toggle tema di navbar. Seluruh halaman
   (beranda, cek, komunitas) berubah ke mode gelap/terang tanpa teks yang
   hilang atau kontras yang rusak. Refresh halaman — pilihan tema tetap
   tersimpan.

7. **Cek tampilan mobile** — Buka DevTools → mode responsive (lebar 360px).
   Navigasi berubah menjadi menu hamburger yang bisa dibuka-tutup, form cek
   dan form lapor tetap bisa diisi dan disubmit, tidak ada elemen yang
   terpotong atau tumpang tindih.

## Catatan

- Untuk smoke test API otomatis, jalankan `bash scripts/smoke-test.sh`
  (atau dengan `BASE_URL` lain, mis. `BASE_URL=http://waspada.test bash scripts/smoke-test.sh`).
- Checklist ini mencakup happy-path saja; kasus negatif/edge-case dicatat
  terpisah oleh divisi qa-unit.
