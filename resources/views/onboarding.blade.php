@extends('layouts.mobile')

@section('content')
<div class="bg-cosmic stars pt-12 pb-24 px-6 text-white rounded-b-[32px]">
    <div class="flex gap-2 mb-8 justify-center">
        <div class="h-2 w-8 bg-[#48CAE4] rounded-full"></div>
        <div class="h-2 w-8 bg-white/20 rounded-full"></div>
        <div class="h-2 w-8 bg-white/20 rounded-full"></div>
    </div>
    <h2 class="text-2xl font-bold">Tambahkan pendapatanmu</h2>
    <p class="text-sm text-gray-300 mt-2 opacity-90">Mari mulai dengan mencatat sumber uang utamamu.</p>
</div>

<div class="px-6 -mt-16 flex-1 mb-8">
    <div class="bg-white rounded-[16px] shadow-sm p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama sumber pendapatan</label>
            <input type="text" placeholder="Mis. Gaji" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl focus:ring-2 focus:ring-[#1F4E79] outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nominal biasa</label>
            <div class="relative">
                <span class="absolute left-4 top-3 text-gray-500 font-medium">Rp</span>
                <input type="text" placeholder="6.000.000" class="w-full pl-12 pr-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl focus:ring-2 focus:ring-[#1F4E79] outline-none">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Frekuensi</label>
                <select class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl focus:ring-2 focus:ring-[#1F4E79] outline-none text-sm">
                    <option>Bulanan</option>
                    <option>Mingguan</option>
                    <option>Tidak tetap</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tgl Gajian</label>
                <input type="number" placeholder="25" min="1" max="31" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl focus:ring-2 focus:ring-[#1F4E79] outline-none">
            </div>
        </div>
        <div class="pt-2 flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Jadikan pendapatan utama</span>
            <div class="w-12 h-6 bg-[#48CAE4] rounded-full relative cursor-pointer">
                <div class="w-5 h-5 bg-white rounded-full absolute right-0.5 top-0.5 shadow"></div>
            </div>
        </div>
    </div>
    
    <button class="w-full border-2 border-dashed border-gray-300 text-gray-500 font-semibold py-3 px-4 rounded-xl mt-4 hover:bg-gray-50 transition">
        + Tambah pendapatan lain
    </button>
</div>

<div class="p-6 bg-white border-t border-gray-100">
    <button class="w-full bg-[#1F4E79] text-white font-semibold py-4 rounded-xl shadow-lg">Lanjut</button>
</div>
@endsection