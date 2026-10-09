{{-- resources/views/components/alert.blade.php --}}
{{--
    Kotak pesan alert.

    Penggunaan:
        <x-alert type="success">Laporan berhasil dikirim!</x-alert>
        <x-alert type="error">Terjadi kesalahan.</x-alert>
        <x-alert type="info">Info untuk pengguna.</x-alert>

    Prop:
        @prop string $type  Nilai: 'success' | 'error' | 'info'
    Slot:
        Isi pesan alert.
--}}
@props(['type' => 'info'])

@php
    $peta = [
        'success' => 'border-green-300 bg-green-50 text-green-800 dark:border-green-700 dark:bg-green-900/30 dark:text-green-200',
        'error' => 'border-red-300 bg-red-50 text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200',
        'info' => 'border-sky-300 bg-sky-50 text-sky-800 dark:border-sky-700 dark:bg-sky-900/30 dark:text-sky-200',
    ];
    $kelas = $peta[$type] ?? $peta['info'];

    $ikon = [
        'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'error' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/>',
        'info' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
    ][$type] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm ' . $kelas, 'role' => 'alert']) }}>
    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        {!! $ikon !!}
    </svg>
    <div class="flex-1">{{ $slot }}</div>
</div>
