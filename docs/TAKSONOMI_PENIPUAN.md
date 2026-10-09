# Taksonomi Penipuan Online di Indonesia

Dokumen riset ini memetakan jenis-jenis penipuan (scam) online yang marak beredar di
Indonesia. Tujuannya adalah menjadi acuan bersama untuk mesin penilaian risiko,
kata kunci pendeteksi, dan materi edukasi di platform Waspada.

> Catatan: contoh pesan dan nomor dalam dokumen ini **fiktif** dan dibuat hanya
> untuk keperluan riset. Tidak ada statistik angka spesifik yang diklaim karena
> belum terverifikasi dari sumber resmi.

---

## 1. Phising — Link Palsu (`phising`)

**Modus operandi:**
Pelaku menyamar sebagai bank, e-wallet, kurir, atau instansi resmi lalu mengirim
link yang tampilannya mirip situs asli. Korban yang memasukkan data login, OTP,
atau PIN di halaman palsu tersebut langsung kehilangan akses akun dan saldonya.

**Contoh pesan tipikal:**
- "Yth Nasabah, akun BCA Anda terdeteksi login dari perangkat asing. Segera verifikasi di http://bca-verifikasi-aman.example agar tidak diblokir."
- "Paket JNE Anda tertahan. Lacak & bayar biaya admin Rp12.000 di jne-paket-tertahan.example"

**Kata kunci pendeteksi:**
`verifikasi akun`, `segera`, `diblokir`, `klik link`, `login ulang`, `perangkat asing`,
`biaya admin`, `http://` (bukan https), domain mirip bank/kurir, `otp`, `kode verifikasi`

**Level bahaya umum:** Tinggi

---

## 2. Investasi Bodong (`investasi_bodong`)

**Modus operandi:**
Pelaku menawarkan "investasi" dengan imbal hasil tidak masuk akal (mis. profit
10–50% per minggu) lewat grup WhatsApp/Telegram. Awalnya korban diberi profit kecil
agar percaya, lalu diminta setor dana lebih besar sebelum pelaku menghilang.
Sering berkedok trading forex, kripto, atau "robot trading".

**Contoh pesan tipikal:**
- "Join grup VIP trading kami, modal 500rb profit 2jt per minggu. Dijamin aman, sudah banyak yang WD!"
- "Robot trading AI profit konsisten 5% per hari. Slot terbatas, daftar sekarang!"

**Kata kunci pendeteksi:**
`profit`, `dijamin`, `WD` (withdraw), `robot trading`, `slot terbatas`, `modal kecil`,
`cuan`, `tanpa risiko`, `grup vip`, `passive income`

**Level bahaya umum:** Tinggi

---

## 3. Love Scam / Romance Scam (`love_scam`)

**Modus operandi:**
Pelaku membangun hubungan romantis lewat media sosial atau aplikasi kencan selama
berminggu-minggu, lalu mulai meminta uang dengan alasan darurat (sakit, bisnis
macet, tiket pesawat untuk bertemu). Sering juga dikombinasikan dengan ajakan
"investasi bareng" (pig butchering).

**Contoh pesan tipikal:**
- "Sayang, mama aku masuk RS dan butuh operasi 20jt. Kamu bisa bantu transfer dulu? Nanti aku ganti pas ketemu."
- "Aku nemu platform investasi bagus, kita coba bareng yuk biar bisa nabung buat masa depan kita."

**Kata kunci pendeteksi:**
`sayang`, `transfer dulu`, `darurat`, `mama sakit`, `operasi`, `nanti diganti`,
`jangan bilang siapa-siapa`, `investasi bareng`, `tiket pesawat`

**Level bahaya umum:** Tinggi

---

## 4. Pinjol Ilegal (`pinjol_ilegal`)

**Modus operandi:**
Pelaku menawarkan pinjaman online "tanpa syarat, cair 5 menit" lewat SMS/WhatsApp
atau iklan media sosial. Setelah korban menginstal aplikasi atau mengisi data,
pelaku memeras dengan bunga mencekik, menyebar data pribadi, dan meneror kontak
di ponsel korban.

**Contoh pesan tipikal:**
- "PINJAMAN ONLINE TANPA JAMINAN! Limit s/d 50jt, cukup KTP, cair 5 menit. WA: 0812-xxxx-xxxx"
- "Selamat! Anda lolos pra-persetujuan pinjaman 10jt. Klik link berikut untuk pencairan."

**Kata kunci pendeteksi:**
`tanpa jaminan`, `cair 5 menit`, `cukup KTP`, `limit`, `tanpa BI checking`,
`pra-persetujuan`, `pinjaman online`, `bunga rendah`, `tanpa survei`

**Level bahaya umum:** Tinggi

---

## 5. Undangan APK — File Berbahaya Berkedok Undangan/Paket (`undangan_apk`)

**Modus operandi:**
Pelaku mengirim file `.apk` lewat WhatsApp dengan nama seperti "Undangan
Pernikahan.apk" atau "Paket-JNE.apk". Jika diinstal, aplikasi mencuri SMS (termasuk
OTP/m-banking), kontak, dan mengambil alih akun finansial korban.

**Contoh pesan tipikal:**
- "Assalamualaikum, ini undangan pernikahan saya 🙏 Undangan Pernikahan.apk (tolong dibuka ya)"
- "Paket anda sudah sampai, cek resi di sini: Paket-JNT.apk"

**Kata kunci pendeteksi:**
`.apk`, `undangan`, `pernikahan`, `cek resi`, `paket`, `install`, `unduh file`,
`buka lampiran`, `kurir`, nama file berakhiran `.apk`

**Level bahaya umum:** Tinggi

---

## 6. Hadiah Palsu / Undian Palsu (`hadiah_palsu`)

