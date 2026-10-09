{{-- resources/views/components/laporan-card.blade.php --}}
{{--
    Kartu ringkasan satu laporan komunitas.

    Penggunaan:
        <x-laporan-card :report="$laporan" />

    Prop:
        @prop \App\Models\Report|array $report
--}}
@props(['report'])

@php
    $data = $report instanceof \App\Models\Report ? $report->toArray() : (array) $report;

    $kategori = $data['category'] ?? 'lainnya';
    $labelKategori = [
        'phising' => 'Phising',
        'investasi_bodong' => 'Investasi Bodong',
        'love_scam' => 'Love Scam',
        'pinjol_ilegal' => 'Pinjol Ilegal',
        'undangan_apk' => 'Undangan APK',
        'hadiah_palsu' => 'Hadiah Palsu',
        'jual_beli' => 'Jual Beli',
        'lowongan_kerja_palsu' => 'Lowongan Kerja Palsu',
        'petugas_palsu' => 'Petugas Palsu',
        'arisan_online' => 'Arisan Online',
        'lainnya' => 'Lainnya',
    ][$kategori] ?? ucfirst(str_replace('_', ' ', $kategori));

    // Sensor nilai di tengah demi privasi, mis. 62812****001
    $nilai = (string) ($data['value'] ?? '-');
    $nilaiSensor = strlen($nilai) > 8
        ? substr($nilai, 0, 5) . '****' . substr($nilai, -3)
        : $nilai;

    $deskripsi = (string) ($data['description'] ?? '');
    $deskripsiPendek = mb_strlen($deskripsi) > 150
        ? mb_substr($deskripsi, 0, 150) . '…'
        : $deskripsi;

    $status = $data['status'] ?? 'pending';
    $labelStatus = ['pending' => 'Menunggu Verifikasi', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'][$status] ?? $status;
    $kelasStatus = [
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        'verified' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
        'rejected' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    ][$status] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';

    $tanggal = isset($data['created_at'])
        ? \Carbon\Carbon::parse($data['created_at'])->translatedFormat('d M Y')
        : '-';
@endphp

<article {{ $attributes->merge(['class' => 'rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800']) }}>
    <div class="mb-2 flex flex-wrap items-center gap-2">
        <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
            {{ $labelKategori }}
        </span>
        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $kelasStatus }}">
            {{ $labelStatus }}
        </span>
        <time class="ml-auto text-xs text-gray-500 dark:text-gray-400">{{ $tanggal }}</time>
    </div>

    <p class="font-mono text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $nilaiSensor }}</p>

    <p class="mt-1.5 text-sm leading-relaxed text-gray-600 dark:text-gray-300">{{ $deskripsiPendek }}</p>
</article>
