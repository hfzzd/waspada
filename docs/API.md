# 📡 Dokumentasi API Waspada

Semua endpoint publik Waspada terdokumentasi di sini. Base URL untuk development lokal:

```
http://localhost:8000/api
```

## Konvensi Umum

- **Format:** request dan response memakai JSON (`Content-Type: application/json`).
- **Autentikasi:** endpoint publik **tidak** memerlukan token. (Endpoint admin/moderasi di masa depan akan memakai Laravel Sanctum.)
- **Pembungkus response:** response sukses dibungkus dalam objek `data`:
  ```json
  { "data": { ... } }
  ```
- **Error validasi:** Laravel mengembalikan status `422` dengan detail field:
  ```json
  {
    "message": "The value field is required.",
    "errors": { "value": ["The value field is required."] }
  }
  ```
- **Rate limit:** setiap endpoint publik dibatasi via middleware `throttle`. Jika limit terlampaui, server mengembalikan `429 Too Many Requests` dengan header `Retry-After`.

| Endpoint | Rate limit |
|----------|-----------|
| `POST /api/check` | 30 request / menit |
| `POST /api/reports` | 10 request / menit |
| `GET /api/reports` | tanpa batas khusus |
| `GET /api/threat-feed` | 120 request / menit |
| `GET /api/threat-feed/{entity}` | 120 request / menit |

---

## POST /api/check — Cek Penipuan

Memeriksa sebuah nilai (nomor telepon, rekening, URL, email, pesan, dsb.) terhadap basis data Waspada dan mengembalikan verdict beserta penjelasan.

**Rate limit:** 30/menit

### Parameter

| Nama  | Tipe   | Wajib | Keterangan |
|-------|--------|-------|------------|
| `type`  | string | Ya | Salah satu: `phone`, `bank_account`, `ewallet`, `url`, `email`, `message`, `other` |
| `value` | string | Ya | Nilai yang dicek (maks 2000 karakter) |

### Contoh request

```bash
curl -X POST http://localhost:8000/api/check \
  -H "Content-Type: application/json" \
  -d '{"type": "phone", "value": "0812-3456-7890"}'
```

### Contoh response (200)

```json
{
  "data": {
    "verdict": "bahaya",
    "verdict_label": "BAHAYA",
    "risk_score": 85,
    "report_count": 27,
    "explanation": "Nomor ini telah dilaporkan 27 kali oleh komunitas sebagai modus undangan APK palsu. Jangan transfer uang atau mengklik tautan dari nomor ini.",
    "checked_at": "2026-10-10T00:00:00+07:00"
  }
}
```

### Nilai `verdict`

| Nilai       | Label      | Arti |
|-------------|------------|------|
| `aman`      | AMAN       | Tidak ditemukan di basis data / skor risiko rendah |
| `hati_hati` | HATI-HATI  | Ada sinyal mencurigakan, tetap waspada |
| `bahaya`    | BAHAYA     | Terindikasi kuat sebagai penipuan |

> Nomor telepon dinormalisasi otomatis ke format kanonik (`0812-…` → `62812…`) sebelum dicocokkan, jadi format penulisan tidak memengaruhi hasil.

### Kode error

| Kode | Arti |
|------|------|
| `422` | Validasi gagal (`type` tidak dikenal / `value` kosong / melebihi 2000 karakter) |
| `429` | Rate limit terlampaui |

---

## POST /api/reports — Kirim Laporan

Mengirim laporan penipuan baru dari komunitas. Laporan masuk dengan status `pending` untuk dimoderasi, lalu ikut memperbarui skor entitas terkait.

**Rate limit:** 10/menit

### Parameter

| Nama | Tipe | Wajib | Keterangan |
|------|------|-------|------------|
| `type` | string | Ya | `phone`, `bank_account`, `ewallet`, `url`, `email`, `message`, `other` |
| `value` | string | Ya | Nilai yang dilaporkan (maks 2000 karakter) |
| `category` | string | Ya | Salah satu: `phising`, `investasi_bodong`, `love_scam`, `pinjol_ilegal`, `undangan_apk`, `hadiah_palsu`, `jual_beli`, `lainnya` |
| `description` | string | Ya | Kronologi modus (10–5000 karakter) |
| `reporter_name` | string | Tidak | Nama pelapor (maks 255 karakter) |
| `reporter_contact` | string | Tidak | Kontak pelapor (maks 255 karakter) |
| `evidence_url` | string (URL) | Tidak | Tautan bukti, mis. screenshot (maks 2000 karakter) |

