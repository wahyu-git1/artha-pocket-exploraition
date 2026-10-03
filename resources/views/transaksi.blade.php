@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Riwayat aktivitas ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Transaksi</h1>
        <p class="text-sm text-slate-500 mt-2">Telusuri dan kelola semua pengeluaran dari sumber dana aktifmu.</p>
    </div>
    <a href="{{ route('transaksi.detail') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1F4E79] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-900/15 transition hover:bg-[#163859]">
        <span class="text-lg leading-none">+</span> Transaksi baru
    </a>
</div>

<div id="transaction-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    <div class="card p-5 border-l-4 border-l-rose-400"><p class="text-xs font-semibold text-slate-500">Total pengeluaran bulan ini</p><p id="monthly-expense-total" class="mt-3 text-2xl font-extrabold text-rose-500">Rp0</p><p class="mt-3 text-xs text-slate-400">Dari ringkasan finansial</p></div>
    <div class="card p-5 border-l-4 border-l-[#19B5A5]"><p class="text-xs font-semibold text-slate-500">Jumlah transaksi</p><p id="transaction-count" class="mt-3 text-2xl font-extrabold text-[#148B80]">0</p><p class="mt-3 text-xs text-slate-400">Pada hasil filter saat ini</p></div>
    <div class="card p-5 border-l-4 border-l-[#1F4E79]"><p class="text-xs font-semibold text-slate-500">Rata-rata pengeluaran</p><p id="average-expense" class="mt-3 text-2xl font-extrabold text-[#1F4E79]">Rp0</p><p class="mt-3 text-xs text-slate-400">Per transaksi yang tampil</p></div>
    <div class="card p-5 border-l-4 border-l-amber-400"><p class="text-xs font-semibold text-slate-500">Status sinkronisasi</p><p id="sync-status" class="mt-3 text-lg font-extrabold text-amber-600">Memuat...</p><p class="mt-3 text-xs text-slate-400">Terhubung ke API CatatDuit</p></div>
</div>

