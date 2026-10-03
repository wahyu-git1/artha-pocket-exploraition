<?php
use Illuminate\Support\Facades\Route;

// --- RUTE FRONTEND ---
Route::view('/', 'landing')->name('landing');

Route::view('/login', 'login')->name('login');
Route::view('/register', 'auth.register')->name('register');

Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::redirect('/beranda', '/dashboard')->name('beranda');
Route::view('/onboarding', 'onboarding')->name('onboarding');

Route::view('/transaksi', 'transaksi')->name('transaksi');

Route::view('/transaksi/detail', 'transaksi-detail')->name('transaksi.detail');

Route::view('/laporan', 'laporan')->name('laporan');

Route::view('/kategori', 'kategori')->name('kategori');

Route::view('/target', 'target')->name('target');

Route::view('/dana-darurat', 'dana-darurat')->name('dana-darurat');

Route::view('/alokasi', 'alokasi')->name('alokasi');

Route::view('/investasi', 'investasi')->name('investasi');

Route::view('/pengaturan', 'pengaturan')->name('pengaturan');

// --- RUTE SWAGGER (BACKEND) ---
Route::get('/docs/swagger', function () { return view('swagger'); });
Route::get('/swagger', function () { return redirect('/docs/swagger'); });
Route::get('/docs', function () { return redirect('/docs/swagger'); });