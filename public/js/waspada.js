/**
 * Waspada — skrip standalone (public/js/waspada.js).
 *
 * Salinan ringan dari resources/js/app.js untuk dipakai di luar build Vite,
 * misalnya pada halaman statis atau saat skrip utama belum di-bundle.
 * Cukup sertakan: <script src="/js/waspada.js" defer></script>
 *
 * Menyediakan fungsi global:
 *   - toggleTema()  : ganti mode gelap/terang (tersimpan di localStorage)
 *   - kirimCek(e)   : kirim form #form-cek ke /api/check, render ke #hasil-cek
 *
 * Kebutuhan DOM sama seperti app.js:
 *   <meta name="csrf-token">, #form-cek, #cek-tipe, #cek-nilai, #hasil-cek
 */
(function () {
    'use strict';

    /* ---------- Mode gelap / terang ---------- */
    var KUNCI = 'waspada-tema';

    function bacaTema() {
        try { return localStorage.getItem(KUNCI); } catch (e) { return null; }
    }
    function tulisTema(t) {
        try { localStorage.setItem(KUNCI, t); } catch (e) {}
    }
    function terapkan(t) {
        document.documentElement.classList.toggle('dark', t === 'gelap');
        tulisTema(t);
    }
    var awal = bacaTema();
    if (awal !== 'gelap' && awal !== 'terang') {
        awal = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'gelap' : 'terang';
    }
    terapkan(awal);

    window.toggleTema = function () {
        terapkan(document.documentElement.classList.contains('dark') ? 'terang' : 'gelap');
    };

    /* ---------- Util ---------- */
    function esc(s) {
        var d = document.createElement('div');
        d.textContent = String(s == null ? '' : s);
        return d.innerHTML;
    }
    function tokenCsrf() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    var WARNA = {
        aman:      { badge: 'background:#dcfce7;color:#166534', bar: '#22c55e', label: 'AMAN' },
        hati_hati: { badge: 'background:#fef3c7;color:#92400e', bar: '#f59e0b', label: 'HATI-HATI' },
        bahaya:    { badge: 'background:#fee2e2;color:#991b1b', bar: '#ef4444', label: 'BAHAYA' }
    };

    /* ---------- kirimCek ---------- */
    window.kirimCek = function (e) {
        if (e) e.preventDefault();
        var tipeEl = document.getElementById('cek-tipe');
        var nilaiEl = document.getElementById('cek-nilai');
        var wadah = document.getElementById('hasil-cek');
        if (!tipeEl || !nilaiEl || !wadah) return false;

        var nilai = nilaiEl.value.trim();
        if (!nilai) {
            wadah.innerHTML = '<p style="color:#b91c1c">Isi dulu pesan, URL, nomor, atau rekening yang mau diperiksa ya.</p>';
            nilaiEl.focus();
            return false;
        }

        wadah.innerHTML = '<p>Memeriksa, mohon tunggu…</p>';

        fetch('/api/check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': tokenCsrf()
            },
            body: JSON.stringify({ type: tipeEl.value, value: nilai })
        })
        .then(function (r) {
            if (r.status === 422) throw new Error('Data yang dikirim belum valid. Periksa lagi isianmu ya.');
            if (!r.ok) throw new Error('jaringan');
            return r.json();
        })
        .then(function (body) {
            var d = body.data || {};
            var w = WARNA[d.verdict] || WARNA.hati_hati;
            var skor = Math.max(0, Math.min(100, Number(d.risk_score) || 0));
            wadah.innerHTML =
                '<div style="border:1px solid #e5e7eb;border-radius:12px;padding:16px">' +
                '<span style="display:inline-block;border-radius:9999px;padding:4px 12px;font-size:12px;font-weight:700;' + w.badge + '">' +
                esc(d.verdict_label || w.label) + '</span>' +
                '<p style="margin:8px 0 4px;font-size:13px;color:#6b7280">Skor risiko: <b>' + skor + '/100</b> · ' +
                (Number(d.report_count) || 0) + ' laporan komunitas</p>' +
                '<div style="height:10px;border-radius:9999px;background:#e5e7eb">' +
                '<div style="height:100%;border-radius:9999px;background:' + w.bar + ';width:' + skor + '%"></div></div>' +
                '<p style="margin-top:12px;font-size:14px">' + esc(d.explanation) + '</p>' +
                '</div>';
        })
        .catch(function (err) {
            var pesan = err.message === 'jaringan'
                ? 'Koneksi ke server bermasalah. Cek koneksi internetmu lalu coba lagi ya.'
                : err.message;
            wadah.innerHTML = '<p style="color:#b91c1c"><b>Ups, pemeriksaannya gagal.</b><br>' + esc(pesan) + '</p>';
        });

        return false;
    };

    /* ---------- Inisialisasi ---------- */
    function init() {
        var tombolTema = document.querySelectorAll('[data-toggle-tema]');
        for (var i = 0; i < tombolTema.length; i++) {
            tombolTema[i].addEventListener('click', window.toggleTema);
        }
        var tombolMenu = document.querySelector('[data-toggle-menu]');
        var menu = document.querySelector('[data-menu-mobile]');
        if (tombolMenu && menu) {
            tombolMenu.addEventListener('click', function () { menu.classList.toggle('hidden'); });
        }
        var form = document.getElementById('form-cek');
        if (form) form.addEventListener('submit', window.kirimCek);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
