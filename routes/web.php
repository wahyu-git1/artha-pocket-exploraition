<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () { return view('landing'); });

Route::get('/login', function () {
    return view('auth.login'); // Kita simpan file di dalam folder auth
});

Route::get('/register', function () {
    return view('auth.register'); // Kita simpan file di dalam folder auth
});

Route::get('/onboarding', function () { return view('onboarding'); });
Route::get('/beranda', function () { return view('beranda'); });
Route::get('/transaksi', function () { return view('transaksi'); });
Route::get('/transaksi/detail', function () { return view('transaksi-detail'); });
Route::get('/kategori', function () { return view('kategori'); });
Route::get('/laporan', function () { return view('laporan'); });