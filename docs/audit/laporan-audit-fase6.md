# Audit Keamanan Fase 6 — Kode Baru Day-3 Waspada

Tanggal: 2026-10-11. Ruang lingkup: HANYA kode baru Day-3 (read-only review,
tidak ada file yang diubah). Kode lama (Fase 1–5) tidak diaudit ulang.

## File yang diaudit

- `app/Http/Controllers/Api/WaWebhookController.php` — webhook Meta Cloud API
  (GET verifikasi, POST signature check)
- `app/Services/WaBotService.php` — penanganan pesan, perintah `/lapor`,
  pengiriman via Graph API
- `routes/api.php` — seksi route webhook WhatsApp (baris 34–46)
- `extension/manifest.json`, `extension/popup.js`, `extension/background.js`,
  `extension/options.js` — ekstensi browser Manifest V3
- Konteks dibaca: `app/Services/ScamCheckService.php` (query Eloquent
  berparameter — aman), `app/Http/Requests/ReportRequest.php` (aturan validasi
  yang dipakai ulang `/lapor`), `docs/audit/laporan-audit-v1.md` (penomoran
  WAS-SEC-001…006, konvensi severity)

## Temuan (4) — semuanya nyata, tidak ada yang hipotetis

### W07 / WAS-SEC-007 — P2: Webhook WA tanpa rate limit per-pengirim
**File:** `app/Http/Controllers/Api/WaWebhookController.php` (baris 44–63),
`app/Services/WaBotService.php` (`handleIncoming`, `handleReport`),
`routes/api.php` (baris 45–46).

Setiap pengguna WhatsApp di dunia yang mengetahui nomor bot dapat mengirim
pesan teks dalam jumlah tak terbatas. Setiap pesan memicu:
(1) satu query DB via `ScamCheckService::check()`, dan (2) satu panggilan
berbayar ke Graph API via `sendText()`. Perintah `/lapor` membuat baris
`Report` baru setiap kali tanpa batas per-pengirim.

`throttle:120,1` pada route TIDAK membedakan pengirim — Laravel menghitungnya
per IP sumber, dan semua request webhook datang dari IP egress Meta (kumpulan
kecil yang dipakai bersama semua pengguna WA). Efektif: tidak ada batas
per-nomor. Dampak: polusi tabel `Report` (beban moderator), biaya API
WhatsApp, dan beban DB. Berbeda dengan `/api/reports` (throttle 10,60 per IP
pengguna asli), jalur WA tidak punya padanannya.

CWE-770. Ekspektasi SAST: tidak terdeteksi (logika bisnis + konfigurasi
throttle, tanpa pola sintaks).

### W08 / WAS-SEC-008 — P3: Nomor HP (PII) dicatat ke log aplikasi
**File:** `app/Services/WaBotService.php` (`sendText`, baris 122–149).

Setiap kegagalan pengiriman mencatat `'to' => $to` (nomor WhatsApp pengguna)
ke log via `Log::warning`, di dua jalur (respons gagal dan exception).
Nomor HP adalah PII; file log aplikasi umumnya berumur panjang, diakses lebih
luas daripada DB, dan sering diteruskan ke sistem log terpusat.

CWE-532. Ekspektasi SAST: tidak terdeteksi (butuh taint-tracking PII →
fungsi log; pola umum tidak cukup).

### W09 / WAS-SEC-009 — P3: Verifikasi webhook GET fail-open bila token kosong
**File:** `app/Http/Controllers/Api/WaWebhookController.php` (`verify`,
baris 25–35).

`hash_equals((string) env('WA_VERIFY_TOKEN'), (string) $request->query('hub.verify_token'))`.
Bila operator lupa menyetel `WA_VERIFY_TOKEN` (atau `env()` mengembalikan
null, mis. pada deploy dengan config cache), kedua sisi menjadi string kosong
dan `hash_equals('', '') === true` — verifikasi lolos untuk challenge
sembarang. Dampak terbatas: endpoint hanya menggemakan `hub.challenge`
sebagai `text/plain` (tidak ada XSS — content-type teks polos), tetapi ini
pola fail-open yang nyata dan deterministik, bukan hipotetis.

CWE-1188. Ekspektasi SAST: mungkin terdeteksi sebagian (pola perbandingan
dengan nilai yang bisa kosong).

### W10 / WAS-SEC-010 — P3: Ekstensi mengizinkan API base HTTP (cleartext)
**File:** `extension/options.js` (baris 21 — regex `/^https?:\/\//i`),
`extension/popup.js` (baris 4, 57).

Validasi hanya mewajibkan skema `http://` atau `https://`. URL halaman yang
dicek dikirim sebagai JSON ke server tersebut — URL dapat memuat token sesi,
link reset password, atau query sensitif di query string. Bila pengguna
mengarahkan ekstensi ke server `http://`, seluruh riwayat URL yang dicek
terkirim tanpa enkripsi dan dapat disadap jaringan.

Catatan jujur: default `http://localhost:8000` aman (tidak keluar mesin);
masalah muncul pada konfigurasi server remote via HTTP. CWE-319.
Ekspektasi SAST: tidak berlaku — kode JS ekstensi di luar cakupan SAST PHP
(Semgrep/Progpilot/SonarQube PHP).

## True negative (dicek, terbukti aman)

- **N06** — Verifikasi HMAC `X-Hub-Signature-256` timing-safe:
  `hash_equals('sha256='.hash_hmac('sha256', $request->getContent(), $secret), $header)`
  (`WaWebhookController.php`, baris 72–86). Raw body + perbandingan
  constant-time + pengecekan prefix `sha256=` terlebih dahulu. Tidak ada
  timing leak, tidak ada algo-confusion (algoritma hardcoded `sha256`).
- **N07** — Ekstensi me-render respons API hanya via `textContent`
  (`popup.js`, `showResult`): `reasonsEl.innerHTML = ''` lalu
  `li.textContent = String(r)`. Data dari server tidak pernah masuk sink
  `innerHTML` — tidak ada XSS reflektif dari respons API.
- **N08** — Pembuatan `Report` via `/lapor` memakai array eksplisit
  (`WaBotService.php`, baris 101–108) dengan `'status' => 'pending'` yang
  di-hardcode — tidak memakai `$request->all()`. Pola WAS-SEC-004
  (mass assignment) dihindari dengan benar di kode baru ini.

## Observasi non-keamanan (bukan sampel korpus)

1. `env()` dipanggil langsung di controller/service, bukan via `config/`.
   Pada deploy dengan `php artisan config:cache`, `env()` mengembalikan null
   — rahasia webhook menjadi string kosong (memperkuat W09) dan bot rusak
   total (semua signature Meta gagal). Rekomendasi: pindahkan ke
   `config/services.php` + `config()` helper. (Dockerfile saat ini tidak
   menjalankan config:cache, jadi ini hardening, bukan temuan aktif.)
2. `popup.js` membaca `data.verdict` / `data.reasons` di top-level, sedangkan
   API mengembalikan `{data: {verdict, explanation, ...}}` — badge selalu
   tampil "?" dan alasan kosong. Bug fungsional (perlu perbaikan), bukan
   masalah keamanan.
3. Replay payload webhook bertanda-tangan valid tidak didedup — dampak
   tercakup dalam W07 (tidak dijadikan temuan terpisah agar tidak ganda).