### Contoh request

```bash
curl -X POST http://localhost:8000/api/reports \
  -H "Content-Type: application/json" \
  -d '{
    "type": "phone",
    "value": "0812-3456-7890",
    "category": "undangan_apk",
    "description": "Menerima pesan WhatsApp berisi undangan pernikahan dengan lampiran APK. Setelah dipasang, saldo m-banking berkurang.",
    "reporter_name": "Budi",
    "evidence_url": "https://contoh.id/bukti.png"
  }'
```

### Contoh response (201)

```json
{
  "data": {
    "id": 123,
    "type": "phone",
    "value": "0812-3456-7890",
    "category": "undangan_apk",
    "status": "pending",
    "created_at": "2026-10-10T00:00:00+07:00"
  }
}
```

> 💡 Data pelapor bersifat sukarela. Jangan cantumkan data pribadi sensitif (NIK, PIN, kata sandi) di kolom mana pun.

### Kode error

| Kode | Arti |
|------|------|
| `422` | Validasi gagal (kategori tidak dikenal, deskripsi < 10 karakter, `evidence_url` bukan URL valid, dsb.) |
| `429` | Rate limit terlampaui |

---

## GET /api/reports — Daftar Laporan Terverifikasi

Mengembalikan daftar laporan komunitas yang sudah lolos moderasi (status `verified`), terurut dari yang terbaru. Mendukung paginasi Laravel standar.

### Parameter query

| Nama | Tipe | Keterangan |
|------|------|------------|
| `page` | integer | Halaman (default: 1) |
| `per_page` | integer | Jumlah per halaman |

### Contoh request

```bash
curl "http://localhost:8000/api/reports?per_page=5"
```

### Contoh response (200)

```json
{
  "data": [
    {
      "id": 120,
      "type": "phone",
      "value": "0812-3456-7890",
      "category": "undangan_apk",
      "description": "Modus undangan pernikahan berisi APK berbahaya…",
      "status": "verified",
      "created_at": "2026-10-09T18:30:00+07:00"
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "per_page": 5, "total": 42 }
}
```

---

## GET /api/threat-feed — Feed Intelijen Ancaman

Feed publik berisi entitas dengan verdict `bahaya`, diurutkan dari jumlah laporan terbanyak. Dirancang untuk dikonsumsi ekstensi browser, aplikasi pihak ketiga, atau sistem lain yang ingin memakai data Waspada.

**Rate limit:** 120/menit

### Contoh request

```bash
curl http://localhost:8000/api/threat-feed
```

### Contoh response (200)

```json
{
  "data": [
    {
      "id": 7,
      "type": "phone",
      "value": "0812-3456-7890",
      "normalized_value": "6281234567890",
      "verdict": "bahaya",
      "risk_score": 85,
      "report_count": 27,
      "metadata": { "categories": ["undangan_apk"] },
      "updated_at": "2026-10-09T20:00:00+07:00"
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "per_page": 20, "total": 15 }
}
```

> Feed ini hanya memuat entitas terverifikasi berbahaya — data pribadi pelapor **tidak** pernah ikut dipublikasikan.

---

## GET /api/threat-feed/{entity} — Detail Entitas Ancaman

Mengembalikan detail satu entitas ancaman berdasarkan ID.

**Rate limit:** 120/menit

### Contoh request

```bash
curl http://localhost:8000/api/threat-feed/7
```

### Contoh response (200)

```json
{
  "data": {
    "id": 7,
    "type": "phone",
    "value": "0812-3456-7890",
    "normalized_value": "6281234567890",
    "verdict": "bahaya",
    "risk_score": 85,
    "report_count": 27,
    "metadata": { "categories": ["undangan_apk"] },
    "created_at": "2026-10-01T10:00:00+07:00",
    "updated_at": "2026-10-09T20:00:00+07:00"
  }
}
```

### Kode error

| Kode | Arti |
|------|------|
| `404` | Entitas dengan ID tersebut tidak ditemukan |
| `429` | Rate limit terlampaui |

---

## 🧪 Mencoba API

Koleksi contoh request di atas bisa dijalankan langsung dengan `curl`. Untuk eksplorasi interaktif, buka halaman **Checker** di aplikasi web (`/checker`) — halaman tersebut memanggil `POST /api/check` yang sama persis dengan yang didokumentasikan di sini.
