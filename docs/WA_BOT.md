# Bot WhatsApp Waspada

Bot WhatsApp Waspada memakai **Meta WhatsApp Cloud API** (gratis). Pengguna
cukup mengirim pesan ke nomor bot:

- **Teks biasa** → dicek ke basis data Waspada, dibalas verdict
  🟢 AMAN / 🟡 HATI-HATI / 🔴 BAHAYA + alasan singkat.
- **`/lapor <kategori> | <deskripsi>`** → dibuatkan laporan komunitas
  (status awal `pending`, menunggu moderasi).

Contoh:

```
/lapor phising | Saya diminta transfer oleh nomor tak dikenal yang mengaku kurir
```

## Cara setup (untuk pemilik repo)

1. **Buat aplikasi Meta** di [developers.facebook.com](https://developers.facebook.com)
   → *Create App* → pilih tipe *Other* → *Business*.
2. **Tambahkan produk WhatsApp**: di dashboard aplikasi → *Add Product* →
   *WhatsApp* → *Set up*.
3. **Ambil kredensial** di *WhatsApp → API Setup*:
   - *Phone number ID* → isi `WA_PHONE_NUMBER_ID`
   - *Temporary access token* (atau buat token permanen di pengaturan
     aplikasi) → isi `WA_ACCESS_TOKEN`
   - *App secret* ada di *Settings → Basic* → isi `WA_APP_SECRET`
4. **Buat verify token sendiri** — string acak apa saja, mis. hasil
   `openssl rand -hex 16` → isi `WA_VERIFY_TOKEN` di `.env` **dan**
   ingat nilainya untuk langkah 5.
5. **Konfigurasi webhook**: di *WhatsApp → Configuration* → *Webhook* →
   *Edit*:
   - *Callback URL*: `https://<domain>/api/wa/webhook`
   - *Verify token*: samakan dengan `WA_VERIFY_TOKEN`
   - Klik *Verify and save*.
6. **Subscribe ke field `messages`**: pada bagian *Webhook fields* → klik
   *Subscribe* pada `messages`.

> Catatan: untuk development lokal, expose dulu server Laravel ke internet
> (mis. `php artisan serve` + tunnel seperti ngrok/Cloudflare) karena Meta
> harus bisa mencapai URL webhook via HTTPS publik.

## Variabel environment

| Variabel            | Wajib | Keterangan                                              |
|---------------------|-------|---------------------------------------------------------|
| `WA_VERIFY_TOKEN`   | Ya    | Token verifikasi webhook (dibuat sendiri, string acak). |
| `WA_APP_SECRET`     | Ya    | App Secret dari dashboard Meta.                         |
| `WA_PHONE_NUMBER_ID`| Ya    | Phone Number ID dari produk WhatsApp.                   |
| `WA_ACCESS_TOKEN`   | Ya    | Token akses API (sementara/permanen).                   |

## Perintah yang didukung

| Perintah | Aksi |
|----------|------|
| *(teks apa saja)* | Dicek sebagai pesan (`type=message`) via `ScamCheckService`; balasan ≤ ~300 karakter. |
| `/lapor <kategori> \| <deskripsi>` | Membuat `Report` dengan aturan validasi yang sama dengan `POST /api/reports`. Kategori: `phising`, `investasi_bodong`, `love_scam`, `pinjol_ilegal`, `undangan_apk`, `hadiah_palsu`, `jual_beli`, `lainnya`. Deskripsi minimal 10 karakter. |

## Keamanan

- **Verifikasi signature wajib**: setiap `POST /api/wa/webhook` harus
  membawa header `X-Hub-Signature-256` yang valid (HMAC-SHA256 dari raw
  body dengan `WA_APP_SECRET`, dibandingkan memakai `hash_equals`).
  Request tanpa signature yang cocok ditolak dengan **401**.
- **Verifikasi webhook** (`GET /api/wa/webhook`) membandingkan
  `hub.verify_token` dengan `WA_VERIFY_TOKEN` memakai `hash_equals`;
  gagal → **403**.
- Kegagalan pengiriman balasan ke Graph API **tidak pernah** membuat
  webhook mengembalikan error — hanya dicatat ke log (`Log::warning`),
  supaya Meta tidak me-retry pesan yang sama berulang kali.
- Jangan pernah commit nilai asli keempat variabel di atas ke Git;
  cukup isi di `.env` server (lihat `.env.example`).
