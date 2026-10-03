@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Riwayat Aktivitas ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Daftar Transaksi</h1>
        <p class="text-sm text-slate-500 mt-2">Telusuri seluruh arus kas pengeluaran dan pemasukan dalam orbit finansialmu.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('transaksi.detail') }}?type=income" class="inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-100">
            <span class="text-lg leading-none">+</span> Pemasukan
        </a>
        <a href="{{ route('transaksi.detail') }}?type=expense" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1F4E79] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-900/15 transition hover:bg-[#163859]">
            <span class="text-lg leading-none">+</span> Pengeluaran
        </a>
    </div>
</div>

<div id="transaction-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<!-- KPI Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    <div class="card p-5 border-l-4 border-l-rose-400">
        <p class="text-xs font-semibold text-slate-500">Total Pengeluaran Bulan Ini</p>
        <p id="monthly-expense-total" class="mt-3 text-2xl font-extrabold text-rose-500">Rp0</p>
        <p class="mt-3 text-xs text-slate-400">Akumulasi pengeluaran</p>
    </div>
    <div class="card p-5 border-l-4 border-l-emerald-500">
        <p class="text-xs font-semibold text-slate-500">Total Pemasukan Bulan Ini</p>
        <p id="monthly-income-total" class="mt-3 text-2xl font-extrabold text-emerald-600">Rp0</p>
        <p class="mt-3 text-xs text-slate-400">Akumulasi pemasukan</p>
    </div>
    <div class="card p-5 border-l-4 border-l-[#1F4E79]">
        <p class="text-xs font-semibold text-slate-500">Jumlah Transaksi Ditampilkan</p>
        <p id="transaction-count" class="mt-3 text-2xl font-extrabold text-[#1F4E79]">0</p>
        <p class="mt-3 text-xs text-slate-400">Sesuai filter pencarian</p>
    </div>
    <div class="card p-5 border-l-4 border-l-amber-400">
        <p class="text-xs font-semibold text-slate-500">Status Sinkronisasi</p>
        <p id="sync-status" class="mt-3 text-lg font-extrabold text-amber-600">Memuat...</p>
        <p class="mt-3 text-xs text-slate-400">Koneksi API Backend</p>
    </div>
</div>

<!-- Main Transaction Table Card -->
<div class="card overflow-hidden">
    <div class="border-b border-slate-100 p-5">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
            <div class="relative lg:col-span-4">
                <span class="absolute left-4 top-3 text-slate-400">⌕</span>
                <input id="search-input" type="text" placeholder="Cari item, keterangan, kategori..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15">
            </div>
            <div class="lg:col-span-2">
                <input id="date-filter" type="date" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-[#19B5A5]/15">
            </div>
            <div class="lg:col-span-2">
                <select id="category-filter" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-[#19B5A5]/15">
                    <option value="">Semua Kategori</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <select id="income-filter" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-[#19B5A5]/15">
                    <option value="">Semua Dompet</option>
                </select>
            </div>
            <div class="lg:col-span-2">
                <button type="button" onclick="clearFilters()" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 transition">
                    Reset Filter
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <button id="filter-all" onclick="filterType('all')" class="filter-btn rounded-full bg-[#1F4E79] px-4 py-2 text-xs font-bold text-white transition">Semua</button>
                <button id="filter-expense" onclick="filterType('expense')" class="filter-btn rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">💸 Pengeluaran</button>
                <button id="filter-income" onclick="filterType('income')" class="filter-btn rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">💰 Pemasukan</button>
            </div>
            <button type="button" onclick="openExportModal()" class="rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700 hover:bg-emerald-100 flex items-center gap-1.5 transition">
                <span>📊</span> Ekspor Excel / Laporan
            </button>
        </div>
    </div>

    <div class="table-wrap overflow-x-auto">
        <table class="w-full min-w-[920px] text-sm">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                <tr>
                    <th class="px-5 py-4 text-left">Tanggal</th>
                    <th class="px-5 py-4 text-left">Tipe</th>
                    <th class="px-5 py-4 text-left">Item / Keterangan</th>
                    <th class="px-5 py-4 text-left">Kategori</th>
                    <th class="px-5 py-4 text-left">Dompet</th>
                    <th class="px-5 py-4 text-right">Nominal</th>
                    <th class="px-5 py-4 text-left">Sumber Input</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="transactions-container">
                <tr><td colspan="8" class="p-8 text-center text-sm text-slate-400">Memuat transaksi...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
        <span id="pagination-info">Menampilkan transaksi...</span>
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
const token = localStorage.getItem('token');
let allTransactions = [];
let allCategories = [];
let allIncomes = [];
let currentFilterType = 'all';

if (!token) window.location.href = '/login';

const fmtRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(num || 0));
const escapeHtml = (val) => { const el = document.createElement('div'); el.textContent = val ?? ''; return el.innerHTML; };

function showError(message = '') {
    const box = document.getElementById('transaction-error');
    box.textContent = message;
    box.classList.toggle('hidden', !message);
}

async function authFetch(url, options = {}) {
    const res = await fetch(url, {
        ...options,
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept': 'application/json',
            ...(options.headers || {})
        }
    });
    if (res.status === 401) {
        localStorage.removeItem('token');
        window.location.href = '/login';
        return null;
    }
    return res;
}

function addOptions(selectId, items, labelBuilder) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">' + select.options[0].text + '</option>';
    (items || []).forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = labelBuilder(item);
        select.appendChild(opt);
    });
}

