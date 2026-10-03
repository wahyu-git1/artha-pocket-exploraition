@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Pusat insight keuangan ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Laporan Keuangan</h1>
        <p class="text-sm text-slate-500 mt-2">Analisis arus uang, kategori, dan rencana finansialmu dalam satu layar.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <select id="month-select" onchange="loadReportData()" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 outline-none focus:ring-2 focus:ring-[#19B5A5]">
            <option value="2026-10">Oktober 2026</option>
            <option value="2026-09">September 2026</option>
            <option value="2026-08">Agustus 2026</option>
        </select>
        <button type="button" onclick="exportTransactions()" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">⇩ Ekspor CSV</button>
    </div>
</div>

<div id="report-loading" class="hidden mb-5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">Memuat laporan dari server...</div>
<div id="report-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    <div class="card p-5 border-l-4 border-l-[#19B5A5]"><p class="text-xs font-semibold text-slate-500">Pemasukan bulan ini</p><p id="total-income" class="mt-3 text-2xl font-extrabold text-emerald-600">—</p><p class="mt-3 text-xs text-slate-400">Data dari API summary</p></div>
    <div class="card p-5 border-l-4 border-l-rose-400"><p class="text-xs font-semibold text-slate-500">Pengeluaran bulan ini</p><p id="total-expense" class="mt-3 text-2xl font-extrabold text-rose-500">—</p><p class="mt-3 text-xs text-slate-400">Data dari API summary</p></div>
    <div class="card p-5 border-l-4 border-l-[#1F4E79]"><p class="text-xs font-semibold text-slate-500">Arus kas bersih</p><p id="net-balance" class="mt-3 text-2xl font-extrabold text-[#1F4E79]">—</p><p class="mt-3 text-xs text-slate-400">Pemasukan dikurangi pengeluaran</p></div>
    <div class="card p-5 border-l-4 border-l-amber-400"><p class="text-xs font-semibold text-slate-500">Rasio menabung</p><p id="saving-ratio" class="mt-3 text-2xl font-extrabold text-amber-600">—</p><p class="mt-3 text-xs text-slate-400">Berdasarkan arus kas bersih</p></div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="card p-6 xl:col-span-2 min-w-0"><div class="flex items-start justify-between gap-3 mb-5"><div><h2 class="font-extrabold text-lg text-[#102A43]">Tren pemasukan & pengeluaran</h2><p class="text-sm text-slate-500 mt-1">Perbandingan selama enam bulan terakhir.</p></div><span class="badge bg-[#E7F8F5] text-[#148B80] whitespace-nowrap">6 bulan</span></div><div class="relative h-[320px]"><canvas id="monthlyTrendChart"></canvas></div></div>
    <div class="card p-6 min-w-0"><div class="flex justify-between items-start"><div><h2 class="font-extrabold text-lg text-[#102A43]">Kategori pengeluaran</h2><p id="category-total" class="text-sm text-slate-500 mt-1">Memuat...</p></div><span class="text-xl">◉</span></div><div class="relative h-[220px] mt-3"><canvas id="categoryChart"></canvas></div><div id="category-legend" class="space-y-3 mt-4 text-sm"></div></div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <div class="card p-6"><div class="flex justify-between items-start gap-4 mb-6"><div><h2 class="font-extrabold text-lg text-[#102A43]">Alokasi: rencana vs aktual</h2><p class="text-sm text-slate-500 mt-1">Status penggunaan pos bulan terpilih.</p></div><a href="{{ route('alokasi') }}" class="text-sm font-bold text-[#1F4E79] whitespace-nowrap">Kelola alokasi →</a></div><div id="allocation-list" class="space-y-6"><div class="text-sm text-slate-400">Data alokasi akan muncul setelah API tersedia.</div></div></div>
    <div class="card p-6"><div class="flex justify-between items-start gap-4 mb-6"><div><h2 class="font-extrabold text-lg text-[#102A43]">Misi tabungan</h2><p class="text-sm text-slate-500 mt-1">Kemajuan target dan dana daruratmu.</p></div><span class="text-2xl">🪐</span></div><div id="savings-list" class="space-y-6"><div class="text-sm text-slate-400">Data target akan muncul setelah endpoint target terhubung.</div></div></div>
</div>

<div class="card overflow-hidden"><div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4"><div><h2 class="font-extrabold text-lg text-[#102A43]">Insight orbit bulan ini</h2><p class="text-sm text-slate-500 mt-1">Ringkasan berdasarkan data laporan dari server.</p></div><span class="badge bg-blue-50 text-[#1F4E79]">✦ Insight</span></div><div id="report-insights" class="grid md:grid-cols-3 border-t border-slate-100"><div class="p-6 text-sm text-slate-400">Insight akan dibuat dari data laporan.</div></div></div>
@endsection

@push('scripts')
<script>
const reportToken = localStorage.getItem('token');
let trendChart = null;
let categoryChart = null;

if (!reportToken) window.location.href = '/login';

function rupiah(value) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(value || 0));
}

function getData(payload) {
    return payload?.data ?? payload ?? {};
}

async function apiGet(url) {
    const response = await fetch(url, {
        headers: { 'Authorization': `Bearer ${reportToken}`, 'Accept': 'application/json' }
    });
    if (response.status === 401) {
        localStorage.removeItem('token');
        window.location.href = '/login';
        return null;
    }
    if (!response.ok) throw new Error(`API gagal (${response.status})`);
    return response.json();
}

function setLoading(active) {
    document.getElementById('report-loading').classList.toggle('hidden', !active);
}

function setError(message = '') {
    const element = document.getElementById('report-error');
    element.textContent = message;
    element.classList.toggle('hidden', !message);
}

function renderSummary(summary) {
    const income = Number(summary.totalincome ?? summary.totalIncome ?? 0);
    const expense = Number(summary.totalexpense ?? summary.totalExpense ?? 0);
    const balance = Number(summary.balance ?? income - expense);
    const ratio = income > 0 ? Math.max(0, (balance / income) * 100) : 0;

    document.getElementById('total-income').textContent = rupiah(income);
    document.getElementById('total-expense').textContent = rupiah(expense);
    document.getElementById('net-balance').textContent = rupiah(balance);
    document.getElementById('saving-ratio').textContent = `${ratio.toFixed(1)}%`;
}

function renderCategories(payload) {
    const rows = Array.isArray(payload) ? payload : (payload.categories || payload.items || []);
    const labels = rows.map(row => row.category?.name || row.name || row.category_name || 'Lainnya');
    const values = rows.map(row => Number(row.total ?? row.amount ?? row.value ?? 0));
    const total = values.reduce((sum, value) => sum + value, 0);
    const colors = ['#F59E0B', '#3B82F6', '#8B5CF6', '#19B5A5', '#F07C7C', '#1F4E79'];

    document.getElementById('category-total').textContent = `Total ${rupiah(total)}`;
    document.getElementById('category-legend').innerHTML = rows.length
        ? rows.slice(0, 6).map((row, index) => `<div class="flex justify-between gap-3"><span class="text-slate-600"><span style="color:${colors[index % colors.length]}">●</span> ${labels[index]}</span><b>${rupiah(values[index])}</b></div>`).join('')
        : '<div class="text-sm text-slate-400">Belum ada data kategori.</div>';

    if (categoryChart) categoryChart.destroy();
    categoryChart = new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: { labels: labels.length ? labels : ['Belum ada data'], datasets: [{ data: values.length ? values : [1], backgroundColor: labels.length ? colors : ['#E5ECF3'], borderWidth: 0, hoverOffset: 5 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false } } }
    });
}

