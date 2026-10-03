@extends('layouts.mobile')

@section('content')
<div class="bg-white pt-10 pb-4 px-6 sticky top-0 z-20 shadow-sm flex items-center gap-4">
    <a href="/transaksi" class="text-2xl text-gray-400 hover:text-[#1F4E79]">←</a>
    <h2 class="text-xl font-bold text-[#1F4E79]">Ubah Transaksi</h2>
</div>

<div class="px-6 py-6 flex-1 overflow-y-auto">
    <!-- Read Only Section: Smart Entry Hint -->
    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <span class="text-xl mt-0.5">⚡</span>
            <div>
                <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider mb-1">Dicatat lewat Smart Entry (Akurasi 94%)</p>
                <p class="text-sm text-gray-600 italic">"beli bakso 10k"</p>
            </div>
        </div>
    </div>

    <!-- Form Edit -->
    <div class="space-y-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Item</label>
            <input type="text" value="Bakso" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl font-medium focus:outline-none">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nominal</label>
            <div class="relative">
                <span class="absolute left-4 top-3 text-gray-500 font-medium">Rp</span>
                <input type="text" value="10.000" class="w-full pl-12 pr-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl font-bold text-red-500 focus:outline-none">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                <input type="date" value="2026-10-02" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Kategori</label>
                <select class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none">
                    <option selected>Makanan & Minuman</option>
                </select>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sumber Pendapatan</label>
            <select class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none">
                <option selected>Gaji</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan (Opsional)</label>
            <textarea rows="2" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none"></textarea>
        </div>
    </div>
</div>

<div class="p-6 bg-white border-t border-gray-100 space-y-3">
    <button class="w-full bg-[#1F4E79] text-white font-semibold py-3.5 rounded-xl shadow-md">Simpan perubahan</button>
    <button class="w-full bg-transparent text-red-500 font-bold py-3.5 rounded-xl">Hapus transaksi</button>
</div>
@endsection