<div class="card overflow-hidden">
    <div class="border-b border-slate-100 p-5">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
            <div class="relative lg:col-span-4"><span class="absolute left-4 top-3 text-slate-400">⌕</span><input id="search-input" type="text" placeholder="Cari item atau kategori..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15"></div>
            <div class="lg:col-span-2"><input id="date-filter" type="date" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-[#19B5A5]/15"></div>
            <div class="lg:col-span-2"><select id="category-filter" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-[#19B5A5]/15"><option value="">Semua kategori</option></select></div>
            <div class="lg:col-span-2"><select id="income-filter" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 outline-none focus:ring-2 focus:ring-[#19B5A5]/15"><option value="">Semua sumber dana</option></select></div>
            <div class="lg:col-span-2"><button type="button" onclick="clearFilters()" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50">Reset filter</button></div>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <button id="filter-all" onclick="filterType('all')" class="filter-btn rounded-full bg-[#1F4E79] px-4 py-2 text-xs font-bold text-white">Semua</button>
                <button id="filter-expense" onclick="filterType('expense')" class="filter-btn rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Pengeluaran</button>
                <button id="filter-income" onclick="filterType('income')" class="filter-btn rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50">Pemasukan</button>
            </div>
            <button type="button" onclick="exportTransactions()" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">⇩ Ekspor CSV</button>
        </div>
    </div>

    <div class="table-wrap overflow-x-auto">
        <table class="w-full min-w-[920px] text-sm">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500"><tr><th class="px-5 py-4 text-left">Tanggal</th><th class="px-5 py-4 text-left">Item</th><th class="px-5 py-4 text-left">Kategori</th><th class="px-5 py-4 text-left">Sumber dana</th><th class="px-5 py-4 text-right">Nominal</th><th class="px-5 py-4 text-left">Sumber input</th><th class="px-5 py-4 text-right">Aksi</th></tr></thead>
            <tbody id="transactions-container"><tr><td colspan="7" class="p-8 text-center text-sm text-slate-400">Memuat data transaksi...</td></tr></tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between"><span id="pagination-info">Memuat transaksi...</span><div class="flex items-center gap-2"><button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-slate-400" disabled>‹</button><button type="button" class="rounded-lg bg-[#1F4E79] px-3 py-2 font-bold text-white">1</button><button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-slate-400" disabled>›</button></div></div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
let allExpenses = [];
let allCategories = [];
let allIncomes = [];
let currentFilter = 'all';

if (!token) window.location.href = '/login';

const fmtRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(num || 0));
const escapeHtml = (value) => { const element = document.createElement('div'); element.textContent = value ?? ''; return element.innerHTML; };

function showError(message = '') {
    const box = document.getElementById('transaction-error');
    box.textContent = message;
    box.classList.toggle('hidden', !message);
}

async function authorizedFetch(url, options = {}) {
    const response = await fetch(url, { ...options, headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json', ...(options.headers || {}) } });
    if (response.status === 401) { localStorage.removeItem('token'); window.location.href = '/login'; return null; }
    return response;
}

function addOptions(selectId, items, labelBuilder) {
    const select = document.getElementById(selectId);
    (items || []).forEach(item => { const option = document.createElement('option'); option.value = item.id; option.textContent = labelBuilder(item); select.appendChild(option); });
}

async function loadData() {
    showError('');
    try {
        const [summaryResponse, expenseResponse, categoryResponse, incomeResponse] = await Promise.all([
            authorizedFetch('/api/v1/summary'),
            authorizedFetch('/api/v1/expenses'),
            authorizedFetch('/api/v1/categories?filter[type]=expense'),
            authorizedFetch('/api/v1/incomes')
        ]);
        if (!summaryResponse || !expenseResponse || !categoryResponse || !incomeResponse) return;
        if (!expenseResponse.ok) throw new Error('Gagal memuat transaksi dari server.');

        const [summaryData, expenseData, categoryData, incomeData] = await Promise.all([
            summaryResponse.ok ? summaryResponse.json() : { data: {} },
            expenseResponse.json(), categoryResponse.ok ? categoryResponse.json() : { data: [] }, incomeResponse.ok ? incomeResponse.json() : { data: [] }
        ]);

        allExpenses = expenseData.data || [];
        allCategories = categoryData.data || [];
        allIncomes = incomeData.data || [];
        document.getElementById('monthly-expense-total').textContent = fmtRp(summaryData.data?.monthly_expense ?? summaryData.data?.totalexpense ?? 0);
        document.getElementById('sync-status').textContent = 'Tersinkron';
        addOptions('category-filter', allCategories, category => `${category.icon || '📁'} ${category.name}`);
        addOptions('income-filter', allIncomes, income => income.name);
        renderList();
    } catch (error) {
        console.error(error);
        document.getElementById('sync-status').textContent = 'Gagal memuat';
        document.getElementById('transactions-container').innerHTML = '<tr><td colspan="7" class="p-8 text-center text-sm text-rose-500">Gagal memuat transaksi.</td></tr>';
        showError('Data transaksi belum dapat dimuat. Pastikan API backend sedang aktif.');
    }
}

function renderList() {
    const query = document.getElementById('search-input').value.toLowerCase().trim();
    const date = document.getElementById('date-filter').value;
    const categoryId = document.getElementById('category-filter').value;
    const incomeId = document.getElementById('income-filter').value;
    const container = document.getElementById('transactions-container');

    const filtered = allExpenses.filter(item => {
        const itemName = (item.item || '').toLowerCase();
        const categoryName = (item.category?.name || '').toLowerCase();
        const isMatchQuery = itemName.includes(query) || categoryName.includes(query);
        const itemDate = (item.spent_at || '').slice(0, 10);
        const isMatchDate = !date || itemDate === date;
        const isMatchCategory = !categoryId || String(item.category_id) === String(categoryId);
        const isMatchIncome = !incomeId || String(item.income_id) === String(incomeId);
        return isMatchQuery && isMatchDate && isMatchCategory && isMatchIncome;
    });

    document.getElementById('transaction-count').textContent = filtered.length;
    const total = filtered.reduce((sum, item) => sum + Number(item.amount || 0), 0);
    document.getElementById('average-expense').textContent = fmtRp(filtered.length ? total / filtered.length : 0);
    document.getElementById('pagination-info').textContent = `Menampilkan ${filtered.length} dari ${allExpenses.length} transaksi`;

    if (!filtered.length) {
        container.innerHTML = '<tr><td colspan="7" class="p-10 text-center text-sm text-slate-400">Tidak ada transaksi yang sesuai dengan filter.</td></tr>';
        return;
    }

    container.innerHTML = filtered.map(item => {
        const source = item.raw_input ? 'Smart Entry' : 'Manual';
        const inputBadge = item.raw_input ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-600';
        const category = item.category?.name || 'Umum';
        const categoryIcon = item.category?.icon || '🛒';
        const income = item.income?.name || item.income_name || '—';
        const dateLabel = item.spent_at ? new Date(item.spent_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '—';
        return `<tr class="border-t border-slate-100 transition hover:bg-slate-50"><td class="px-5 py-4 text-slate-500">${dateLabel}</td><td class="px-5 py-4"><div class="flex items-center gap-3"><div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-lg">${escapeHtml(categoryIcon)}</div><span class="font-extrabold text-slate-800">${escapeHtml(item.item)}</span></div></td><td class="px-5 py-4 text-slate-600">${escapeHtml(category)}</td><td class="px-5 py-4 text-slate-600">${escapeHtml(income)}</td><td class="px-5 py-4 text-right font-extrabold text-rose-500">-${fmtRp(item.amount)}</td><td class="px-5 py-4"><span class="badge ${inputBadge}">${source}</span></td><td class="px-5 py-4 text-right whitespace-nowrap"><a href="/transaksi/detail?id=${encodeURIComponent(item.id)}" class="mr-3 font-bold text-[#1F4E79] hover:underline">Edit</a><button type="button" onclick="deleteExpense(${Number(item.id)})" class="font-bold text-rose-500 hover:underline">Hapus</button></td></tr>`;
    }).join('');
}

async function deleteExpense(id) {
    if (!confirm('Hapus pengeluaran ini? Tindakan ini tidak dapat dibatalkan.')) return;
    try {
        const response = await authorizedFetch(`/api/v1/expenses/${id}`, { method: 'DELETE' });
        if (!response) return;
        if (!response.ok) { const error = await response.json().catch(() => ({})); throw new Error(error.message || 'Gagal menghapus transaksi.'); }
        allExpenses = allExpenses.filter(item => String(item.id) !== String(id));
        renderList();
        loadData();
    } catch (error) { alert(error.message || 'Terjadi kesalahan jaringan.'); }
}

function filterType(type) {
    currentFilter = type;
    document.querySelectorAll('.filter-btn').forEach(button => button.className = 'filter-btn rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50');
    document.getElementById(`filter-${type}`).className = 'filter-btn rounded-full bg-[#1F4E79] px-4 py-2 text-xs font-bold text-white';
    renderList();
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('date-filter').value = '';
    document.getElementById('category-filter').value = '';
    document.getElementById('income-filter').value = '';
    filterType('all');
}

function exportTransactions() {
    alert('Ekspor CSV akan tersedia melalui endpoint laporan setelah integrasi final selesai.');
}

['search-input', 'date-filter', 'category-filter', 'income-filter'].forEach(id => document.getElementById(id).addEventListener(id === 'search-input' ? 'input' : 'change', renderList));
loadData();
</script>
@endpush
