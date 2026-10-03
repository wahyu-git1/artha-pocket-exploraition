<?php
use Illuminate\Support\Facades\Route;

// --- RUTE FRONTEND ---
Route::get('/', function () { return view('landing'); });
Route::get('/login', function () { return view('auth.login'); });
Route::get('/register', function () { return view('auth.register'); });
Route::get('/onboarding', function () { return view('onboarding'); });
Route::get('/beranda', function () { return view('beranda'); });
Route::get('/transaksi', function () { return view('transaksi'); });
Route::get('/transaksi/detail', function () { return view('transaksi-detail'); });
Route::get('/kategori', function () { return view('kategori'); });
Route::get('/laporan', function () { return view('laporan'); });

// --- RUTE SWAGGER (BACKEND) ---
Route::get('/docs/swagger', function () { return view('swagger'); });
Route::get('/swagger', function () { return redirect('/docs/swagger'); });
Route::get('/docs', function () { return redirect('/docs/swagger'); });