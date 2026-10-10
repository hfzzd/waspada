# Laporan Audit Keamanan v1 — Waspada

**Tanggal:** 2026-10-10
**Ruang lingkup:** `app/Models`, `app/Http/Controllers`, `app/Http/Requests`,
`app/Services`, `routes/api.php`, `routes/web.php`, `database/migrations`,
`resources/views` (komponen + halaman terkait).
**Basis kode:** `main@c62069c` (29 commit Day-1).

## Ringkasan eksekutif

Audit statis terstruktur menemukan **6 temuan nyata**: 4 berprioritas **P1**,
1 berprioritas **P2**, dan 1 berprioritas **P3**. Tidak ada temuan P0
(eksekusi kode jarak jauh / pembobolan data massal) — permukaan serangan
aplikasi ini sempit karena belum ada autentikasi pengguna sama sekali
(semua endpoint publik), sehingga kelas kerentanan dominan adalah
**kontrol akses yang hilang**, **keputusan keamanan dari input tak
terverifikasi**, dan **inkonsistensi antar-lapisan**.

Tiga temuan P1 menyentuh integritas produk secara langsung:

- **WAS-SEC-001** — endpoint detail threat-feed melewati filter kurasi
  "hanya verdict bahaya" sehingga entitas yang belum terverifikasi bisa
  dibaca lewat ID.
- **WAS-SEC-003** — laporan anonim (tanpa login) dapat mendorong verdict
  sebuah nomor/entitas menjadi **BAHAYA** — vektor pencemaran nama
  (defamation) terhadap nomor yang sah.
- **WAS-SEC-006** — `type=message` lolos validasi dan ditawarkan di UI,
  tetapi ditolak enum database → error 500 di MySQL produksi.

Semua temuan P1 diperbaiki dalam commit lanjutan
`security: perbaiki temuan audit prioritas P0/P1`. Temuan P2/P3
didokumentasikan sebagai rekomendasi.

**Keterbatasan:** VPS tidak memiliki runtime PHP/Composer, sehingga audit
ini murni analisis statis (baca kode + penalaran alur). Tidak ada eksekusi
migrasi, tidak ada uji dinamis. Seluruh temuan di bawah dikutip dengan
bukti `file:baris` yang terverifikasi di kode.

## Metodologi

1. **Enumerasi attack surface** — petakan semua route (`routes/api.php`,
   `routes/web.php`) beserta middleware-nya.
2. **Uji tiap endpoint** — telusuri alur request → validasi
   (`FormRequest`) → controller → model/service → database; catat
   setiap titik di mana input pengguna memengaruhi keputusan keamanan.
3. **Periksa model** — `$fillable`/`$guarded`, relasi, dan field sensitif.
4. **Periksa konsistensi antar-lapisan** — apakah nilai yang diterima
   validasi juga diterima database (temuan WAS-SEC-006 lahir dari sini,
   atas masukan verifikasi divisi qa-unit).
5. **Periksa view** — pastikan output Blade di-escape (`{{ }}`) dan tidak
   ada `{!! !!}` dari input pengguna.
6. Setiap temuan **hanya dicatat bila ada bukti baris kode** — tidak ada
   temuan spekulatif.

## Daftar temuan

### WAS-SEC-001 — [P1] Detail threat-feed melewati filter kurasi verdict

- **Bukti:** `app/Http/Controllers/Api/ThreatFeedController.php:33-38`
  (`show()`), bandingkan dengan `index()` di baris 21-29 yang memfilter
  `where('verdict', 'bahaya')`.
- **Deskripsi:** `index()` hanya mempublikasikan entitas berverdict
  "bahaya", tetapi `show(ScamEntity $entity)` mengembalikan **semua**
  entitas berdasarkan ID — termasuk yang berverdict "aman"/"hati_hati"
  dan yang baru dibuat otomatis dari laporan yang belum dimoderasi.
- **Dampak:** inkonsistensi kontrol akses (CWE-639). Penyerang dapat
  mengenumerasi ID untuk membaca entitas yang seharusnya belum layak
  tampil di feed publik.
- **Rekomendasi:** terapkan filter yang sama di `show()` — kembalikan
  404 bila verdict bukan "bahaya". ✅ *Diperbaiki di commit P0/P1.*

### WAS-SEC-002 — [P1] Rute /admin tanpa autentikasi

- **Bukti:** `routes/web.php:21` — `Route::get('/admin', ...)` tanpa
  middleware; baris 22 sendiri mengakui
  `TODO: lindungi dengan middleware auth + admin`. View-nya bahkan
  mengklaim "akses dibatasi" (`resources/views/pages/admin.blade.php`)
  padahal tidak.
- **Deskripsi:** halaman moderasi dapat dibuka siapa saja. Saat ini
  masih placeholder (data kosong), tetapi begitu dihubungkan ke
  `Report::pending()`, ia akan mengekspos seluruh laporan masuk
  termasuk **nama dan kontak pelapor (PII)**.
- **Dampak:** broken access control (CWE-862) — hari ini cacat desain,
  besok kebocoran data.
- **Rekomendasi:** nonaktifkan rute (404) sampai middleware auth+admin
  diimplementasikan; aksi setuju/tolak wajib POST + CSRF.
  ✅ *Diperbaiki di commit P0/P1 (rute dinonaktifkan).*

### WAS-SEC-003 — [P1] Laporan anonim dapat memanipulasi verdict menjadi BAHAYA

