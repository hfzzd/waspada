@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
{{-- ===== Hero ===== --}}
<section class="text-center py-10 sm:py-16">
    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">
        Cek dulu, <span class="text-brand-600 dark:text-brand-400">baru percaya.</span>
    </h1>
    <p class="mt-4 max-w-2xl mx-auto text-slate-600 dark:text-slate-400 text-base sm:text-lg">
        Waspada membantu kamu memeriksa pesan, link, nomor HP, dan rekening yang mencurigakan
        sebelum menjadi korban penipuan. Gratis, open-source, dan didukung laporan komunitas.
    </p>
    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ url('/cek') }}"
           class="rounded-xl bg-brand-600 px-6 py-3 font-semibold text-white shadow hover:bg-brand-700 transition">
            Cek Sekarang
        </a>
        <a href="{{ url('/komunitas') }}"
           class="rounded-xl border border-slate-300 dark:border-slate-700 px-6 py-3 font-semibold hover:bg-slate-100 dark:hover:bg-slate-800 transition">
            Lihat Laporan Komunitas
        </a>
    </div>
</section>

{{-- ===== Fitur ===== --}}
<section class="py-8">
    <h2 class="text-xl font-bold mb-4 text-slate-900 dark:text-white">Fitur utama</h2>
    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="text-2xl mb-2">🔍</div>
            <h3 class="font-semibold text-slate-900 dark:text-white">Cek Pesan, URL & Nomor</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Tempel pesan WhatsApp/SMS, link, nomor HP, atau nomor rekening — dapatkan verdict
                <strong>AMAN</strong>, <strong>HATI-HATI</strong>, atau <strong>BAHAYA</strong> beserta penjelasannya.
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="text-2xl mb-2">🤝</div>
            <h3 class="font-semibold text-slate-900 dark:text-white">Laporan Komunitas</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Laporkan modus penipuan yang kamu temui agar orang lain tidak jadi korban berikutnya.
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <div class="text-2xl mb-2">📡</div>
            <h3 class="font-semibold text-slate-900 dark:text-white">Feed Ancaman Publik</h3>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                API dan feed terbuka berisi daftar nomor, rekening, dan situs berbahaya yang terverifikasi komunitas.
            </p>
        </div>
    </div>
</section>

{{-- ===== Statistik (placeholder) ===== --}}
<section class="py-8">
    <div class="grid grid-cols-3 gap-4 text-center">
        <div class="rounded-2xl bg-brand-50 dark:bg-slate-900 p-5">
            <p class="text-2xl sm:text-3xl font-extrabold text-brand-700 dark:text-brand-300">—</p>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Entitas terdata</p>
        </div>
        <div class="rounded-2xl bg-brand-50 dark:bg-slate-900 p-5">
            <p class="text-2xl sm:text-3xl font-extrabold text-brand-700 dark:text-brand-300">—</p>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Laporan komunitas</p>
        </div>
        <div class="rounded-2xl bg-brand-50 dark:bg-slate-900 p-5">
            <p class="text-2xl sm:text-3xl font-extrabold text-brand-700 dark:text-brand-300">—</p>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Pengecekan dilakukan</p>
        </div>
    </div>
    {{-- TODO: isi statistik dari database (controller) --}}
</section>

{{-- ===== Edukasi ===== --}}
<section class="py-8">
    <h2 class="text-xl font-bold mb-4 text-slate-900 dark:text-white">3 tips anti-penipuan</h2>
    <ol class="space-y-3">
        <li class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm flex gap-3">
            <span class="font-extrabold text-brand-600 dark:text-brand-400">1.</span>
            <p class="text-sm text-slate-600 dark:text-slate-400"><strong class="text-slate-800 dark:text-slate-200">Jangan klik link dari nomor tak dikenal.</strong> Bank dan kurir resmi tidak pernah meminta data lewat link acak di WhatsApp/SMS.</p>
        </li>
        <li class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm flex gap-3">
            <span class="font-extrabold text-brand-600 dark:text-brand-400">2.</span>
            <p class="text-sm text-slate-600 dark:text-slate-400"><strong class="text-slate-800 dark:text-slate-200">Waspadai tekanan "segera".</strong> Penipu suka membuat panik ("akun diblokir!", "promo berakhir 5 menit!") agar kamu tidak sempat berpikir.</p>
        </li>
        <li class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-sm flex gap-3">
            <span class="font-extrabold text-brand-600 dark:text-brand-400">3.</span>
            <p class="text-sm text-slate-600 dark:text-slate-400"><strong class="text-slate-800 dark:text-slate-200">Cek di Waspada dulu.</strong> Tempel pesan/nomor/rekening yang mencurigakan ke halaman Cek sebelum transfer atau memberi data pribadi.</p>
        </li>
    </ol>
</section>
@endsection
