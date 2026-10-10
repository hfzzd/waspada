# Catatan Pemindaian Dependensi (Composer Audit) + Izin Lisensi

Tanggal: 2026-10-10
Divisi: security-scan
Cabang: `feat/security-scan`

## 1. Pernyataan jujur tentang keterbatasan

**`composer audit` TIDAK dapat dijalankan di VPS ini** karena PHP dan Composer
tidak terpasang. Dokumen ini BUKAN hasil pemindaian otomatis — ia berisi:

- (a) **Review manual** dependensi pada `composer.json` terhadap advisory
      keamanan yang diketahui publik (GitHub Advisory Database, CVE, Snyk).
- (b) **Instruksi wajib untuk CI** agar `composer audit` dijalankan otomatis.
- (c) **Tabel lisensi** dependensi utama.

Selain itu, repo ini **belum memiliki `composer.lock`** (tidak ter-commit),
sehingga versi pasti yang ter-resolve tidak dapat diverifikasi di sini.
Review manual di bawah ini bersifat **level constraint** (rentang versi),
bukan level versi pasti. Hasil otoritatif tetap menunggu `composer audit`
di CI.

> Jangan perlakukan dokumen ini sebagai "hasil scan bersih". Anggap sebagai
> temuan awal yang WAJIB dikonfirmasi ulang oleh CI.

## 2. Dependensi utama (`composer.json`)

| Paket | Constraint | Peran |
|---|---|---|
| `php` | `^8.2` | Runtime |
| `laravel/framework` | `^11.0` | Framework inti |
| `laravel/sanctum` | `^4.0` | Autentikasi API token |
| `laravel/tinker` | `^2.9` | REPL dev |
| `fakerphp/faker` | `^1.23` | Data dummy (dev) |
| `laravel/pint` | `^1.13` | Code style (dev) |
| `mockery/mockery` | `^1.6` | Mocking test (dev) |
| `nunomaduro/collision` | `^8.1` | Error handler CLI (dev) |
| `phpunit/phpunit` | `^11.0` | Testing (dev) |

Lisensi root package: **MIT** (`"license": "MIT"` di `composer.json`).

## 3. Review manual terhadap advisory yang diketahui

Pengecekan dilakukan 2026-10-10 terhadap basis data advisory publik.
Karena tidak ada `composer.lock`, setiap entri di bawah dinilai terhadap
**rentang constraint**, bukan versi terinstal.

### 3.1 `laravel/framework` (`^11.0`) — PERLU PERHATIAN

Ditemukan beberapa advisory yang mencakup Laravel 11.x:

1. **CVE-2025-27515** (Moderate) — *Wildcard validation bypass*:
   memungkinkan bypass aturan validasi file dengan validasi wildcard,
   berpotensi mengarah ke upload file berbahaya. **Terdampak: semua versi
   Laravel sebelum 11.44.1** (ditambal di 11.44.1 dan 12.1.1).
   Constraint `^11.0` mengizinkan versi rentan terinstal.
2. **GHSA-5vg9-5847-vvmq / CVE-2026-48019** (High) — *CRLF injection*
   pada logika validasi email bawaan Laravel (CWE-93): input email berisi
   `\r\n` dapat menyuntikkan header email tambahan (mis. `Bcc:` diam-diam).
   Tidak butuh autentikasi; relevan untuk form registrasi/kontak.
3. **GHSA-crmm-hgp2-wgrp** (Moderate) — advisory Laravel 11.x (detail
   mengikuti basis data GitHub Advisory).
4. **AIKIDO-2025-10064** (Low) — *Sensitive information in log file*:
   versi 8.16.0–11.41.0 dapat mencatat kredensial DB ke log saat
   `php artisan db` gagal (diperbaiki di 11.41.1). Dampak rendah untuk
   Waspada (tidak memakai pola tersebut), tetap dicatat.
5. **CVE-2024-13919** (XSS, halaman error debug): terdampak 11.9.0–11.35.1,
   ditambal 11.36.0. Hanya relevan jika `APP_DEBUG=true` — pastikan
   `APP_DEBUG=false` di production (sudah menjadi standar).

**Rekomendasi:** naikkan batas bawah menjadi `"laravel/framework": "^11.44.1"`
(atau jalankan `composer update` ke 11.x terbaru) lalu kunci via
`composer.lock`. Waspada memakai validasi nomor telepon/rekening di endpoint
`/check` — CVE-2025-27515 relevan secara tematis dengan area validasi.

### 3.2 Dependensi dev — perlu verifikasi CI

- **`phpunit/phpunit` (`^11.0`)**: sebuah ringkasan komunitas melaporkan
  CVE-2026-24765 (High) pada rentang 11.x — **belum terverifikasi dari sumber
  primer**; statusnya "perlu dikonfirmasi `composer audit`", bukan temuan pasti.
