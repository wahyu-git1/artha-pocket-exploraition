@extends('layouts.mobile')

@section('content')
<div class="bg-white pt-10 pb-4 px-6 sticky top-0 z-20 shadow-sm">
    <h2 class="text-xl font-bold text-[#1F4E79] mb-4">Transaksi</h2>
    
    <!-- Search Bar -->
    <div class="relative mb-4">
        <span class="absolute left-4 top-2.5 text-gray-400">🔍</span>
        <input type="text" placeholder="Cari transaksi..." class="w-full pl-10 pr-4 py-2.5 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none">
    </div>

    <!-- Filter Chips -->
    <div class="flex gap-2 overflow-x-auto hide-scrollbar -mx-6 px-6">
        <div class="bg-[#1F4E79] text-white px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap">Bulan ini</div>
        <div class="bg-[#F6F8FB] text-gray-600 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-gray-200">Semua Kategori</div>
        <div class="bg-[#F6F8FB] text-gray-600 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-gray-200">Semua Sumber</div>
    </div>
</div>

<div class="px-4 py-4 flex-1 pb-24 overflow-y-auto">
    <!-- Summary Strip -->
    <div class="bg-red-50 rounded-xl p-4 mb-6 flex justify-between items-center border border-red-100">
        <span class="text-sm font-medium text-red-800">Total pengeluaran bulan ini</span>
        <span class="font-bold text-red-600">Rp10.000</span>
    </div>

    <!-- Group: Kemarin -->
    <div class="mb-6">
        <h3 class="text-xs font-bold text-gray-400 mb-3 ml-2">KEMARIN</h3>
        <div class="bg-white rounded-[16px] shadow-sm overflow-hidden">
            <!-- Hint Swipe to Delete -->
            <div class="relative bg-red-500">
                <div class="absolute right-0 inset-y-0 flex items-center pr-6 text-white text-sm font-medium">Hapus</div>
                
                <!-- Row Item (Di-geser sedikit ke kiri untuk simulasi swipe) -->
                <div class="relative bg-white flex justify-between items-center p-4 border-b border-gray-50 transform -translate-x-12 transition-transform">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-xl">🍜</div>
                        <div>
                            <p class="font-bold text-sm text-gray-900">Bakso</p>
                            <div class="flex gap-1 items-center mt-0.5">
                                <span class="text-[10px] text-gray-500">Makanan & Minuman</span>
                                <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded">Gaji</span>
                            </div>
                        </div>
                    </div>
                    <p class="font-bold text-red-500 text-sm">-Rp10.000</p>
                </div>
            </div>
            
            <div class="relative bg-white flex justify-between items-center p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center text-xl">💰</div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">Gajian</p>
                        <div class="flex gap-1 items-center mt-0.5">
                            <span class="text-[10px] text-gray-500">Pendapatan</span>
                            <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded">Gaji</span>
                        </div>
                    </div>
                </div>
                <p class="font-bold text-green-500 text-sm">+Rp6.000.000</p>
            </div>
        </div>
    </div>
</div>
@endsection