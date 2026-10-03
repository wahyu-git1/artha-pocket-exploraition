@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Kolom Kiri (Porsi Lebih Lebar) -->
    <div class="lg:col-span-2 space-y-6">
        
        <!-- Kartu Saldo -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex justify-between items-center">
            <div>
                <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Sisa Saldo (Pemasukan Utama)</h2>
                <p class="text-4xl font-bold text-gray-900 mt-1">Rp {{ number_format($dummyData['saldo'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Kolom Smart Entry -->
        <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
            <h3 class="text-lg font-bold text-blue-900 mb-1">⚡ Smart Entry</h3>
            <p class="text-sm text-blue-700 mb-4">Catat pengeluaran secepat mengetik pesan. Sistem akan mengatur nominal dan kategorinya.</p>
            <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="text" name="raw_input" placeholder="Contoh: beli bakso 10k kemarin..." class="w-full px-4 py-3 rounded-xl border border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition">
                <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-sm transition whitespace-nowrap">
                    Catat
                </button>
            </form>
        </div>

        <!-- Tabel Riwayat Transaksi -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-bold text-gray-900">Riwayat Terakhir</h3>
                <a href="#" class="text-sm text-blue-600 font-semibold hover:underline">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($dummyData['transaksi'] as $tx)
                <div class="p-4 sm:px-6 flex justify-between items-center hover:bg-gray-50 transition cursor-pointer">
                    <div class="flex items-center gap-4">
                        <div class="bg-orange-100 p-2 rounded-lg">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $tx['item'] }}</p>
                            <p class="text-xs text-gray-500">{{ $tx['kategori'] }} • {{ $tx['tanggal'] }}</p>
                        </div>
                    </div>
                    <div class="text-red-600 font-bold">
                        -Rp {{ number_format($tx['nominal'], 0, ',', '.') }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Kolom Kanan -->
    <div class="space-y-6">
        <!-- Ringkasan Pengeluaran -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Pengeluaran Bulan Ini</h3>
            <p class="text-3xl font-bold text-red-500 mb-6">Rp {{ number_format($dummyData['pengeluaran_bulan_ini'], 0, ',', '.') }}</p>
            
            <div class="space-y-4">
                <!-- Bar Kategori 1 -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 font-medium">Makanan & Minuman</span>
                        <span class="font-bold text-gray-900">60%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="bg-orange-500 h-2.5 rounded-full" style="width: 60%"></div>
                    </div>
                </div>
                <!-- Bar Kategori 2 -->
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-600 font-medium">Transportasi</span>
                        <span class="font-bold text-gray-900">40%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                        <div class="bg-blue-500 h-2.5 rounded-full" style="width: 40%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection