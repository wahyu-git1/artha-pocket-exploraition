@extends('layouts.mobile')

@section('content')
<!-- Header Kosmik -->
<div class="bg-cosmic stars pt-10 pb-20 px-6 text-white rounded-b-[32px]">
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-gray-300">Halo,</p>
            <h1 class="text-xl font-bold">Rina 👋</h1>
        </div>
        <div class="bg-white/10 px-4 py-1.5 rounded-full text-sm font-medium backdrop-blur-sm">
            Okt 2026 ▼
        </div>
    </div>

    <!-- Hero Card -->
    <div class="bg-white/10 border border-white/20 p-5 rounded-[16px] backdrop-blur-md text-center">
        <p class="text-sm text-gray-200">Total Saldo</p>
        <h2 class="text-4xl font-bold mt-1 mb-4">Rp6.990.000</h2>
        <div class="flex justify-between text-xs font-medium px-2">
            <div class="flex items-center gap-1 text-[#48CAE4]">↓ Pemasukan Rp7.000.000</div>
            <div class="flex items-center gap-1 text-red-300">↑ Pengeluaran Rp10.000</div>
        </div>
    </div>
</div>

<div class="px-6 -mt-6 z-10 flex-1 pb-40">
    <!-- Scrollable Income Cards -->
    <div class="flex gap-4 overflow-x-auto pb-4 pt-2 -mx-6 px-6 hide-scrollbar">
        <!-- Card 1 -->
        <div class="min-w-[200px] bg-white p-4 rounded-[16px] shadow-sm">
            <div class="flex justify-between mb-2">
                <h3 class="font-bold text-[#1F4E79]">Gaji</h3>
                <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Utama</span>
            </div>
            <p class="text-lg font-bold text-gray-900">Rp5.990.000</p>
            <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3">
                <div class="bg-[#48CAE4] h-1.5 rounded-full" style="width: 1%;"></div>
            </div>
        </div>
        <!-- Card 2 -->
        <div class="min-w-[200px] bg-white p-4 rounded-[16px] shadow-sm">
            <h3 class="font-bold text-[#1F4E79] mb-2">Freelance</h3>
            <p class="text-lg font-bold text-gray-900">Rp1.000.000</p>
            <div class="w-full bg-gray-100 h-1.5 rounded-full mt-3">
                <div class="bg-gray-300 h-1.5 rounded-full" style="width: 0%;"></div>
            </div>
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="mt-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-[#1F4E79]">Transaksi terbaru</h3>
            <a href="#" class="text-xs font-medium text-[#48CAE4]">Lihat semua</a>
        </div>
        <div class="bg-white rounded-[16px] p-2 shadow-sm space-y-1">
            <div class="flex justify-between items-center p-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-xl">🍜</div>
                    <div>
                        <p class="font-semibold text-sm">Bakso</p>
                        <p class="text-xs text-gray-500">Makanan & Minuman • Kemarin</p>
                    </div>
                </div>
                <p class="font-bold text-red-500 text-sm">-Rp10.000</p>
            </div>
        </div>
    </div>
</div>

<!-- Sticky Input Bar (Di atas Navigasi) -->
<div class="absolute bottom-[72px] left-0 w-full px-4 pb-2 bg-gradient-to-t from-[#F6F8FB] to-transparent">
    <div class="bg-white p-2 rounded-2xl shadow-lg border border-blue-50 flex gap-2">
        <input type="text" placeholder="Ketik, mis. beli bakso 10k" class="flex-1 bg-[#F6F8FB] px-4 py-3 rounded-xl text-sm outline-none focus:ring-1 focus:ring-[#1F4E79]">
        <button class="bg-[#1F4E79] w-12 h-12 rounded-xl flex items-center justify-center text-white shrink-0">
            ➤
        </button>
    </div>
</div>

<!-- Bottom Navigation Bar -->
<div class="absolute bottom-0 left-0 w-full h-[72px] bg-white border-t border-gray-100 flex justify-between items-center px-6 pb-2 text-xs font-medium text-gray-400">
    <div class="flex flex-col items-center text-[#1F4E79]"><span class="text-xl mb-1">🏠</span>Beranda</div>
    <div class="flex flex-col items-center"><span class="text-xl mb-1">📄</span>Transaksi</div>
    <div class="w-12 h-12 bg-[#48CAE4] rounded-full flex items-center justify-center text-white text-2xl font-bold -mt-8 shadow-lg shadow-teal-500/30">+</div>
    <div class="flex flex-col items-center"><span class="text-xl mb-1">🎯</span>Target</div>
    <div class="flex flex-col items-center"><span class="text-xl mb-1">☰</span>Lainnya</div>
</div>

<!-- MODAL SMART ENTRY (Variant 2 item) - Un-comment untuk melihatnya -->

<div class="absolute inset-0 bg-gray-900/40 z-50 flex flex-col justify-end">
    <div class="bg-white rounded-t-[24px] p-6 pb-8 shadow-2xl animate-slide-up">
        <h3 class="font-bold text-[#1F4E79] mb-4">Konfirmasi Pencatatan</h3>
        
        <div class="space-y-3 mb-6">
            <div class="flex justify-between items-center bg-[#F6F8FB] p-4 rounded-xl border border-gray-100">
                <div>
                    <p class="font-bold">Bakso</p>
                    <p class="text-xs text-gray-500">Makanan & Minuman • Kemarin</p>
                </div>
                <div class="flex items-center gap-4">
                    <p class="font-bold">Rp10.000</p>
                    <button class="text-red-400 text-lg">×</button>
                </div>
            </div>
            
            <div class="flex justify-between items-center bg-[#F6F8FB] p-4 rounded-xl border border-gray-100">
                <div>
                    <p class="font-bold">Es teh</p>
                    <p class="text-xs text-gray-500">Makanan & Minuman • Kemarin</p>
                </div>
                <div class="flex items-center gap-4">
                    <p class="font-bold">Rp5.000</p>
                    <button class="text-red-400 text-lg">×</button>
                </div>
            </div>
        </div>

        <div class="flex justify-between items-center mb-6 px-2">
            <span class="text-gray-500 text-sm">Total pengeluaran</span>
            <span class="font-bold text-xl text-red-500">Rp15.000</span>
        </div>

        <div class="flex gap-3">
            <button class="flex-1 py-3.5 rounded-xl font-semibold text-gray-500 bg-gray-100">Batal</button>
            <button class="flex-1 py-3.5 rounded-xl font-semibold text-white bg-[#1F4E79]">Simpan semua</button>
        </div>
    </div>
</div>

@endsection