- **Bukti:** `app/Http/Controllers/Api/ReportController.php:33-88`
  (`store()`); `app/Services/ScamScorer.php:22` (ambang 70),
  baris 70-78 (komposisi skor).
- **Deskripsi:** `POST /api/reports` tidak butuh login (throttle
  10/mnt/IP). Setiap laporan menaikkan `report_count` (+8 poin,
  maks 40), entitas baru otomatis +15 (umur < 7 hari), dan kata kunci
  berbahaya di deskripsi +4 per kata (maks 20). Hitungan serangan:
  5 laporan (40) + entitas baru (15) + 4 kata kunci, mis. "transfer
  otp hadiah gratis" (16) = **71 ≥ 70 → verdict "bahaya"**.
  Lima request dalam satu menit dari satu IP sudah cukup.
- **Dampak:** penyerang dapat mencap nomor/rekening **milik orang yang
  sah** sebagai penipuan — pencemaran nama dengan dampak nyata
  (CWE-807: keputusan keamanan dari input tak terpercaya).
- **Rekomendasi:** verdict "bahaya" tidak boleh lahir murni dari
  laporan yang belum terverifikasi; batasi verdict otomatis maksimal
  "hati_hati" sampai ada ≥1 laporan terverifikasi moderator.
  ✅ *Diperbaiki di commit P0/P1 (clamp + tautan report→entity).*

### WAS-SEC-004 — [P2] Mass assignment laten: `status` ada di `$fillable`

- **Bukti:** `app/Models/Report.php:36-45` — `$fillable` memuat
  `status` dan `scam_entity_id`.
- **Deskripsi:** `store()` saat ini aman karena meng-hardcode
  `'status' => 'pending'` (baris 48) dan memakai array eksplisit,
  bukan `$request->all()`. Namun `status` yang mass-assignable adalah
  bom waktu: satu baris `Report::create($request->validated())` di
  masa depan — atau FormRequest yang memvalidasi `status` — langsung
  memungkinkan penyerang melewati moderasi (`pending` → `verified`).
- **Dampak:** laten (CWE-915). Belum tereksploitasi di kode saat ini.
- **Rekomendasi:** keluarkan `status` (dan `scam_entity_id`) dari
  `$fillable`; isi keduanya via assignment eksplisit. ⏳ *Belum
  diperbaiki — butuh penyesuaian factory/seed bila diubah.*

### WAS-SEC-005 — [P3] `GET /api/reports` tanpa throttle

- **Bukti:** `routes/api.php:25` — tidak ada `->middleware('throttle')`,
  padahal semua route API lain memilikinya (`throttle:30,60`,
  `throttle:10,60`, `throttle:120,60`).
- **Deskripsi:** endpoint daftar laporan terverifikasi dapat di-scrape /
  dienumerasi tanpa batas kecepatan.
- **Dampak:** rendah (CWE-770) — data yang diekspos memang publik.
- **Rekomendasi:** samakan dengan pola yang ada:
  `->middleware('throttle:120,60')`. ⏳ *Belum diperbaiki.*

### WAS-SEC-006 — [P1] `type=message` lolos validasi, ditolak database

- **Bukti:** `app/Http/Requests/CheckRequest.php:26` dan
  `app/Http/Requests/ReportRequest.php:26` menerima
  `in:phone,bank_account,ewallet,url,email,message,other`;
  UI `/cek` menawarkan `<option value="message">`
  (`resources/views/pages/cek.blade.php:19`); tetapi
  `database/migrations/2026_10_09_000001_create_scam_entities_table.php:16`
  mendefinisikan enum **tanpa** `message`.
- **Deskripsi:** laporan bertipe `message` lolos validasi, `Report::create`
  berhasil (kolom `reports.type` bertipe string), lalu
  `ScamEntity::firstOrCreate` melempar exception di MySQL produksi →
  **HTTP 500** pada alur yang ditawarkan UI. (Ditemukan atas masukan
  verifikasi divisi qa-unit.)
- **Dampak:** inkonsistensi antar-lapisan (CWE-20); pemicu tanpa
  autentikasi pada fitur utama.
- **Rekomendasi:** `message` adalah fitur yang dimaksudkan (UI +
  validasi + keyword scorer untuk teks pesan), jadi yang diperbaiki
  adalah sisi database: tambah `message` ke enum via migrasi baru.
  ✅ *Diperbaiki di commit P0/P1.*

## Pola aman (true negatives — diverifikasi)

- **TN-01** — throttle konsisten di endpoint tulis dan feed:
  `POST /check` (30,60), `POST /reports` (10,60), threat-feed
  (120,60) — `routes/api.php:18-31`.
- **TN-02** — validasi FormRequest memadai: `evidence_url` memakai
  rule `url`, `description` dibatasi `min:10|max:5000`, controller
  memakai `$request->validated()` + array eksplisit (tidak ada
  `$request->all()` mass assignment) — `ReportRequest.php`,
  `ReportController.php:40-49`.
- **TN-03** — output Blade di-escape: seluruh view memakai `{{ }}`;
  satu-satunya `{!! $ikon !!}` (`components/alert.blade.php:34`)
  me-render dari **map ikon hardcoded internal** berdasarkan prop
  `type`, bukan dari input pengguna — aman.

## Lampiran

- `ground-truth-appendix.csv` — seluruh temuan di atas dalam skema
  12 kolom untuk riset TA (pasangan vuln→fixed, true negatives).