async function loadData() {
    showError('');
    try {
        const [sumRes, expRes, catRes, incRes] = await Promise.all([
            authFetch('/api/v1/summary'),
            authFetch('/api/v1/expenses?limit=100'),
            authFetch('/api/v1/categories'),
            authFetch('/api/v1/incomes')
        ]);

        if (!sumRes || !expRes || !catRes || !incRes) return;

        const sumData = await sumRes.json();
        const expData = await expRes.json();
        const catData = await catRes.json();
        const incData = await incRes.json();

        allCategories = catData.data || [];
        allIncomes = incData.data || [];

        // Build list of expenses
        const expenses = (expData.data || []).map(e => ({
            id: e.id,
            type: 'expense',
            item: e.item,
            amount: e.amount,
            date: e.spent_at ? e.spent_at.slice(0, 10) : '',
            category_id: e.category_id,
            category_name: e.category?.name || 'Umum',
            category_icon: e.category?.icon || '🛒',
            income_id: e.income_id,
            income_name: e.income?.name || e.income_name || 'Dompet',
            raw_input: e.raw_input,
            source: e.source || (e.raw_input ? 'smart_entry' : 'manual')
        }));

        // Fetch receipts for each income
        let receipts = [];
        for (const inc of allIncomes) {
            try {
                const recRes = await authFetch(`/api/v1/incomes/${inc.id}/receipts`);
                if (recRes && recRes.ok) {
                    const recJson = await recRes.json();
                    (recJson.data || []).forEach(r => {
                        const matchedCat = allCategories.find(c => c.id === r.category_id);
                        receipts.push({
                            id: r.id,
                            type: 'income',
                            item: r.note || 'Penerimaan',
                            amount: r.amount,
                            date: r.received_at ? r.received_at.slice(0, 10) : '',
                            category_id: r.category_id,
                            category_name: matchedCat ? matchedCat.name : 'Pemasukan',
                            category_icon: matchedCat ? (matchedCat.icon || '💰') : '💰',
                            income_id: inc.id,
                            income_name: inc.name,
                            raw_input: r.raw_input,
                            source: r.raw_input ? 'smart_entry' : 'manual'
                        });
                    });
                }
            } catch (err) {
                console.error(err);
            }
        }

        // Combine and sort by date descending
        allTransactions = [...expenses, ...receipts].sort((a, b) => new Date(b.date) - new Date(a.date));

        // Update KPIs
        document.getElementById('monthly-expense-total').textContent = fmtRp(sumData.data?.monthly_expense ?? 0);
        document.getElementById('monthly-income-total').textContent = fmtRp(sumData.data?.monthly_income ?? 0);
        document.getElementById('sync-status').textContent = 'Tersinkron';

        addOptions('category-filter', allCategories, c => `${c.icon || '📁'} ${c.name} (${c.type === 'income' ? 'Masuk' : 'Keluar'})`);
        addOptions('income-filter', allIncomes, i => i.name);

        renderList();
    } catch (err) {
        console.error(err);
        document.getElementById('sync-status').textContent = 'Koneksi Terganggu';
        document.getElementById('transactions-container').innerHTML = '<tr><td colspan="8" class="p-8 text-center text-sm text-rose-500">Gagal memuat riwayat transaksi.</td></tr>';
        showError('Gagal memuat transaksi. Silakan segarkan halaman.');
    }
}

