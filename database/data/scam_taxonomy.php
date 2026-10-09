<?php

/**
 * Taksonomi penipuan online di Indonesia.
 *
 * Sumber riset: docs/TAKSONOMI_PENIPUAN.md
 * Semua contoh bersifat fiktif dan hanya untuk keperluan riset.
 *
 * Struktur tiap item:
 * - category     : slug kategori (unik)
 * - name         : nama kategori (Bahasa Indonesia)
 * - description  : ringkasan modus operandi
 * - keywords     : kata/frasa kunci pendeteksi
 * - danger_level : rendah | sedang | tinggi
 */
return [
    [
        'category' => 'phising',
        'name' => 'Phising — Link Palsu',
        'description' => 'Pelaku menyamar sebagai bank, e-wallet, kurir, atau instansi resmi lalu mengirim link mirip situs asli untuk mencuri data login, OTP, atau PIN korban.',
        'keywords' => ['verifikasi akun', 'segera', 'diblokir', 'klik link', 'login ulang', 'perangkat asing', 'biaya admin', 'otp', 'kode verifikasi', 'http://'],
        'danger_level' => 'tinggi',
    ],
    [
        'category' => 'investasi_bodong',
        'name' => 'Investasi Bodong',
        'description' => 'Tawaran investasi dengan imbal hasil tidak masuk akal lewat grup WhatsApp/Telegram; profit awal diberikan agar korban percaya lalu diminta setor dana besar sebelum pelaku menghilang.',
        'keywords' => ['profit', 'dijamin', 'robot trading', 'slot terbatas', 'modal kecil', 'tanpa risiko', 'grup vip', 'passive income', 'cuan', 'WD'],
        'danger_level' => 'tinggi',
    ],
    [
        'category' => 'love_scam',
        'name' => 'Love Scam / Romance Scam',
        'description' => 'Pelaku membangun hubungan romantis lewat media sosial/aplikasi kencan lalu meminta uang dengan alasan darurat, atau mengajak investasi bareng (pig butchering).',
        'keywords' => ['sayang', 'transfer dulu', 'darurat', 'mama sakit', 'operasi', 'nanti diganti', 'jangan bilang siapa-siapa', 'investasi bareng', 'tiket pesawat'],
        'danger_level' => 'tinggi',
    ],
    [
        'category' => 'pinjol_ilegal',
        'name' => 'Pinjol Ilegal',
        'description' => 'Tawaran pinjaman online tanpa syarat dan cair cepat; setelah korban menginstal aplikasi atau menyerahkan data, pelaku memeras dengan bunga mencekik dan meneror kontak korban.',
        'keywords' => ['tanpa jaminan', 'cair 5 menit', 'cukup KTP', 'limit', 'tanpa BI checking', 'pra-persetujuan', 'pinjaman online', 'bunga rendah', 'tanpa survei'],
        'danger_level' => 'tinggi',
    ],
    [
        'category' => 'undangan_apk',
        'name' => 'Undangan APK — File Berbahaya',
        'description' => 'File .apk dikirim via WhatsApp berkedok undangan pernikahan atau resi paket; jika diinstal, aplikasi mencuri SMS berisi OTP/m-banking dan mengambil alih akun finansial.',
        'keywords' => ['.apk', 'undangan', 'pernikahan', 'cek resi', 'paket', 'install', 'unduh file', 'buka lampiran', 'kurir'],
        'danger_level' => 'tinggi',
    ],
    [
        'category' => 'hadiah_palsu',
        'name' => 'Hadiah Palsu / Undian Palsu',
        'description' => 'Korban dikabari menang undian dari nomor tak dikenal mengatasnamakan brand besar; untuk mencairkan hadiah korban diminta membayar pajak/biaya admin — hadiahnya tidak pernah ada.',
        'keywords' => ['selamat', 'pemenang', 'grand prize', 'undian', 'giveaway', 'klaim hadiah', 'biaya admin', 'pajak hadiah', 'mencairkan', 'terpilih'],
        'danger_level' => 'sedang',
    ],
    [
        'category' => 'jual_beli',
        'name' => 'Penipuan Jual Beli / Marketplace',
        'description' => 'Penjual fiktif memasang iklan barang dengan harga jauh di bawah pasar lalu meminta pembayaran di muka via transfer di luar platform resmi; barang tidak pernah dikirim.',
        'keywords' => ['transfer dulu', 'harga miring', 'BNIB', 'stok tinggal', 'di luar aplikasi', 'jangan lewat rekber', 'bukti transfer', 'kirim sekarang', 'DP dulu'],
        'danger_level' => 'sedang',
    ],
    [
        'category' => 'lowongan_kerja_palsu',
        'name' => 'Lowongan Kerja Palsu',
        'description' => 'Lowongan bergaji fantastis untuk pekerjaan mudah disebar via WhatsApp/Telegram; korban diminta membayar biaya pendaftaran/training atau top up untuk membuka tugas — uang hilang, pekerjaan tidak ada.',
        'keywords' => ['gaji', 'kerja dari HP', 'tanpa pengalaman', 'biaya pendaftaran', 'biaya training', 'top up', 'unlock tugas', 'komisi', 'like dan subscribe'],
        'danger_level' => 'sedang',
    ],
    [
        'category' => 'petugas_palsu',
        'name' => 'Penipuan Berkedok Petugas',
        'description' => 'Pelaku mengaku sebagai polisi, pegawai bank, atau petugas pajak dan menciptakan kepanikan agar korban mentransfer dana verifikasi atau menginstal aplikasi remote untuk dikuras saldonya.',
        'keywords' => ['polisi', 'polda', 'pegawai bank', 'terindikasi', 'dibekukan', 'dana verifikasi', 'rekening penampung', 'install aplikasi', 'remote', 'jangan tutup telepon'],
        'danger_level' => 'tinggi',
    ],
    [
        'category' => 'arisan_online',
        'name' => 'Arisan Online / Skema Ponzi',
        'description' => 'Arisan online dengan imbal hasil jauh di atas wajar; peserta awal dibayar dari dana peserta baru (skema Ponzi) hingga bandar kabur membawa dana terkumpul.',
        'keywords' => ['arisan online', 'slot', 'amanah', 'testimoni', 'cair', 'dana hibah', 'berantai', 'kuota terbatas', 'dalam 10 hari'],
        'danger_level' => 'tinggi',
    ],
];
