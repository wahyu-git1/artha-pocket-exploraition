<?php
use Illuminate\Support\Facades\Route;

// --- RUTE FRONTEND ---
Route::view('/', 'landing')->name('landing');

Route::view('/login', 'login')->name('login');

Route::view('/dashboard', 'dashboard')->name('dashboard');

Route::view('/transaksi', 'transaksi')->name('transaksi');

Route::view('/transaksi/detail', 'transaksi-detail')->name('transaksi.detail');

Route::view('/laporan', 'laporan')->name('laporan');

Route::view('/kategori', 'kategori')->name('kategori');

Route::view('/target', 'dashboard')->name('target');

Route::view('/dana-darurat', 'dashboard')->name('dana-darurat');

Route::view('/alokasi', 'dashboard')->name('alokasi');

Route::view('/investasi', 'dashboard')->name('investasi');

Route::view('/pengaturan', 'dashboard')->name('pengaturan');

// --- RUTE SWAGGER (BACKEND) ---
Route::get('/docs/swagger', function () { return view('swagger'); });
Route::get('/swagger', function () { return redirect('/docs/swagger'); });
Route::get('/docs', function () { return redirect('/docs/swagger'); });