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
    // TODO: lindungi dengan middleware auth + admin (divisi security-audit).
    // TODO: ambil dari database — Report::pending()->latest()->paginate(20)
    return view('pages.admin', ['reports' => collect()]);
});