**Modus operandi:**
Korban menerima kabar "menang undian" (mobil, motor, uang tunai) dari nomor tak
dikenal yang mengatasnamakan brand besar atau e-wallet. Untuk "mencairkan hadiah",
korban diminta membayar "pajak/biaya admin" via transfer atau pulsa — hadiahnya
tidak pernah ada.

**Contoh pesan tipikal:**
- "SELAMAT! Nomor Anda memenangkan Grand Prize 1 unit mobil dari undian DANA. Hubungi 0812-xxxx-xxxx untuk klaim. Biaya admin Rp250rb."
- "Anda terpilih sebagai pemenang Giveaway Shopee Rp5.000.000. Klik link untuk verifikasi pemenang."

**Kata kunci pendeteksi:**
`selamat`, `pemenang`, `grand prize`, `undian`, `giveaway`, `klaim hadiah`,
`biaya admin`, `pajak hadiah`, `mencairkan`, `terpilih`

**Level bahaya umum:** Sedang

---

## 7. Penipuan Jual Beli / Marketplace (`jual_beli`)

**Modus operandi:**
Penjual fiktif memasang iklan barang (gadget, motor, tiket) dengan harga jauh di
bawah pasar di marketplace/media sosial, lalu meminta pembayaran di muka via
transfer bank atau e-wallet di luar platform resmi. Barang tidak pernah dikirim.
Varian lain: pembeli palsu mengirim "bukti transfer" editan.

**Contoh pesan tipikal:**
- "iPhone 15 Pro 128GB cuma 6jt, BNIB garansi resmi. Transfer dulu baru kirim, stok tinggal 1."
- "[KIRIM GAMBAR bukti transfer] Kak transfernya sudah saya kirim ya, tolong kirim barangnya sekarang."

**Kata kunci pendeteksi:**
`transfer dulu`, `harga miring`, `BNIB`, `stok tinggal`, `di luar aplikasi`,
`jangan lewat rekber`, `bukti transfer`, `kirim sekarang`, `DP dulu`

**Level bahaya umum:** Sedang

---

## 8. Lowongan Kerja Palsu (`lowongan_kerja_palsu`)

**Modus operandi:**
Pelaku menyebar lowongan kerja bergaji fantastis untuk pekerjaan mudah (input data,
like & subscribe, admin online) lewat WhatsApp/Telegram. Korban diminta membayar
"biaya pendaftaran/training" atau diminta "top up" untuk membuka tugas berlevel —
uang hilang, pekerjaan tidak ada. Varian lain mencuri data KTP untuk pinjol.

**Contoh pesan tipikal:**
- "Dibutuhkan admin online, gaji 8jt/bulan, kerja dari HP 2 jam sehari. Syarat: bayar biaya training 150rb."
- "Tugas mudah: like video dapat 50rb/tugas. Top up 200rb untuk unlock tugas level VIP dengan komisi lebih besar."

**Kata kunci pendeteksi:**
`gaji`, `/bulan`, `kerja dari HP`, `tanpa pengalaman`, `biaya pendaftaran`,
`biaya training`, `top up`, `unlock tugas`, `komisi`, `like dan subscribe`

**Level bahaya umum:** Sedang

---

## 9. Penipuan Berkedok Petugas / Social Engineering (`petugas_palsu`)

**Modus operandi:**
Pelaku menelepon/menghubungi korban mengaku sebagai polisi, pegawai bank, petugas
pajak, atau kurir, menciptakan kepanikan ("rekening Anda terlibat pencucian uang",
"paket Anda berisi barang ilegal"). Korban diarahkan mentransfer "dana verifikasi"
atau menginstal aplikasi remote (AnyDesk/TeamViewer) untuk dikuras saldonya.

**Contoh pesan tipikal:**
- "Saya dari Polda, rekening Bapak terindikasi tindak pidana. Agar tidak dibekukan, transfer dana verifikasi ke rekening penampung berikut."
- "Saya dari pihak bank, ada transaksi mencurigakan. Tolong install aplikasi ini agar saya bisa bantu amankan akun Bapak."

**Kata kunci pendeteksi:**
`polisi`, `polda`, `pegawai bank`, `terindikasi`, `dibekukan`, `dana verifikasi`,
`rekening penampung`, `install aplikasi`, `remote`, `jangan tutup telepon`

**Level bahaya umum:** Tinggi

---

## 10. Arisan Online / Skema Ponzi (`arisan_online`)

**Modus operandi:**
"Arisan online" dengan imbal hasil jauh di atas wajar dipromosikan di media sosial.
Peserta awal dibayar dari dana peserta baru (skema Ponzi). Begitu perekrutan melambat
atau dana terkumpul besar, bandar kabur. Sering dikemas sebagai "slot arisan",
"dana gaib", atau "sedekah berantai".

**Contoh pesan tipikal:**
- "Arisan online amanah! Slot 100rb dapat 1jt dalam 10 hari. Testimoni banyak, join sekarang!"
- "Program dana hibah, setor 500rb cair 5jt. Kuota terbatas!"

**Kata kunci pendeteksi:**
`arisan online`, `slot`, `amanah`, `testimoni`, `cair`, `dana hibah`, `berantai`,
`kuota terbatas`, `dapat ... dalam ... hari`

**Level bahaya umum:** Tinggi

---

## Sumber Pola Umum

Pola-pola di atas dirangkum dari observasi umum terhadap laporan masyarakat,
pemberitaan media nasional tentang penipuan digital, dan materi edukasi literasi
digital yang beredar publik (mis. imbauan OJK tentang pinjol ilegal dan investasi
bodong, serta peringatan perbankan tentang phising). Dokumen ini tidak mengklaim
angka statistik tertentu; setiap klaim kuantitatif di platform Waspada wajib
merujuk pada sumber resmi yang terverifikasi.
