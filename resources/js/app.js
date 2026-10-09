/**
 * Waspada — skrip interaksi utama.
 *
 * Berisi:
 *  1. Dark/light mode (localStorage + prefers-color-scheme)
 *  2. Toggle menu mobile
 *  3. Smooth scroll untuk tautan jangkar
 *  4. kirimCek() — kirim form pengecekan ke /api/check via fetch
 *
 * Halaman yang memakai form cek wajib menyediakan:
 *   - <meta name="csrf-token" content="...">
 *   - <form id="form-cek"> dengan <select id="cek-tipe"> dan <input|textarea id="cek-nilai">
 *   - <div id="hasil-cek"> sebagai wadah hasil
 */

(function () {
    'use strict';

    /* ------------------------------------------------------------------
     * 1. Dark / light mode
     * ------------------------------------------------------------------ */
    const KUNCI_TEMA = 'waspada-tema';

    function temaTersimpan() {
        try {
            return localStorage.getItem(KUNCI_TEMA);
        } catch (e) {
            return null;
        }
    }

    function terapkanTema(tema) {
        const gelap = tema === 'gelap';
        document.documentElement.classList.toggle('dark', gelap);
        try {
            localStorage.setItem(KUNCI_TEMA, tema);
        } catch (e) {
            /* abaikan bila penyimpanan tidak tersedia */
        }
        // Sinkronkan semua tombol toggle tema
        document.querySelectorAll('[data-toggle-tema]').forEach(function (tombol) {
            tombol.setAttribute('aria-pressed', gelap ? 'true' : 'false');
            tombol.setAttribute('aria-label', gelap ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
        });
    }

    function temaAwal() {
        const tersimpan = temaTersimpan();
        if (tersimpan === 'gelap' || tersimpan === 'terang') return tersimpan;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'gelap' : 'terang';
    }

    // Terapkan sedini mungkin untuk menghindari kedip
    terapkanTema(temaAwal());

    // Fungsi global agar bisa dipanggil dari onclick="toggleTema()"
    window.toggleTema = function () {
        const saatIniGelap = document.documentElement.classList.contains('dark');
        terapkanTema(saatIniGelap ? 'terang' : 'gelap');
    };

    /* ------------------------------------------------------------------
     * 2. Menu mobile
     * ------------------------------------------------------------------ */
    function siapkanMenuMobile() {
        const tombol = document.querySelector('[data-toggle-menu]');
        const menu = document.querySelector('[data-menu-mobile]');
        if (!tombol || !menu) return;

        tombol.addEventListener('click', function () {
            const terbuka = menu.classList.toggle('hidden');
            tombol.setAttribute('aria-expanded', terbuka ? 'false' : 'true');
        });
    }

    /* ------------------------------------------------------------------
     * 3. Smooth scroll untuk tautan jangkar (#...)
     * ------------------------------------------------------------------ */
    function siapkanSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (tautan) {
            tautan.addEventListener('click', function (e) {
                const id = tautan.getAttribute('href');
                if (id.length < 2) return;
                const target = document.querySelector(id);
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    /* ------------------------------------------------------------------
     * 4. Form pengecekan -> POST /api/check
     * ------------------------------------------------------------------ */
    const WARNA_VERDICT = {
        aman: {
            badge: 'bg-green-100 text-green-800 ring-green-600/30 dark:bg-green-900/40 dark:text-green-300 dark:ring-green-400/30',
            bar: 'bg-green-500',
            label: 'AMAN',
        },
        hati_hati: {
            badge: 'bg-amber-100 text-amber-800 ring-amber-600/30 dark:bg-amber-900/40 dark:text-amber-300 dark:ring-amber-400/30',
            bar: 'bg-amber-500',
            label: 'HATI-HATI',
        },
        bahaya: {
            badge: 'bg-red-100 text-red-800 ring-red-600/30 dark:bg-red-900/40 dark:text-red-300 dark:ring-red-400/30',
            bar: 'bg-red-500',
            label: 'BAHAYA',
        },
    };

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function tampilkanLoading(wadah) {
        wadah.innerHTML =
            '<div class="flex items-center justify-center gap-3 rounded-xl border border-gray-200 bg-white p-8 dark:border-gray-700 dark:bg-gray-800">' +
            '<svg class="h-6 w-6 animate-spin text-indigo-600" fill="none" viewBox="0 0 24 24" aria-hidden="true">' +
            '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
            '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>' +
            '</svg>' +
            '<p class="text-sm text-gray-600 dark:text-gray-300">Memeriksa, mohon tunggu…</p>' +
            '</div>';
    }

    function tampilkanError(wadah, pesan) {
        wadah.innerHTML =
            '<div class="rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-700 dark:bg-red-900/30 dark:text-red-200" role="alert">' +
            '<p class="font-semibold">Ups, pemeriksaannya gagal.</p>' +
            '<p class="mt-1">' + pesan + '</p>' +
            '</div>';
    }

    function escapeHtml(teks) {
        const div = document.createElement('div');
        div.textContent = String(teks == null ? '' : teks);
        return div.innerHTML;
    }

    function renderHasil(wadah, data) {
        const warna = WARNA_VERDICT[data.verdict] || WARNA_VERDICT.hati_hati;
        const skor = Math.max(0, Math.min(100, Number(data.risk_score) || 0));
        const jumlahLaporan = Number(data.report_count) || 0;

        wadah.innerHTML =
            '<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">' +
            '<div class="flex flex-wrap items-center gap-3">' +
            '<span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide ring-1 ring-inset ' + warna.badge + '">' +
            '<span class="h-2 w-2 rounded-full ' + warna.bar + '"></span>' + escapeHtml(data.verdict_label || warna.label) +
            '</span>' +
            '<span class="text-xs text-gray-500 dark:text-gray-400">' + jumlahLaporan + ' laporan komunitas</span>' +
            '</div>' +
            '<div class="mt-4">' +
            '<div class="mb-1 flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">' +
            '<span>Skor risiko</span><span class="font-bold">' + skor + '/100</span>' +
            '</div>' +
            '<div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">' +
            '<div class="h-full rounded-full ' + warna.bar + '" style="width:' + skor + '%"></div>' +
            '</div>' +
            '</div>' +
            '<p class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-200">' + escapeHtml(data.explanation) + '</p>' +
            '</div>';

        wadah.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Kirim form pengecekan. Dipanggil dari onsubmit="return kirimCek(event)".
     */
    window.kirimCek = async function (event) {
        if (event) event.preventDefault();

        const form = document.getElementById('form-cek');
        const inputTipe = document.getElementById('cek-tipe');
        const inputNilai = document.getElementById('cek-nilai');
        const wadah = document.getElementById('hasil-cek');
        if (!form || !inputTipe || !inputNilai || !wadah) return false;

        const tipe = inputTipe.value;
        const nilai = inputNilai.value.trim();
        if (!nilai) {
            tampilkanError(wadah, 'Isi dulu pesan, URL, nomor, atau rekening yang mau diperiksa ya.');
            inputNilai.focus();
            return false;
        }

        tampilkanLoading(wadah);

        try {
            const respons = await fetch('/api/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({ type: tipe, value: nilai }),
            });

            if (respons.status === 422) {
                const body = await respons.json().catch(function () { return {}; });
                const pesanValidasi = body.message || 'Data yang dikirim belum valid. Periksa lagi isianmu ya.';
                tampilkanError(wadah, pesanValidasi);
                return false;
            }

            if (!respons.ok) {
                throw new Error('HTTP ' + respons.status);
            }

            const body = await respons.json();
            renderHasil(wadah, body.data || {});
        } catch (err) {
            tampilkanError(
                wadah,
                'Koneksi ke server bermasalah. Cek koneksi internetmu lalu coba lagi ya.'
            );
        }

        return false;
    };

    /* ------------------------------------------------------------------
     * Inisialisasi saat DOM siap
     * ------------------------------------------------------------------ */
    function init() {
        // Ikat ulang tombol tema (untuk yang memakai addEventListener)
        document.querySelectorAll('[data-toggle-tema]').forEach(function (tombol) {
            tombol.addEventListener('click', window.toggleTema);
        });
        siapkanMenuMobile();
        siapkanSmoothScroll();

        // Ikat form cek otomatis bila memakai onsubmit bawaan
        const form = document.getElementById('form-cek');
        if (form && !form.hasAttribute('data-cek-manual')) {
            form.addEventListener('submit', window.kirimCek);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
