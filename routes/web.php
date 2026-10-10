<?php

use Illuminate\Support\Facades\Route;

// TODO: pindahkan ke controller (mis. PageController) saat backend siap.
// Untuk sekarang route mengembalikan view langsung dengan data dummy kosong.

Route::get('/', function () {
    return view('pages.beranda');
});

Route::get('/cek', function () {
    return view('pages.cek');
});

Route::get('/komunitas', function () {
    // TODO: ambil dari database — Report::verified()->latest()->paginate(10)
    return view('pages.komunitas', ['reports' => collect()]);
});

Route::get('/admin', function () {
    // Divisi security-audit (WAS-SEC-002): halaman moderasi DINONAKTIFKAN
    // sampai middleware auth + admin diimplementasikan. Jangan biarkan
    // halaman admin dapat diakses publik.
    abort(404);
});
