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
        <button type="button" onclick="openExportModal()" class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 hover:bg-emerald-100 flex items-center gap-2 transition shadow-sm">
            <span>📊</span> Ekspor Excel / Laporan
        </button>
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

<!-- SISTEM KEPUTUSAN FINANSIAL & PROYEKSI MASA DEPAN AI -->
<div class="card p-6 mb-8 border-t-4 border-t-[#19B5A5]">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-5 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xl">⚡</span>
                <h2 class="font-extrabold text-xl text-[#102A43]">Analisis Keputusan Finansial & Proyeksi Masa Depan</h2>
            </div>
            <p class="text-xs text-slate-500 mt-1">Kesimpulan cerdas dari pengeluaranmu beserta dampaknya ke Target, Dana Darurat, Alokasi, dan Investasi.</p>
        </div>
        <button type="button" onclick="fetchAiDecisionAnalysis()" id="btn-trigger-ai" class="inline-flex items-center gap-2 rounded-xl bg-[#1F4E79] hover:bg-[#163859] px-4 py-2.5 text-xs font-bold text-white transition shadow-md shadow-blue-900/15 whitespace-nowrap">
            <span>✦</span> Analisis Ulang AI
        </button>
    </div>

    <!-- AI Loading State -->
    <div id="ai-loading" class="hidden py-12 text-center">
        <div class="inline-block animate-spin text-3xl mb-3">✦</div>
        <p class="text-sm font-bold text-[#102A43]">AI Financial Advisor sedang menganalisis orbit keuanganmu...</p>
        <p class="text-xs text-slate-400 mt-1">Mengevaluasi pengeluaran terhadap 4 pilar masa depanmu</p>
    </div>

    <!-- AI Results Container -->
    <div id="ai-results" class="mt-6 space-y-6">
        <!-- Skor Kesehatan & Kesimpulan Utama -->
        <div class="rounded-2xl bg-[#F4F8FC] border border-slate-200 p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col items-center justify-center shrink-0">
                    <span id="ai-health-score" class="text-2xl font-black text-[#1F4E79]">—</span>
                    <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Skor / 100</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Status Kesehatan Finansial:</span>
                        <span id="ai-health-status" class="badge bg-teal-100 text-[#148B80] font-extrabold text-xs">Memuat...</span>
                    </div>
                    <p id="ai-primary-conclusion" class="text-sm font-medium text-slate-700 mt-1.5 leading-relaxed">
                        Menganalisis keputusan pengeluaran periode ini...
                    </p>
                </div>
            </div>
        </div>

        <!-- 4 Pilar Dampak Masa Depan -->
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Dampak Nyata Terhadap 4 Pilar Finansialmu:</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- 1. Alokasi Anggaran -->
                <div class="card p-5 border-l-4 border-l-blue-500 bg-white">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🥖</span>
                            <h4 id="pillar-alloc-title" class="font-extrabold text-sm text-[#102A43]">Alokasi Anggaran</h4>
                        </div>
                        <span id="pillar-alloc-badge" class="badge bg-slate-100 text-slate-600">Alokasi</span>
                    </div>
                    <p id="pillar-alloc-exp" class="text-xs text-slate-600 mt-2.5 leading-relaxed">Menganalisis alokasi...</p>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                        <span class="font-bold text-slate-700">Dampak ke depan:</span>
                        <span id="pillar-alloc-impact" class="text-slate-600 ml-1">...</span>
                    </div>
                </div>

                <!-- 2. Dana Darurat -->
                <div class="card p-5 border-l-4 border-l-emerald-500 bg-white">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🛡️</span>
                            <h4 id="pillar-ef-title" class="font-extrabold text-sm text-[#102A43]">Dana Darurat</h4>
                        </div>
                        <span id="pillar-ef-badge" class="badge bg-slate-100 text-slate-600">Ketahanan</span>
                    </div>
                    <p id="pillar-ef-exp" class="text-xs text-slate-600 mt-2.5 leading-relaxed">Menganalisis cadangan darurat...</p>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                        <span class="font-bold text-slate-700">Dampak ke depan:</span>
                        <span id="pillar-ef-impact" class="text-slate-600 ml-1">...</span>
                    </div>
                </div>

                <!-- 3. Target Tabungan -->
                <div class="card p-5 border-l-4 border-l-amber-500 bg-white">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🎯</span>
                            <h4 id="pillar-goals-title" class="font-extrabold text-sm text-[#102A43]">Target Tabungan</h4>
                        </div>
                        <span id="pillar-goals-badge" class="badge bg-slate-100 text-slate-600">Target Impian</span>
                    </div>
                    <p id="pillar-goals-exp" class="text-xs text-slate-600 mt-2.5 leading-relaxed">Menganalisis pencapaian target...</p>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                        <span class="font-bold text-slate-700">Dampak ke depan:</span>
                        <span id="pillar-goals-impact" class="text-slate-600 ml-1">...</span>
                    </div>
                </div>

                <!-- 4. Investasi -->
                <div class="card p-5 border-l-4 border-l-purple-500 bg-white">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">📈</span>
                            <h4 id="pillar-inv-title" class="font-extrabold text-sm text-[#102A43]">Pertumbuhan Investasi</h4>
                        </div>
                        <span id="pillar-inv-badge" class="badge bg-slate-100 text-slate-600">Masa Depan</span>
                    </div>
                    <p id="pillar-inv-exp" class="text-xs text-slate-600 mt-2.5 leading-relaxed">Menganalisis peluang portofolio...</p>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                        <span class="font-bold text-slate-700">Dampak ke depan:</span>
                        <span id="pillar-inv-impact" class="text-slate-600 ml-1">...</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Langkah Keputusan Aksi (Action Plan) -->
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3 flex items-center gap-2">
                <span>📋</span> Rekomendasi Keputusan Aksi Nyata (Action Plan):
            </h3>
            <div id="ai-action-plan" class="space-y-2.5">
                <div class="text-xs text-slate-400">Memuat rencana aksi...</div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EKSPOR LAPORAN EXCEL / CSV DENGAN PILIHAN RENTANG TANGGAL -->