function renderList() {
    const query = document.getElementById('search-input').value.toLowerCase().trim();
    const date = document.getElementById('date-filter').value;
    const categoryId = document.getElementById('category-filter').value;
    const incomeId = document.getElementById('income-filter').value;
    const container = document.getElementById('transactions-container');

    const filtered = allTransactions.filter(item => {
        if (currentFilterType !== 'all' && item.type !== currentFilterType) return false;
        
        const itemName = (item.item || '').toLowerCase();
        const catName = (item.category_name || '').toLowerCase();
        const isMatchQuery = !query || itemName.includes(query) || catName.includes(query);
        const isMatchDate = !date || item.date === date;
        const isMatchCategory = !categoryId || String(item.category_id) === String(categoryId);
        const isMatchIncome = !incomeId || String(item.income_id) === String(incomeId);

        return isMatchQuery && isMatchDate && isMatchCategory && isMatchIncome;
    });

    document.getElementById('transaction-count').textContent = filtered.length;
    document.getElementById('pagination-info').textContent = `Menampilkan ${filtered.length} dari ${allTransactions.length} transaksi total`;

    if (!filtered.length) {
        container.innerHTML = '<tr><td colspan="8" class="p-10 text-center text-sm text-slate-400">Tidak ada transaksi yang cocok dengan kriteria filter.</td></tr>';
        return;
    }

    container.innerHTML = filtered.map(item => {
        const isExpense = item.type === 'expense';
        const typeBadge = isExpense 
            ? '<span class="badge bg-rose-50 text-rose-600 border border-rose-100">Pengeluaran</span>' 
            : '<span class="badge bg-emerald-50 text-emerald-600 border border-emerald-100">Pemasukan</span>';
        
        const amountDisplay = isExpense 
            ? `<span class="font-extrabold text-rose-500">-${fmtRp(item.amount)}</span>` 
            : `<span class="font-extrabold text-emerald-600">+${fmtRp(item.amount)}</span>`;

        const sourceLabel = item.raw_input ? '⚡ Smart Entry' : 'Manual';
        const sourceBadge = item.raw_input ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-600';
        const dateFormatted = item.date ? new Date(item.date).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '—';

        const editUrl = isExpense
            ? `/transaksi/detail?type=expense&id=${encodeURIComponent(item.id)}`
            : `/transaksi/detail?type=income&income_id=${encodeURIComponent(item.income_id)}&id=${encodeURIComponent(item.id)}`;

        return `
            <tr class="border-t border-slate-100 transition hover:bg-slate-50/70">
                <td class="px-5 py-4 text-slate-500 whitespace-nowrap">${dateFormatted}</td>
                <td class="px-5 py-4">${typeBadge}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl ${isExpense ? 'bg-orange-50' : 'bg-emerald-50'} text-base">
                            ${escapeHtml(item.category_icon)}
                        </div>
                        <span class="font-bold text-slate-800">${escapeHtml(item.item)}</span>
                    </div>
                </td>
                <td class="px-5 py-4 text-slate-600">${escapeHtml(item.category_name)}</td>
                <td class="px-5 py-4 text-slate-600 font-medium">${escapeHtml(item.income_name)}</td>
                <td class="px-5 py-4 text-right whitespace-nowrap">${amountDisplay}</td>
                <td class="px-5 py-4"><span class="badge ${sourceBadge}">${sourceLabel}</span></td>
                <td class="px-5 py-4 text-right whitespace-nowrap">
                    <a href="${editUrl}" class="mr-3 font-bold text-[#1F4E79] hover:underline">Edit</a>
                    <button type="button" onclick="deleteItem('${item.type}', '${item.id}', '${item.income_id}')" class="font-bold text-rose-500 hover:underline">Hapus</button>
                </td>
            </tr>
        `;
    }).join('');
}

async function deleteItem(type, id, incomeId) {
    if (!confirm('Hapus transaksi ini? Tindakan tidak dapat dibatalkan.')) return;
    try {
        let url = type === 'expense' 
            ? `/api/v1/expenses/${id}` 
            : `/api/v1/incomes/${incomeId}/receipts/${id}`;

        const res = await authFetch(url, { method: 'DELETE' });
        if (!res) return;
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menghapus transaksi.');
        }
        allTransactions = allTransactions.filter(item => !(item.type === type && String(item.id) === String(id)));
        renderList();
    } catch (err) {
        alert(err.message || 'Terjadi kesalahan jaringan.');
    }
}

function filterType(type) {
    currentFilterType = type;
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.className = 'filter-btn rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50 transition';
    });
    document.getElementById(`filter-${type}`).className = 'filter-btn rounded-full bg-[#1F4E79] px-4 py-2 text-xs font-bold text-white transition';
    renderList();
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('date-filter').value = '';
    document.getElementById('category-filter').value = '';
    document.getElementById('income-filter').value = '';
    filterType('all');
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
            headers: { 'Authorization': `Bearer ${token}` }
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

['search-input', 'date-filter', 'category-filter', 'income-filter'].forEach(id => {
    document.getElementById(id).addEventListener(id === 'search-input' ? 'input' : 'change', renderList);
});

loadData();
</script>
@endpush
