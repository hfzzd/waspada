@extends('layouts.app')

@section('title', 'Cek Penipuan')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Cek Penipuan</h1>
    <p class="mt-2 text-slate-600 dark:text-slate-400 text-sm sm:text-base">
        Tempel pesan, link, nomor HP, atau nomor rekening yang mencurigakan. Kami beri verdict
        <strong>AMAN</strong>, <strong>HATI-HATI</strong>, atau <strong>BAHAYA</strong>.
    </p>

    {{-- Form cek — logika submit & render hasil dikerjakan frontend-interaction (Alpine.js) --}}
    <form id="form-cek" class="mt-6 space-y-4 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-sm">
        <div>
            <label for="tipe" class="block text-sm font-medium mb-1">Jenis yang diperiksa</label>
            <select id="tipe" name="type" required
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                <option value="message">Pesan / Chat (WhatsApp, SMS)</option>
                <option value="url">URL / Link</option>
                <option value="phone">Nomor HP / Telepon</option>
                <option value="bank_account">Nomor Rekening Bank</option>
                <option value="ewallet">Nomor E-Wallet (DANA, OVO, GoPay)</option>
            </select>
        </div>
        <div>
            <label for="nilai" class="block text-sm font-medium mb-1">Isi yang ingin diperiksa</label>
            <textarea id="nilai" name="value" rows="4" required maxlength="2000"
                placeholder="Contoh: Halo kak, paket Anda tertahan. Klik http://contoh-link… untuk konfirmasi."
                class="w-full rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
            <p class="mt-1 text-xs text-slate-400">Maksimal 2000 karakter. Jangan masukkan data pribadimu sendiri.</p>
        </div>
        <button type="submit"
            class="w-full rounded-xl bg-brand-600 px-6 py-3 font-semibold text-white shadow hover:bg-brand-700 transition disabled:opacity-60">
            Periksa
        </button>
    </form>

    {{-- Area hasil — diisi oleh frontend-interaction via Alpine.js / fetch ke POST /api/check --}}
    <div id="hasil-cek" class="mt-6" aria-live="polite">
        {{-- TODO (frontend-interaction):
             - tangkap submit #form-cek, POST ke /api/check
             - render verdict (AMAN/HATI-HATI/BAHAYA) + risk_score + explanation
             - pakai class .verdict-aman / .verdict-hatihati / .verdict-bahaya dari css/app.css
             - tambah animasi .hasil-masuk saat hasil muncul --}}
    </div>
</div>
@endsection
