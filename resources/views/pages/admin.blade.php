@extends('layouts.app')

@section('title', 'Moderasi Admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">Moderasi Laporan</h1>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
        Tinjau laporan masuk sebelum ditampilkan ke publik.
    </p>

    {{--
        CATATAN KEAMANAN (untuk divisi security-audit):
        - Halaman ini WAJIB di belakang autentikasi + otorisasi admin.
        - Aksi setuju/tolak harus berupa POST dengan CSRF token, bukan GET.
        - Jangan tampilkan data pelapor secara penuh ke publik.
    --}}

    <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-slate-800 text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    <th class="px-4 py-3">Nilai</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Pelapor</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr class="border-b border-slate-100 dark:border-slate-800 last:border-0">
                        <td class="px-4 py-3 font-mono text-xs break-all">{{ \Illuminate\Support\Str::limit($report->value, 32) }}</td>
                        <td class="px-4 py-3"><span class="badge-kategori">{{ str_replace('_', ' ', $report->category) }}</span></td>
                        <td class="px-4 py-3 text-slate-500">{{ $report->reporter_name ?: 'Anonim' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium">{{ ucfirst($report->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            {{-- TODO (security-audit): ganti dengan form POST + @csrf ke route moderasi admin --}}
                            <button type="button" disabled title="Butuh auth admin"
                                class="rounded-lg bg-green-100 dark:bg-green-900 px-3 py-1.5 text-xs font-semibold text-green-700 dark:text-green-300 opacity-50 cursor-not-allowed">
                                Setuju
                            </button>
                            <button type="button" disabled title="Butuh auth admin"
                                class="rounded-lg bg-red-100 dark:bg-red-900 px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-300 opacity-50 cursor-not-allowed">
                                Tolak
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">
                            Tidak ada laporan menunggu moderasi. 🎉
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <p class="mt-3 text-xs text-slate-400">Halaman admin — akses dibatasi. Implementasi autentikasi oleh divisi security.</p>
</div>
@endsection
