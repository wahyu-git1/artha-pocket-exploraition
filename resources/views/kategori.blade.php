@extends('layouts.mobile')

@section('content')
<div class="bg-[#1F4E79] pt-10 px-6 pb-0 text-white rounded-b-[24px] z-10 relative shadow-md">
    <div class="flex items-center gap-4 mb-6">
        <a href="/beranda" class="text-2xl hover:text-[#48CAE4]">←</a>
        <h2 class="text-xl font-bold">Kelola Kategori</h2>
    </div>
    
    <!-- Tabs -->
    <div class="flex justify-between border-b border-white/20">
        <button class="flex-1 pb-3 text-center border-b-2 border-[#48CAE4] font-bold text-[#48CAE4]">Pengeluaran</button>
        <button class="flex-1 pb-3 text-center text-gray-300 font-medium">Pemasukan</button>
    </div>
</div>

<div class="px-4 py-6 flex-1 overflow-y-auto">
    <h3 class="text-xs font-bold text-gray-400 mb-3 ml-2 uppercase">Bawaan Sistem</h3>
    <div class="bg-white rounded-[16px] shadow-sm overflow-hidden mb-6">
        <!-- Default Item -->
        <div class="flex justify-between items-center p-4 border-b border-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-xl relative">
                    🍜 <span class="absolute bottom-0 right-0 w-3 h-3 bg-orange-500 border-2 border-white rounded-full"></span>
                </div>
                <div>
                    <p class="font-bold text-sm text-gray-900">Makanan & Minuman</p>
                    <span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium mt-1 inline-block">Kebutuhan</span>
                </div>
            </div>
            <span class="text-gray-300 text-sm">🔒</span>
        </div>
        <!-- Default Item 2 -->
        <div class="flex justify-between items-center p-4 border-b border-gray-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center text-xl relative">
                    🎮 <span class="absolute bottom-0 right-0 w-3 h-3 bg-purple-500 border-2 border-white rounded-full"></span>
                </div>
                <div>
                    <p class="font-bold text-sm text-gray-900">Hiburan</p>
                    <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full font-medium mt-1 inline-block">Keinginan</span>
                </div>
            </div>
            <span class="text-gray-300 text-sm">🔒</span>
        </div>
    </div>

    <h3 class="text-xs font-bold text-gray-400 mb-3 ml-2 uppercase">Kategori Kustom</h3>
    <div class="bg-white rounded-[16px] shadow-sm overflow-hidden border border-dashed border-gray-300 p-6 text-center">
        <p class="text-sm text-gray-500">Belum ada kategori kustom.</p>
    </div>
</div>

<!-- Floating Action Button -->
<button class="absolute bottom-8 right-6 w-14 h-14 bg-[#48CAE4] rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-lg shadow-teal-500/40 z-30">
    +
</button>

<!-- Hidden Modal: Tambah Kategori -->
<!-- (Bisa dimunculkan dengan state management Alpine.js/JS murni nanti) -->
@endsection