<div id="export-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-lg text-[#102A43] flex items-center gap-2">
                    <span>📊</span> Ekspor Laporan Finansial
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Pilih rentang tanggal dan format dokumen yang diinginkan</p>
            </div>
            <button onclick="closeExportModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="export-form" onsubmit="submitExport(event)" class="space-y-4">
            <!-- Preset Cepat -->
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-2">Preset Rentang Cepat:</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <button type="button" onclick="setExportPreset('this_month')" class="py-2 px-3 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 transition text-center">Bulan Ini</button>
                    <button type="button" onclick="setExportPreset('last_month')" class="py-2 px-3 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 transition text-center">Bulan Lalu</button>
                    <button type="button" onclick="setExportPreset('last_3_months')" class="py-2 px-3 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 transition text-center">3 Bulan Terakhir</button>
                    <button type="button" onclick="setExportPreset('this_year')" class="py-2 px-3 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-slate-700 transition text-center">Tahun Ini</button>
                </div>
            </div>

            <!-- Rentang Tanggal Manual -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dari Tanggal (Mulai)</label>
                    <input id="export-from" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none focus:bg-white focus:border-[#19B5A5] focus:ring-2 focus:ring-[#19B5A5]/15">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Tanggal (Selesai)</label>
                    <input id="export-to" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none focus:bg-white focus:border-[#19B5A5] focus:ring-2 focus:ring-[#19B5A5]/15">
                </div>
            </div>

            <!-- Pilihan Format File -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Format Dokumen</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-emerald-500 cursor-pointer bg-slate-50 hover:bg-emerald-50/40 transition">
                        <input type="radio" name="export-format" value="excel" checked class="text-emerald-600 focus:ring-0">
                        <div>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <span>📊</span> Microsoft Excel
                            </div>
                            <div class="text-[10px] text-slate-400">Format .xls dengan ringkasan & warna</div>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-[#1F4E79] cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
                        <input type="radio" name="export-format" value="csv" class="text-[#1F4E79] focus:ring-0">
                        <div>
                            <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                <span>📄</span> File CSV
                            </div>
                            <div class="text-[10px] text-slate-400">Format tabel murni UTF-8 BOM</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Pilihan Cakupan Transaksi -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Cakupan Transaksi</label>
                <select id="export-type" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs font-semibold outline-none focus:bg-white focus:border-[#19B5A5]">
                    <option value="all" selected>Semua Transaksi (Pemasukan & Pengeluaran)</option>
                    <option value="expense">Hanya Pengeluaran</option>
                    <option value="income">Hanya Pemasukan</option>
                </select>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closeExportModal()" class="flex-1 py-3 rounded-xl font-bold text-xs text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-submit-export" class="flex-1 py-3 rounded-xl font-bold text-xs text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-md shadow-emerald-700/15 flex items-center justify-center gap-2">
                    <span>⬇</span> Unduh Dokumen
                </button>
            </div>
        </form>
    </div>
</div>
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

async function fetchAiDecisionAnalysis() {
    const month = document.getElementById('month-select').value;
    const from = `${month}-01`;
    const to = `${month}-31`;

    const loadingEl = document.getElementById('ai-loading');
    const resultsEl = document.getElementById('ai-results');
    const btn = document.getElementById('btn-trigger-ai');

    if (loadingEl) loadingEl.classList.remove('hidden');
    if (resultsEl) resultsEl.classList.add('hidden');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>⏳</span> Menganalisis...';
    }

    try {
        const res = await apiGet(`/api/v1/reports/ai-insights?from=${from}&to=${to}`);
        if (!res) return;
        const json = getData(res);
        const analysis = json.decision_analysis || json;

        // 1. Health Score & Status
        const score = analysis.health_score ?? 70;
        document.getElementById('ai-health-score').textContent = score;
        const statusEl = document.getElementById('ai-health-status');
        statusEl.textContent = analysis.health_status || 'Stabil';
        if (score >= 75) {
            statusEl.className = 'badge bg-emerald-100 text-emerald-800 font-extrabold text-xs';
        } else if (score >= 50) {
            statusEl.className = 'badge bg-amber-100 text-amber-800 font-extrabold text-xs';
        } else {
            statusEl.className = 'badge bg-rose-100 text-rose-800 font-extrabold text-xs';
        }

        // 2. Primary Conclusion
        document.getElementById('ai-primary-conclusion').textContent = analysis.primary_conclusion || 'Pola pengeluaran dan pemasukan Anda terpantau seimbang.';

        // 3. 4 Pillars
        const p = analysis.pillars || {};

        // Pillar: Allocation
        if (p.allocation) {
            document.getElementById('pillar-alloc-title').textContent = p.allocation.headline || 'Alokasi Pos';
            document.getElementById('pillar-alloc-exp').textContent = p.allocation.explanation || 'Distribusi belanja kebutuhan dan keinginan.';
            document.getElementById('pillar-alloc-impact').textContent = p.allocation.future_impact || 'Menjaga kestabilan anggaran.';
            const badge = document.getElementById('pillar-alloc-badge');
            badge.textContent = p.allocation.status === 'warning' ? 'Perlu Waspada' : (p.allocation.status === 'danger' ? 'Bocor' : 'Sesuai Target');
            badge.className = p.allocation.status === 'good' ? 'badge bg-emerald-100 text-emerald-700' : 'badge bg-amber-100 text-amber-800';
        }

        // Pillar: Emergency Fund
        if (p.emergency_fund) {
            document.getElementById('pillar-ef-title').textContent = p.emergency_fund.headline || 'Dana Darurat';
            document.getElementById('pillar-ef-exp').textContent = p.emergency_fund.explanation || 'Cadangan proteksi biaya hidup.';
            document.getElementById('pillar-ef-impact').textContent = p.emergency_fund.future_impact || 'Ketahanan saat kondisi darurat.';
            const badge = document.getElementById('pillar-ef-badge');
            badge.textContent = p.emergency_fund.status === 'good' ? 'Aman' : (p.emergency_fund.status === 'warning' ? 'Waspada' : 'Rentan');
            badge.className = p.emergency_fund.status === 'good' ? 'badge bg-emerald-100 text-emerald-700' : (p.emergency_fund.status === 'warning' ? 'badge bg-amber-100 text-amber-800' : 'badge bg-rose-100 text-rose-800');
        }

        // Pillar: Savings Goals
        if (p.savings_goals) {
            document.getElementById('pillar-goals-title').textContent = p.savings_goals.headline || 'Target Tabungan';
            document.getElementById('pillar-goals-exp').textContent = p.savings_goals.explanation || 'Kelayakan setoran bulanan target impian.';
            document.getElementById('pillar-goals-impact').textContent = p.savings_goals.future_impact || 'Dampak pada jadwal pencapaian target.';
            const badge = document.getElementById('pillar-goals-badge');
            badge.textContent = p.savings_goals.status === 'good' ? 'On Track' : 'Berisiko Tertunda';
            badge.className = p.savings_goals.status === 'good' ? 'badge bg-blue-100 text-blue-800' : 'badge bg-amber-100 text-amber-800';
        }

        // Pillar: Investment
        if (p.investment) {
            document.getElementById('pillar-inv-title').textContent = p.investment.headline || 'Peluang Investasi';
            document.getElementById('pillar-inv-exp').textContent = p.investment.explanation || 'Akumulasi pertumbuhan aset jangka panjang.';
            document.getElementById('pillar-inv-impact').textContent = p.investment.future_impact || 'Proyeksi nilai portofolio 3 tahun.';
            const badge = document.getElementById('pillar-inv-badge');
            badge.textContent = 'Proyeksi';
            badge.className = 'badge bg-purple-100 text-purple-800';
        }

        // 4. Action Plan
        const planList = document.getElementById('ai-action-plan');
        const actions = analysis.action_plan || [];
        if (actions.length === 0) {
            planList.innerHTML = '<div class="text-xs text-slate-400">Pertahankan kebiasaan finansial positif Anda.</div>';
        } else {
            planList.innerHTML = actions.map((act, idx) => {
                const priorityBadge = act.priority === 'Tinggi' 
                    ? '<span class="badge bg-rose-100 text-rose-700 text-[10px]">Prioritas Tinggi</span>'
                    : '<span class="badge bg-blue-100 text-blue-700 text-[10px]">Disarankan</span>';
                
                let targetUrl = '/transaksi';
                let targetBtn = 'Lihat Transaksi';
                if (act.target_pillar === 'emergency_fund') { targetUrl = '/dana-darurat'; targetBtn = 'Buka Dana Darurat'; }
                else if (act.target_pillar === 'savings_goals') { targetUrl = '/target'; targetBtn = 'Buka Target'; }
                else if (act.target_pillar === 'allocation') { targetUrl = '/alokasi'; targetBtn = 'Buka Alokasi'; }
                else if (act.target_pillar === 'investment') { targetUrl = '/investasi'; targetBtn = 'Buka Investasi'; }

                return `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-white rounded-xl border border-slate-200">
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-600 font-extrabold text-[11px] flex items-center justify-center shrink-0 mt-0.5">${idx + 1}</span>
                            <div>
                                <div class="text-xs font-bold text-slate-800">${act.action}</div>
                                <div class="mt-1">${priorityBadge}</div>
                            </div>
                        </div>
                        <a href="${targetUrl}" class="text-xs font-bold text-[#1F4E79] hover:underline whitespace-nowrap self-end sm:self-center">
                            ${targetBtn} →
                        </a>
                    </div>
                `;
            }).join('');
        }

    } catch (e) {
        console.error('AI Decision error:', e);
        document.getElementById('ai-primary-conclusion').textContent = 'Gagal memuat analisis cerdas AI. Silakan coba kembali.';
    } finally {
        if (loadingEl) loadingEl.classList.add('hidden');
        if (resultsEl) resultsEl.classList.remove('hidden');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<span>✦</span> Analisis Ulang AI';
        }
    }
}

function renderAllocations(allocData) {
    const list = document.getElementById('allocation-list');
    if (!allocData || !allocData.realized) {
        list.innerHTML = '<div class="text-sm text-slate-400">Belum ada data alokasi untuk bulan ini.</div>';
        return;
    }

    const realized = allocData.realized;
    const plan = allocData.plan;
    const buckets = [
        { key: 'need', name: 'Kebutuhan', color: '#19B5A5' },
        { key: 'want', name: 'Keinginan', color: '#F59E0B' },
        { key: 'saving', name: 'Tabungan', color: '#1F4E79' },
        { key: 'investment', name: 'Investasi', color: '#8B5CF6' }
    ];

    const totalSpent = Object.values(realized).reduce((a, b) => Number(a) + Number(b), 0) || 1;

    list.innerHTML = buckets.map(b => {
        const spent = Number(realized[b.key] || 0);
        const pct = Math.min(100, Math.round((spent / totalSpent) * 100));
        return `
            <div>
                <div class="flex justify-between text-sm mb-2">
                    <span class="font-semibold">${b.name}</span>
                    <span class="text-slate-600 font-medium">${rupiah(spent)} (${pct}%)</span>
                </div>
                <div class="h-3 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-3 rounded-full" style="width: ${pct}%; background-color: ${b.color};"></div>
                </div>
            </div>
        `;
    }).join('');
}

function renderSavings(goalsData, fundData) {
    const list = document.getElementById('savings-list');
    const goals = Array.isArray(goalsData) ? goalsData : (goalsData?.data || []);
    const fund = fundData?.data || fundData;

    let html = '';

    if (fund && fund.target_amount) {
        const current = Number(fund.current_amount || 0);
        const target = Number(fund.target_amount || 1);
        const pct = Math.min(100, Math.round((current / target) * 100));
        html += `
            <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 mb-3">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-sm text-[#102A43]">🛡️ Dana Darurat</span>
                    <span class="text-xs font-bold text-[#19B5A5]">${pct}%</span>
                </div>
                <p class="text-xs text-slate-500 mb-2">${rupiah(current)} dari target ${rupiah(target)}</p>
                <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-2 rounded-full bg-[#19B5A5]" style="width: ${pct}%"></div>
                </div>
            </div>
        `;
    }

    if (goals.length > 0) {
        goals.slice(0, 3).forEach(g => {
            const current = Number(g.current_amount || 0);
            const target = Number(g.target_amount || 1);
            const pct = Math.min(100, Math.round((current / target) * 100));
            html += `
                <div class="p-4 rounded-xl border border-slate-100 bg-white mb-2 shadow-sm">
                    <div class="flex justify-between items-center mb-1">
                        <span class="font-bold text-sm text-[#102A43]">🎯 ${g.name}</span>
                        <span class="text-xs font-bold text-[#1F4E79]">${pct}%</span>
                    </div>
                    <p class="text-xs text-slate-500 mb-2">${rupiah(current)} dari target ${rupiah(target)}</p>
                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-2 rounded-full bg-[#1F4E79]" style="width: ${pct}%"></div>
                    </div>
                </div>
            `;
        });
    }

    list.innerHTML = html || '<div class="rounded-2xl bg-[#F3F8FC] p-5 text-sm text-slate-500">Belum ada target tabungan atau dana darurat aktif.</div>';
}

async function loadReportData() {
    const month = document.getElementById('month-select').value;
    setLoading(true);
    setError('');
    try {
        const [summaryResponse, categoriesResponse, monthlyResponse, allocResponse, goalsResponse, fundResponse] = await Promise.all([
            apiGet(`/api/v1/summary?month=${encodeURIComponent(month)}`),
            apiGet(`/api/v1/reports/categories?from=${month}-01&to=${month}-31&type=expense`),
            apiGet('/api/v1/reports/monthly?months=6'),
            apiGet(`/api/v1/allocations/summary?month=${encodeURIComponent(month)}`).catch(() => null),
            apiGet('/api/v1/savings-goals').catch(() => null),
            apiGet('/api/v1/emergency-fund').catch(() => null)
        ]);

        if (!summaryResponse || !categoriesResponse || !monthlyResponse) return;
        const summary = getData(summaryResponse);
        const categories = getData(categoriesResponse);
        const monthly = getData(monthlyResponse);

        renderSummary(summary);
        renderCategories(categories);
        renderMonthly(monthly);
        fetchAiDecisionAnalysis();
        renderAllocations(getData(allocResponse));
        renderSavings(getData(goalsResponse), getData(fundResponse));
    } catch (error) {
        console.error(error);
        setError('Data laporan belum dapat dimuat. Pastikan endpoint summary dan reports pada backend sudah aktif.');
    } finally {
        setLoading(false);
    }
}

// Export Modal Handlers & Logic
function openExportModal() {
    setExportPreset('this_month');
    document.getElementById('export-modal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('export-modal').classList.add('hidden');
}

function setExportPreset(preset) {
    const now = new Date();
    let from, to;

    if (preset === 'this_month') {
        const y = now.getFullYear();
        const m = now.getMonth();
        from = new Date(y, m, 1);
        to = new Date(y, m + 1, 0);
    } else if (preset === 'last_month') {
        const y = now.getFullYear();
        const m = now.getMonth() - 1;
        from = new Date(y, m, 1);
        to = new Date(y, m + 1, 0);
    } else if (preset === 'last_3_months') {
        const y = now.getFullYear();
        const m = now.getMonth();
        from = new Date(y, m - 2, 1);
        to = new Date(y, m + 1, 0);
    } else if (preset === 'this_year') {
        const y = now.getFullYear();
        from = new Date(y, 0, 1);
        to = new Date(y, 11, 31);
    }

    const fmt = d => d.toISOString().slice(0, 10);
    document.getElementById('export-from').value = fmt(from);
    document.getElementById('export-to').value = fmt(to);
}

async function submitExport(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-export');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span>⏳</span> Menyiapkan Berkas...';
    btn.disabled = true;

    const from = document.getElementById('export-from').value;
    const to = document.getElementById('export-to').value;
    const type = document.getElementById('export-type').value;
    const format = document.querySelector('input[name="export-format"]:checked').value;

    try {
        const url = `/api/v1/exports/transactions?from=${from}&to=${to}&type=${type}&format=${format}`;
        const response = await fetch(url, {
            headers: { 'Authorization': `Bearer ${reportToken}` }
        });

        if (response.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '/login';
            return;
        }

        if (!response.ok) {
            throw new Error('Gagal mengekspor laporan.');
        }

        const blob = await response.blob();
        const ext = format === 'excel' ? 'xls' : 'csv';
        const downloadUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `Laporan_Finansial_${from}_sd_${to}.${ext}`;
        link.click();
        URL.revokeObjectURL(downloadUrl);

        closeExportModal();
    } catch (err) {
        alert(err.message || 'Terjadi kesalahan saat mengunduh laporan.');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function exportTransactions() {
    openExportModal();
}

loadReportData();
</script>
@endpush