function renderMonthly(payload) {
    const rows = Array.isArray(payload) ? payload : (payload.months || payload.items || []);
    const labels = rows.map(row => row.month || row.label || '');
    const income = rows.map(row => Number(row.income ?? row.totalincome ?? 0));
    const expense = rows.map(row => Number(row.expense ?? row.totalexpense ?? 0));

    if (trendChart) trendChart.destroy();
    trendChart = new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'line',
        data: { labels: labels.length ? labels : ['Belum ada data'], datasets: [
            { label: 'Pemasukan', data: income.length ? income : [0], borderColor: '#19B5A5', backgroundColor: 'rgba(25,181,165,.14)', fill: true, tension: .35, pointRadius: 4, pointBackgroundColor: '#19B5A5' },
            { label: 'Pengeluaran', data: expense.length ? expense : [0], borderColor: '#F07C7C', backgroundColor: 'transparent', fill: false, tension: .35, pointRadius: 4, pointBackgroundColor: '#F07C7C' }
        ] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 22 } } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#EDF2F7' }, ticks: { callback: value => 'Rp' + (value / 1000000) + 'jt' } } } }
    });
}

function renderInsights(summary, categories) {
    const rows = Array.isArray(categories) ? categories : (categories.categories || categories.items || []);
    const first = rows[0];
    const name = first?.category?.name || first?.name || first?.category_name || 'Kategori utama';
    const total = Number(first?.total ?? first?.amount ?? 0);
    const expense = Number(summary.totalexpense ?? summary.totalExpense ?? 0);
    const percent = expense > 0 ? Math.round(total / expense * 100) : 0;

    document.getElementById('report-insights').innerHTML = `
        <div class="p-6 border-b md:border-b-0 md:border-r border-slate-100"><div class="text-2xl">◉</div><p class="font-bold mt-3">${name} menjadi fokus</p><p class="text-sm text-slate-500 leading-6 mt-2">Kategori terbesar menyumbang sekitar ${percent}% dari pengeluaran bulan ini.</p></div>
        <div class="p-6 border-b md:border-b-0 md:border-r border-slate-100"><div class="text-2xl">✦</div><p class="font-bold mt-3">Laporan tersinkron</p><p class="text-sm text-slate-500 leading-6 mt-2">Angka pada halaman ini berasal dari API laporan, bukan data statis.</p></div>
        <div class="p-6"><div class="text-2xl">🚀</div><p class="font-bold mt-3">Tetap pada orbit</p><p class="text-sm text-slate-500 leading-6 mt-2">Gunakan tren bulanan untuk menyesuaikan alokasi dan target finansial.</p></div>`;
}

