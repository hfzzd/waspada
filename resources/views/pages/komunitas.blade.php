@extends('layouts.app')

@section('title', 'Komunitas')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Laporan Komunitas</h1>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Laporan terbaru dari sesama pengguna. Bantu sesama dengan melaporkan penipuan yang kamu temui.
            </p>
        </div>
        {{-- TODO (frontend-interaction): toggle tampil/sembunyi form lapor via Alpine.js --}}
        <button type="button" id="btn-buka-lapor"
            class="shrink-0 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700 transition">
            Lapor Penipuan
        </button>
    </div>

    {{-- ===== Form lapor (disembunyikan dulu, di-toggle oleh interaction) ===== --}}
    <div id="form-lapor-wrapper" class="mt-6 hidden">
        <form id="form-lapor" class="space-y-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
            <h2 class="font-bold text-slate-900 dark:text-white">Formulir Laporan</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="lapor-tipe" class="block text-sm font-medium mb-1">Jenis</label>
                    <select id="lapor-tipe" name="type" required
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="phone">Nomor HP / Telepon</option>
                        <option value="bank_account">Rekening Bank</option>
                        <option value="ewallet">E-Wallet</option>
                        <option value="url">URL / Link</option>
                        <option value="email">Email</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label for="lapor-kategori" class="block text-sm font-medium mb-1">Kategori penipuan</label>
                    <select id="lapor-kategori" name="category" required
                        class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="phising">Phising (link palsu)</option>
                        <option value="investasi_bodong">Investasi bodong</option>
                        <option value="love_scam">Love scam</option>
                        <option value="pinjol_ilegal">Pinjol ilegal</option>
                        <option value="undangan_apk">Undangan / file APK</option>
                        <option value="hadiah_palsu">Hadiah / undian palsu</option>
                        <option value="jual_beli">Jual beli (marketplace)</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="lapor-nilai" class="block text-sm font-medium mb-1">Nomor / link / rekening pelaku</label>
                <input id="lapor-nilai" name="value" type="text" required maxlength="500"
                    placeholder="cth: 62812xxxxxxx"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <div>
                <label for="lapor-deskripsi" class="block text-sm font-medium mb-1">Ceritakan modusnya</label>
                <textarea id="lapor-deskripsi" name="description" rows="4" required minlength="10" maxlength="5000"
                    placeholder="Contoh: Saya dihubungi via WhatsApp mengaku kurir, disuruh install file APK undangan…"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            </div>
            <div>
                <label for="lapor-nama" class="block text-sm font-medium mb-1">Nama kamu <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input id="lapor-nama" name="reporter_name" type="text" maxlength="100"
                    placeholder="Boleh dikosongkan untuk anonim"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
            </div>
            <button type="submit"
                class="w-full rounded-xl bg-brand-600 px-6 py-3 font-semibold text-white shadow hover:bg-brand-700 transition disabled:opacity-60">
                Kirim Laporan
            </button>
            <p class="text-xs text-slate-400 text-center">Laporan akan dimoderasi sebelum tampil publik.</p>
        </form>
        {{-- TODO (frontend-interaction): submit ke POST /api/reports, tampilkan pesan sukses/gagal --}}
    </div>

    {{-- ===== Daftar laporan ===== --}}
    <div class="mt-8 space-y-4">
        @forelse ($reports as $report)
            <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
                <div class="flex items-center justify-between gap-2 flex-wrap">
                    <span class="badge-kategori">{{ str_replace('_', ' ', $report->category) }}</span>
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $report->status === 'verified' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' }}">
                        {{ $report->status === 'verified' ? 'Terverifikasi' : 'Menunggu moderasi' }}
                    </span>
                </div>
                <p class="mt-3 font-mono text-sm font-semibold text-slate-800 dark:text-slate-200 break-all">
                    {{-- nilai disensor sebagian demi privasi --}}
                    @php
                        $v = (string) $report->value;
                        $tampil = strlen($v) > 6 ? substr($v, 0, 4) . str_repeat('•', max(0, strlen($v) - 6)) . substr($v, -2) : str_repeat('•', strlen($v));
                    @endphp
                    {{ $tampil }}
                </p>
                <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $report->description }}</p>
                <p class="mt-3 text-xs text-slate-400">
                    Dilaporkan {{ $report->created_at?->diffForHumans() ?? '-' }}
                    @if(!empty($report->reporter_name)) oleh {{ e($report->reporter_name) }} @else secara anonim @endif
                </p>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-700 p-10 text-center">
                <p class="text-slate-500 dark:text-slate-400">Belum ada laporan. Jadilah yang pertama melaporkan! 🛡️</p>
            </div>
        @endforelse
    </div>

    {{-- TODO: paginasi dari controller ({{ $reports->links() }}) --}}
</div>
@endsection
