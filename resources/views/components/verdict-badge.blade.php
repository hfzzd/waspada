{{-- resources/views/components/verdict-badge.blade.php --}}
{{--
    Badge verdict hasil pengecekan penipuan.

    Penggunaan:
        <x-verdict-badge verdict="bahaya" />
        <x-verdict-badge :verdict="$hasil['verdict']" />

    Prop:
        @prop string $verdict  Nilai: 'aman' | 'hati_hati' | 'bahaya'
--}}
@props(['verdict' => 'hati_hati'])

@php
    $peta = [
        'aman' => [
            'label' => 'AMAN',
            'kelas' => 'bg-green-100 text-green-800 ring-green-600/30 dark:bg-green-900/40 dark:text-green-300 dark:ring-green-400/30',
            'titik' => 'bg-green-500',
        ],
        'hati_hati' => [
            'label' => 'HATI-HATI',
            'kelas' => 'bg-amber-100 text-amber-800 ring-amber-600/30 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-400/30',
            'titik' => 'bg-amber-500',
        ],
        'bahaya' => [
            'label' => 'BAHAYA',
            'kelas' => 'bg-red-100 text-red-800 ring-red-600/30 dark:bg-red-900/40 dark:text-red-300 dark:ring-red-400/30',
            'titik' => 'bg-red-500',
        ],
    ];
    $info = $peta[$verdict] ?? $peta['hati_hati'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 ring-inset ' . $info['kelas']]) }}>
    <span class="h-2 w-2 rounded-full {{ $info['titik'] }}"></span>
    {{ $info['label'] }}
</span>
