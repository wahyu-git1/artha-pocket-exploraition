@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Pusat insight keuangan ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Laporan Keuangan</h1>
        <p class="text-sm text-slate-500 mt-2">Analisis arus uang, kategori, dan rencana finansialmu dalam satu layar.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <select class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-[#19B5A5]">
            <option>Oktober 2026</option>
            <option>September 2026</option>
            <option>Agustus 2026</option>
        </select>
        <button type="button" data-demo class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">⇩ Ekspor CSV</button>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    <div class="card p-5 border-l-4 border-l-[#19B5A5]">
        <p class="text-xs font-semibold text-slate-500">Pemasukan bulan ini</p>
        <p class="mt-3 text-2xl font-extrabold text-emerald-600">Rp6.000.000</p>
        <p class="mt-3 text-xs text-emerald-600">↑ 9,1% dibanding September</p>
    </div>
    <div class="card p-5 border-l-4 border-l-rose-400">
        <p class="text-xs font-semibold text-slate-500">Pengeluaran bulan ini</p>
        <p class="mt-3 text-2xl font-extrabold text-rose-500">Rp1.000.000</p>
        <p class="mt-3 text-xs text-emerald-600">↓ 4,8% lebih hemat</p>
    </div>
    <div class="card p-5 border-l-4 border-l-[#1F4E79]">
        <p class="text-xs font-semibold text-slate-500">Arus kas bersih</p>
        <p class="mt-3 text-2xl font-extrabold text-[#1F4E79]">Rp5.000.000</p>
        <p class="mt-3 text-xs text-slate-400">83,3% dari pemasukan</p>
    </div>
    <div class="card p-5 border-l-4 border-l-amber-400">
        <p class="text-xs font-semibold text-slate-500">Rasio menabung</p>
        <p class="mt-3 text-2xl font-extrabold text-amber-600">35%</p>
        <p class="mt-3 text-xs text-slate-400">Target pribadi: 30%</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="card p-6 xl:col-span-2 min-w-0">
        <div class="flex items-start justify-between gap-3 mb-5">
            <div>
                <h2 class="font-extrabold text-lg text-[#102A43]">Tren pemasukan & pengeluaran</h2>
                <p class="text-sm text-slate-500 mt-1">Perbandingan selama enam bulan terakhir.</p>
            </div>
            <span class="badge bg-[#E7F8F5] text-[#148B80] whitespace-nowrap">6 bulan</span>
        </div>
        <div class="relative h-[320px]"><canvas id="monthlyTrendChart"></canvas></div>
    </div>

    <div class="card p-6 min-w-0">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="font-extrabold text-lg text-[#102A43]">Kategori pengeluaran</h2>
                <p class="text-sm text-slate-500 mt-1">Total Rp1.000.000</p>
            </div>
            <span class="text-xl">◉</span>
        </div>
        <div class="relative h-[220px] mt-3"><canvas id="categoryChart"></canvas></div>
        <div class="space-y-3 mt-4 text-sm">
            <div class="flex justify-between"><span class="text-slate-600">● Makanan & Minuman</span><b>Rp600.000</b></div>
            <div class="flex justify-between"><span class="text-blue-500">● Transportasi</span><b>Rp250.000</b></div>
            <div class="flex justify-between"><span class="text-violet-500">● Hiburan</span><b>Rp150.000</b></div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <div class="card p-6">
        <div class="flex justify-between items-start gap-4 mb-6">
            <div><h2 class="font-extrabold text-lg text-[#102A43]">Alokasi: rencana vs aktual</h2><p class="text-sm text-slate-500 mt-1">Status penggunaan pos Oktober 2026.</p></div>
            <a href="{{ route('alokasi') }}" class="text-sm font-bold text-[#1F4E79] whitespace-nowrap">Kelola alokasi →</a>
        </div>
        <div class="space-y-6">
            <div><div class="flex justify-between text-sm mb-2"><span class="font-semibold">Kebutuhan</span><span class="text-slate-500">Rp600.000 / Rp1.000.000 · 60%</span></div><div class="h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-[#19B5A5] w-[60%]"></div></div></div>
            <div><div class="flex justify-between text-sm mb-2"><span class="font-semibold">Keinginan</span><span class="text-slate-500">Rp400.000 / Rp700.000 · 57%</span></div><div class="h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-amber-400 w-[57%]"></div></div></div>
            <div><div class="flex justify-between text-sm mb-2"><span class="font-semibold">Tabungan</span><span class="text-slate-500">Rp1.100.000 / Rp1.500.000 · 73%</span></div><div class="h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-[#1F4E79] w-[73%]"></div></div></div>
            <div><div class="flex justify-between text-sm mb-2"><span class="font-semibold">Investasi</span><span class="text-slate-500">Rp300.000 / Rp500.000 · 60%</span></div><div class="h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-violet-500 w-[60%]"></div></div></div>
        </div>
    </div>

    <div class="card p-6">
        <div class="flex justify-between items-start gap-4 mb-6"><div><h2 class="font-extrabold text-lg text-[#102A43]">Misi tabungan</h2><p class="text-sm text-slate-500 mt-1">Kemajuan target dan dana daruratmu.</p></div><span class="text-2xl">🪐</span></div>
        <div class="space-y-6">
            <div class="rounded-2xl bg-[#F3F8FC] p-5"><div class="flex justify-between gap-4"><div><p class="font-bold">🎮 Laptop baru</p><p class="text-xs text-slate-500 mt-1">Target: 30 Jun 2027</p></div><p class="font-extrabold text-[#1F4E79]">42%</p></div><div class="h-3 rounded-full bg-white mt-4"><div class="h-3 rounded-full bg-[#1F4E79] w-[42%]"></div></div><p class="text-xs text-slate-500 mt-3">Rp4.200.000 dari Rp10.000.000</p></div>
            <div class="rounded-2xl bg-[#ECFAF8] p-5"><div class="flex justify-between gap-4"><div><p class="font-bold">🛟 Dana darurat</p><p class="text-xs text-slate-500 mt-1">Target aman: Rp5.000.000</p></div><p class="font-extrabold text-[#148B80]">18%</p></div><div class="h-3 rounded-full bg-white mt-4"><div class="h-3 rounded-full bg-[#19B5A5] w-[18%]"></div></div><p class="text-xs text-slate-500 mt-3">Rp900.000 dari Rp5.000.000</p></div>
        </div>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4"><div><h2 class="font-extrabold text-lg text-[#102A43]">Insight orbit bulan ini</h2><p class="text-sm text-slate-500 mt-1">Ringkasan singkat berdasarkan data transaksi demo.</p></div><span class="badge bg-blue-50 text-[#1F4E79]">✦ Insight demo</span></div>
    <div class="grid md:grid-cols-3 border-t border-slate-100"><div class="p-6 border-b md:border-b-0 md:border-r border-slate-100"><div class="text-2xl">🍜</div><p class="font-bold mt-3">Makanan mendominasi</p><p class="text-sm text-slate-500 leading-6 mt-2">60% pengeluaran bulan ini berada pada kategori makanan dan minuman.</p></div><div class="p-6 border-b md:border-b-0 md:border-r border-slate-100"><div class="text-2xl">✦</div><p class="font-bold mt-3">Kamu lebih hemat</p><p class="text-sm text-slate-500 leading-6 mt-2">Pengeluaran turun 4,8% dibanding bulan lalu. Pertahankan orbit ini.</p></div><div class="p-6"><div class="text-2xl">🎯</div><p class="font-bold mt-3">Target masih aman</p><p class="text-sm text-slate-500 leading-6 mt-2">Setoran Laptop baru sudah mencapai 42% dari tujuan yang ditetapkan.</p></div></div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('monthlyTrendChart'), {
    type: 'line',
    data: {
        labels: ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
        datasets: [
            { label: 'Pemasukan', data: [4000000, 5000000, 5000000, 6000000, 5500000, 6000000], borderColor: '#19B5A5', backgroundColor: 'rgba(25,181,165,.14)', fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: '#19B5A5' },
            { label: 'Pengeluaran', data: [1500000, 2000000, 1200000, 1700000, 1050000, 1000000], borderColor: '#F07C7C', backgroundColor: 'transparent', fill: false, tension: .35, pointRadius: 4, pointBackgroundColor: '#F07C7C' }
        ]
    },
    options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:8,padding:22}}}, scales:{x:{grid:{display:false}},y:{grid:{color:'#EDF2F7'},ticks:{callback:(value)=>'Rp'+(value/1000000)+'jt'}}} }
});
new Chart(document.getElementById('categoryChart'), {
    type:'doughnut',
    data:{ labels:['Makanan & Minuman','Transportasi','Hiburan'], datasets:[{data:[600000,250000,150000],backgroundColor:['#F59E0B','#3B82F6','#8B5CF6'],borderWidth:0,hoverOffset:5}] },
    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{display:false}}}
});
</script>
@endpush