- **Dependensi transitif** (carbon, symfony/http-foundation, psy/psysh):
  beberapa advisory beredar untuk versi lama; tanpa `composer.lock` tidak
  dapat dipetakan — sepenuhnya didelegasikan ke CI.

### 3.3 `laravel/sanctum` (`^4.0`)

Tidak ditemukan advisory keamanan spesifik untuk Sanctum 4.x pada
pengecekan manual ini. Tetap wajib lolos `composer audit` di CI.

### 3.4 Ringkasan status review manual

| Paket | Hasil review manual | Status akhir |
|---|---|---|
| `laravel/framework` | Advisory diketahui mencakup rentang `^11.0` | ⚠️ Perlu update ke ≥ 11.44.1 + verifikasi CI |
| `laravel/sanctum` | Tidak ada advisory ditemukan | ✅ Sementara aman, verifikasi CI |
| `laravel/tinker` | Tidak ada advisory ditemukan | ✅ Sementara aman, verifikasi CI |
| `fakerphp/faker` | Tidak ada advisory ditemukan | ✅ Sementara aman, verifikasi CI |
| `laravel/pint` | Tidak ada advisory ditemukan | ✅ Sementara aman, verifikasi CI |
| `mockery/mockery` | Tidak ada advisory ditemukan | ✅ Sementara aman, verifikasi CI |
| `nunomaduro/collision` | Tidak ada advisory ditemukan | ✅ Sementara aman, verifikasi CI |
| `phpunit/phpunit` | Satu laporan belum terverifikasi | ⚠️ Verifikasi CI |

## 4. Instruksi wajib untuk CI (`.github/workflows/ci.yml`)

Pipeline CI **WAJIB** memuat step berikut, dan step ini **harus menggagalkan
build** jika ada kerentanan:

```yaml
- name: Composer audit (dependency vulnerability scan)
  run: composer audit --no-interaction
```

Ketentuan:
- `composer audit` mengembalikan exit code non-nol saat ada advisory yang
  cocok dengan `composer.lock` — JANGAN tambahkan `|| true` atau flag yang
  melemahkan efek gagalnya.
- Job instalasi dependensi harus memakai `composer install` dari
  `composer.lock` yang ter-commit (bukan `composer update`), agar hasil audit
  deterministik dan dapat direproduksi.
- Jika suatu advisory belum ada patch dan terpaksa diabaikan sementara,
  gunakan mekanisme `audit.ignore` di `composer.json` dengan komentar berisi:
  alasan, tanggal, dan tenggat review ulang — JANGAN mengabaikan diam-diam.

Selain itu, `composer.lock` **wajib di-commit** ke repo (saat ini belum ada).
Tanpa lock file, audit tidak deterministik dan review seperti dokumen ini
tidak dapat dipetakan ke versi pasti.

## 5. Tabel lisensi dependensi utama

Semua dependensi di bawah berlisensi permisif dan **kompatibel dengan
lisensi MIT** yang dipakai proyek ini (tidak ada copyleft seperti GPL).

| Paket | Lisensi | Kompatibel MIT? |
|---|---|---|
| `laravel/framework` | MIT | ✅ Ya |
| `laravel/sanctum` | MIT | ✅ Ya |
| `laravel/tinker` | MIT | ✅ Ya |
| `fakerphp/faker` | MIT | ✅ Ya |
| `laravel/pint` | MIT | ✅ Ya |
| `mockery/mockery` | BSD-3-Clause | ✅ Ya (permisif) |
| `nunomaduro/collision` | MIT | ✅ Ya |
| `phpunit/phpunit` | BSD-3-Clause | ✅ Ya (permisif) |

Catatan: lisensi di atas adalah lisensi kanonis masing-masing paket menurut
dokumentasi publiknya. Dependensi transitif (puluhan paket Symfony, dsb.)
mengikuti pola lisensi permisif yang sama (mayoritas MIT); verifikasi
lengkap dependensi transitif didelegasikan ke `composer licenses` /
`composer audit` di CI setelah `composer.lock` tersedia.

## 6. Tindak lanjut yang disarankan

1. Commit `composer.lock` ke repo (prioritas).
2. Naikkan `laravel/framework` ke `^11.44.1` (menutup CVE-2025-27515 dkk).
3. Pastikan `APP_DEBUG=false` di production (mitigasi CVE-2024-13919).
4. Tambahkan step `composer audit` yang menggagalkan build di CI (bagian 4).
5. Jadwalkan audit ulang tiap rilis dependensi / minimal tiap sprint.

---
*Dokumen ini disusun manual tanpa menjalankan `composer audit` (tidak tersedia
di lingkungan ini). Bagian yang ditandai "verifikasi CI" belum terkonfirmasi
dan tidak boleh dikutip sebagai hasil pemindaian.*