function renderAllocationPlaceholder() {
    document.getElementById('allocation-list').innerHTML = `
        <div><div class="flex justify-between text-sm mb-2"><span class="font-semibold">Kebutuhan</span><span class="text-slate-500">Menunggu API alokasi</span></div><div class="h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-[#19B5A5] w-[60%]"></div></div></div>
        <div><div class="flex justify-between text-sm mb-2"><span class="font-semibold">Keinginan</span><span class="text-slate-500">Menunggu API alokasi</span></div><div class="h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-amber-400 w-[45%]"></div></div></div>
        <div><div class="flex justify-between text-sm mb-2"><span class="font-semibold">Tabungan</span><span class="text-slate-500">Menunggu API alokasi</span></div><div class="h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full bg-[#1F4E79] w-[70%]"></div></div></div>`;
}

function renderSavingsPlaceholder() {
    document.getElementById('savings-list').innerHTML = '<div class="rounded-2xl bg-[#F3F8FC] p-5 text-sm text-slate-500">Target tabungan dan dana darurat akan dimuat dari endpoint fase terkait setelah tersedia.</div>';
}

async function loadReportData() {
    const month = document.getElementById('month-select').value;
    setLoading(true);
    setError('');
    try {
        const [summaryResponse, categoriesResponse, monthlyResponse] = await Promise.all([
            apiGet(`/api/v1/summary?month=${encodeURIComponent(month)}`),
            apiGet(`/api/v1/reports/categories?from=${month}-01&to=${month}-31&type=expense`),
            apiGet('/api/v1/reports/monthly?months=6')
        ]);
        if (!summaryResponse || !categoriesResponse || !monthlyResponse) return;
        const summary = getData(summaryResponse);
        const categories = getData(categoriesResponse);
        const monthly = getData(monthlyResponse);
        renderSummary(summary);
        renderCategories(categories);
        renderMonthly(monthly);
        renderInsights(summary, categories);
        renderAllocationPlaceholder();
        renderSavingsPlaceholder();
    } catch (error) {
        console.error(error);
        setError('Data laporan belum dapat dimuat. Pastikan endpoint summary dan reports pada backend sudah aktif.');
    } finally {
        setLoading(false);
    }
}

async function exportTransactions() {
    const month = document.getElementById('month-select').value;
    try {
        const response = await fetch(`/api/v1/reports/export/transactions?from=${month}-01&to=${month}-31&format=csv`, { headers: { 'Authorization': `Bearer ${reportToken}` } });
        if (response.status === 401) { localStorage.removeItem('token'); window.location.href = '/login'; return; }
        if (!response.ok) throw new Error('Gagal mengekspor transaksi');
        const blob = await response.blob();
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a'); link.href = url; link.download = `catatduit-transaksi-${month}.csv`; link.click(); URL.revokeObjectURL(url);
    } catch (error) { alert('Ekspor CSV gagal. Pastikan endpoint export laporan tersedia.'); }
}

loadReportData();
</script>
@endpush