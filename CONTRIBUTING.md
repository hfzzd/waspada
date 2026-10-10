# 🤝 Berkontribusi di Waspada

Terima kasih sudah tertarik membantu Waspada! Proyek ini dibangun oleh banyak tangan (dan banyak agent 😄), jadi aturan main yang jelas bikin kolaborasi tetap rapi dan menyenangkan.

## Cara Memulai

1. **Fork** repo ini, lalu clone fork-mu:
   ```bash
   git clone https://github.com/<username-mu>/waspada.git
   cd waspada
   ```
2. Ikuti **Setup Lokal** di `README.md` sampai aplikasi berjalan di `http://localhost:8000`.
3. Buat branch baru dari `main` dengan nama yang deskriptif:
   ```bash
   git checkout -b feat/nama-fitur        # fitur baru
   git checkout -b fix/nama-perbaikan     # perbaikan bug
   git checkout -b docs/topik-dokumen     # dokumentasi
   ```

## 📦 Aturan Commit: Kecil, Logis, Sering

Ini aturan paling penting di repo ini:

- **Satu commit = satu perubahan logis.** Misalnya: tambah migrasi *saja*, tambah validasi *saja*, tambah komponen Blade *saja*.
- **Jangan** menggabungkan 10 file yang tidak berkaitan dalam satu commit raksasa — riwayat yang kecil-kecil jauh lebih mudah direview dan di-revert kalau ada masalah.
- Usahakan setiap commit membuat codebase **tetap berjalan** (tidak setengah jadi).
- Tidak ada batasan kaku jumlah commit per hari, tapi sebagai panduan: **10–30 commit kecil per hari kerja** itu pola yang sehat dan wajar untuk proyek aktif.

### Format Pesan Commit

Kami memakai gaya [Conventional Commits](https://www.conventionalcommits.org/):

```
<tipe>: <ringkasan singkat dalam Bahasa Indonesia>
```

Tipe yang dipakai:

| Tipe | Untuk |
|------|-------|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `docs` | Dokumentasi saja |
| `style` | Format kode (tanpa mengubah logika) |
| `refactor` | Restrukturisasi tanpa mengubah perilaku |
| `test` | Menambah/memperbaiki test |
| `chore` | Build, dependensi, konfigurasi |

Contoh yang baik:

```
feat: tambah validasi nomor rekening di ReportRequest
fix: perbaiki normalisasi nomor dengan awalan +62
docs: tambah contoh response threat feed di API.md
```

Contoh yang kurang baik:

```
update file
fix bug
WIP
```

### Identitas Commit

- Setiap commit **wajib** memakai nama dan email GitHub-mu sendiri (`git config user.name` / `user.email`).
- **Dilarang** menambahkan `Co-authored-by`, trailer `Generated with`, atau atribusi ke tool/agent apa pun. Riwayat commit adalah portofolio kontributor manusia.

## 🔁 Alur Pull Request

1. Push branch-mu ke fork: `git push origin feat/nama-fitur`.
2. Buka **Pull Request** ke branch `main` repo utama.
3. Isi deskripsi PR dengan jelas:
   - **Apa** yang diubah dan **kenapa**.
   - Cara mengetes perubahan (langkah manual / perintah test).
   - Tangkapan layar jika ada perubahan tampilan.
4. Tandai reviewer yang relevan (atau biarkan maintainer menugaskan).
5. PR di-merge setelah **minimal 1 approval** dan **CI hijau**.

### Checklist sebelum mengajukan PR

- [ ] `php artisan test` lolos (atau jelaskan kenapa tidak bisa dijalankan).
- [ ] Kode mengikuti standar di bawah.
- [ ] Tidak ada `dd()`, `dump()`, `console.log` debugging yang tertinggal.
- [ ] Tidak ada kredensial/secret yang ter-commit (cek `.env` tidak ikut!).
- [ ] Dokumentasi diperbarui bila perilaku berubah (`docs/API.md`, `README.md`, dsb.).

## 👀 Code Review

Review adalah ruang belajar, bukan ruang sidang. Beberapa prinsipnya:

- **Bersikap baik dan spesifik.** "Fungsi ini bisa disederhanakan dengan…" lebih membantu daripada "kode jelek".
- **Reviewer** fokus pada: kebenaran logika, keamanan (validasi input, mass assignment, otorisasi), keterbacaan, dan test.
- **Penulis PR** wajib merespons setiap komentar — setuju lalu perbaiki, atau diskusikan dengan argumen yang jelas.
- Komentar kecil (typo, penamaan) boleh ditandai sebagai *nitpick* agar tidak menghambat merge.

## 🧑‍💻 Standar Kode

- **PHP:** ikuti gaya Laravel (PSR-12). Jalankan formatter sebelum commit bila tersedia.
- **Validasi input** selalu lewat Form Request (`app/Http/Requests/`), jangan validasi inline di controller.
- **Logika bisnis** tinggal di Service (`app/Services/`), bukan di controller — controller hanya validasi + response.
- **Mass assignment:** pastikan `$fillable` model hanya berisi kolom yang memang boleh diisi user.
- **Blade:** gunakan komponen yang sudah ada (`x-verdict-badge`, `x-report-card`, `x-alert`) sebelum membuat markup baru.
- **JavaScript:** `public/js/waspada.js` adalah skrip standalone tanpa build step — jaga agar tetap framework-agnostic dan ringan.

## 🐞 Melaporkan Bug / Mengusulkan Fitur

- **Bug:** buka Issue dengan judul jelas, sertakan langkah reproduksi, perilaku yang diharapkan vs aktual, dan environment (PHP/DB/browser).
- **Fitur:** buka Issue/Discussion dulu untuk didiskusikan sebelum ngoding — biar usahamu tidak sia-sia kalau arahnya belum disepakati.

---

Sekali lagi, makasih banyak! Setiap kontribusi — sekecil apa pun — bikin Waspada makin berguna buat banyak orang. 💚
