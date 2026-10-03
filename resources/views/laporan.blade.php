@extends('layouts.mobile')

@section('content')
<div class="bg-cosmic stars pt-10 pb-20 px-6 text-white rounded-b-[32px] relative z-10">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Laporan</h2>
        <div class="bg-white/10 px-4 py-1.5 rounded-full text-sm font-medium backdrop-blur-sm">Okt 2026 ▼</div>
    </div>
    
    <!-- Bar Chart (Mockup 6 Bulan) -->
    <div class="mt-8 flex items-end justify-between h-32 px-2 border-b border-white/20 pb-2">
        <!-- Mei -->
        <div class="flex gap-1 w-full justify-center">
            <div class="w-3 bg-[#48CAE4] rounded-t-sm h-16 opacity-50"></div>
            <div class="w-3 bg-red-400 rounded-t-sm h-12 opacity-50"></div>
        </div>
        <!-- ... (Bulan lainnya diringkas untuk visual) -->
        <!-- Okt -->
        <div class="flex gap-1 w-full justify-center">
            <div class="w-3 bg-[#48CAE4] rounded-t-sm h-24"></div>
            <div class="w-3 bg-red-400 rounded-t-sm h-8"></div>
        </div>
    </div>
    <div class="flex justify-between text-[10px] text-gray-400 mt-2 px-4">
        <span>Mei</span> <span>Jun</span> <span>Jul</span> <span>Ags</span> <span>Sep</span> <span class="text-white font-bold">Okt</span>
    </div>
</div>

<div class="px-6 -mt-6 z-20 flex-1 overflow-y-auto pb-24">
    <div class="bg-white rounded-[16px] shadow-sm p-5 mb-4">
        <h3 class="font-bold text-[#1F4E79] mb-4">Top Pengeluaran</h3>
        
        <div class="space-y-4">
            <!-- Rank 1 -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <div class="flex items-center gap-2">
                        <span class="text-orange-500">🍜</span>
                        <span class="font-bold text-sm text-gray-900">Makanan & Minuman</span>
                    </div>
                    <span class="font-bold text-red-500 text-sm">Rp600.000</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-full bg-gray-100 h-2 rounded-full"><div class="bg-orange-400 h-2 rounded-full" style="width: 60%;"></div></div>
                    <span class="text-xs font-bold text-gray-500">60%</span>
                </div>
            </div>
            
            <!-- Rank 2 -->
            <div>
                <div class="flex justify-between items-center mb-1">
                    <div class="flex items-center gap-2">
                        <span class="text-blue-500">🚗</span>
                        <span class="font-bold text-sm text-gray-900">Transportasi</span>
                    </div>
                    <span class="font-bold text-red-500 text-sm">Rp400.000</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-full bg-gray-100 h-2 rounded-full"><div class="bg-blue-400 h-2 rounded-full" style="width: 40%;"></div></div>
                    <span class="text-xs font-bold text-gray-500">40%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="p-6 bg-white border-t border-gray-100 absolute bottom-0 left-0 w-full z-30">
    <button class="w-full border-2 border-gray-200 text-gray-700 font-bold py-3.5 rounded-xl hover:bg-gray-50 flex justify-center items-center gap-2">
        📥 Ekspor CSV
    </button>
</div>
@endsection