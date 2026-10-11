# Waspada — Ekstensi Browser

Ekstensi browser (Manifest V3) untuk **Waspada**, platform intelijen penipuan
Indonesia. Mengecek keamanan URL halaman yang sedang kamu buka ke server API
Waspada dan menampilkan verdict: **AMAN** 🟢, **HATI-HATI** 🟡, atau **BAHAYA** 🔴.

## Cara install (mode developer)

1. Buka `chrome://extensions` (atau `edge://extensions`).
2. Aktifkan **Developer mode** (saklar di kanan atas).
3. Klik **Load unpacked** lalu pilih folder `extension/` di repo ini.
4. Ikon perisai hijau Waspada akan muncul di toolbar.

> Catatan: Chrome mungkin menampilkan peringatan "host_permissions `<all_urls>`"
> saat install. Izin ini hanya dipakai agar ekstensi bisa menghubungi server
> API Waspada di alamat yang kamu atur sendiri (mis. server lokal atau VPS).

## Cara pakai

1. Buka halaman **Opsi** ekstensi (klik kanan ikon → *Options*, atau dari link
   "⚙️ Opsi" di popup) dan isi **API base URL** server Waspada-mu, misalnya:
   - Lokal: `http://localhost:8000`
   - Produksi: `https://waspada.example.com`
2. Buka halaman web apa pun, klik ikon Waspada di toolbar.
3. Popup menampilkan URL halaman tersebut — klik **Cek Keamanan**.
4. Hasilnya muncul sebagai badge verdict + daftar alasan dalam Bahasa Indonesia.

Cara cepat lain: klik kanan sebuah link / halaman → **Cek dengan Waspada**.

## Cara kerja

- Popup mengambil URL tab aktif, lalu `POST {apiBase}/api/check` dengan body
  `{ "type": "url", "value": "<url>" }` dan timeout 15 detik.
- Server menjawab `{ "verdict": "aman|hati-hati|bahaya", "reasons": [...] }`
  yang langsung ditampilkan.
- Alamat API disimpan di `chrome.storage.sync` (ikut akun Chrome-mu).

## Catatan

- **Manifest V3**, tanpa build step — file HTML/CSS/JS polos.
- **Tidak mengumpulkan data.** Satu-satunya request jaringan adalah ke API base
  URL yang kamu atur sendiri, berisi URL yang sedang dicek. Tidak ada analytics
  atau pihak ketiga.
- Halaman internal browser (`chrome://`, `edge://`, `about:`) tidak bisa dicek
  — keterbatasan izin browser, bukan bug.
- Jika muncul "Tidak dapat menghubungi server Waspada", pastikan server API
  berjalan dan API base URL di Opsi sudah benar.
