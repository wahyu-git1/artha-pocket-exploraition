@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl">
    <a href="{{ route('transaksi') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-[#1F4E79]">
        <span class="text-lg">←</span> Kembali ke transaksi
    </a>

    <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="text-sm text-slate-500">Pencatatan Keuangan ✦</div>
            <h1 id="page-title" class="mt-1 text-3xl font-extrabold text-[#102A43]">Detail Transaksi</h1>
            <p id="page-subtitle" class="mt-2 text-sm text-slate-500">Kelola informasi nominal, kategori, dan dompet secara akurat.</p>
        </div>
        <span id="transaction-status" class="hidden rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500">Memuat data...</span>
    </div>

    <!-- Toggle Tipe Transaksi (Hanya jika mode buat baru) -->
    <div id="type-switcher" class="mt-6 flex max-w-sm rounded-xl bg-slate-200/70 p-1">
        <button type="button" id="btn-tab-expense" onclick="switchType('expense')" class="flex-1 rounded-lg py-2.5 text-xs font-bold transition shadow-sm bg-white text-rose-600">
            💸 Pengeluaran
        </button>
        <button type="button" id="btn-tab-income" onclick="switchType('income')" class="flex-1 rounded-lg py-2.5 text-xs font-bold transition text-slate-600 hover:text-slate-900">
            💰 Pemasukan
        </button>
    </div>

    <div id="page-error" class="hidden mt-6 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

    <form id="transaksi-form" class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf

        <section class="card p-6 lg:col-span-2">
            <div id="smart-entry-box" class="hidden mb-6 rounded-2xl border border-blue-100 bg-blue-50 p-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-lg">⚡</div>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[.14em] text-blue-800">Dicatat via Smart Entry AI</p>
                        <p id="smart-entry-raw" class="mt-2 text-sm italic leading-6 text-slate-600">“...”</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                <div>
                    <h2 class="text-lg font-extrabold text-[#102A43]">Informasi Transaksi</h2>
                    <p class="mt-1 text-sm text-slate-500">Lengkapi data transaksi di bawah ini.</p>
                </div>
                <span id="badge-type-indicator" class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600">Pengeluaran</span>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label id="label-item" for="item-name" class="mb-2 block text-sm font-bold text-slate-700">Item / Keterangan</label>
                    <input id="item-name" type="text" required placeholder="Mis. Makan Siang, Gaji Bulanan, dsb" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15">
                </div>

                <div>
                    <label for="item-amount" class="mb-2 block text-sm font-bold text-slate-700">Nominal</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-sm font-bold text-slate-500">Rp</span>
                        <input id="item-amount" type="number" min="1" required placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm font-extrabold text-rose-500 outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15">
                    </div>
                </div>

                <div>
                    <label for="item-date" class="mb-2 block text-sm font-bold text-slate-700">Tanggal</label>
                    <input id="item-date" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15">
                </div>

                <div>
                    <label for="item-category" class="mb-2 block text-sm font-bold text-slate-700">Kategori</label>
                    <select id="item-category" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15">
                        <option value="">Pilih kategori...</option>
                    </select>
                </div>

                <div>
                    <label id="label-income" for="item-income" class="mb-2 block text-sm font-bold text-slate-700">Dompet / Rekening</label>
                    <select id="item-income" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15">
                        <option value="">Pilih dompet...</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="item-note" class="mb-2 block text-sm font-bold text-slate-700">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="item-note" rows="3" placeholder="Catatan tambahan bila diperlukan..." class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15"></textarea>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="card p-6">
                <h2 class="font-extrabold text-[#102A43]">Aksi Simpan</h2>
                <p class="mt-2 text-sm leading-6 text-slate-500">Periksa kembali detail sebelum menyimpan transaksi ke sistem.</p>
                
                <button type="submit" id="btn-save" class="mt-6 w-full rounded-xl bg-[#1F4E79] px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-900/15 transition hover:bg-[#163859] disabled:cursor-not-allowed disabled:opacity-60">
                    Simpan Transaksi
                </button>
                
                <button type="button" id="btn-delete" onclick="deleteCurrentItem()" class="hidden mt-3 w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600 transition hover:bg-rose-100">
                    Hapus Transaksi
                </button>
            </div>

            <div class="rounded-2xl border border-[#19B5A5]/20 bg-[#ECFAF8] p-5">
                <div class="flex gap-3">
                    <span class="text-lg">✦</span>
                    <div>
                        <h3 class="text-sm font-extrabold text-[#147B71]">Tips Finansial</h3>
                        <p class="mt-1 text-xs leading-5 text-slate-600">Pastikan saldo dompet mencukupi untuk pengeluaran, atau tambahkan pemasukan baru agar grafik arus kas tetap akurat.</p>
                    </div>
                </div>
            </div>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
const urlParams = new URLSearchParams(window.location.search);
let currentType = urlParams.get('type') === 'income' ? 'income' : 'expense';
const itemId = urlParams.get('id');
const incomeIdParam = urlParams.get('income_id');
const isCreate = !itemId;

let categoriesList = [];
let incomesList = [];
let existingData = null;

if (!token) window.location.href = '/login';

function setStatus(text = '', type = 'loading') {
    const badge = document.getElementById('transaction-status');
    badge.textContent = text;
    badge.className = type === 'error'
        ? 'rounded-full bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-600'
        : 'rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500';
    badge.classList.toggle('hidden', !text);
}

function showError(message = '') {
    const box = document.getElementById('page-error');
    box.textContent = message;
    box.classList.toggle('hidden', !message);
}

async function authFetch(url, options = {}) {
    const headers = {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        ...(options.headers || {})
    };
    const response = await fetch(url, { ...options, headers });
    if (response.status === 401) {
        localStorage.removeItem('token');
        window.location.href = '/login';
        return null;
    }
    return response;
}

function updateUiForType(type) {
    currentType = type;
    const isExpense = type === 'expense';
    
    // Update badge & input styles
    const badge = document.getElementById('badge-type-indicator');
    const amountInput = document.getElementById('item-amount');
    const tabExpense = document.getElementById('btn-tab-expense');
    const tabIncome = document.getElementById('btn-tab-income');

    if (isExpense) {
        badge.textContent = 'Pengeluaran';
        badge.className = 'rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600';
        amountInput.className = 'w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm font-extrabold text-rose-500 outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15';
        document.getElementById('label-income').textContent = 'Sumber Dana / Dompet';
        
        tabExpense.className = 'flex-1 rounded-lg py-2.5 text-xs font-bold transition shadow-sm bg-white text-rose-600';
        tabIncome.className = 'flex-1 rounded-lg py-2.5 text-xs font-bold transition text-slate-600 hover:text-slate-900';
    } else {
        badge.textContent = 'Pemasukan';
        badge.className = 'rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-600';
        amountInput.className = 'w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm font-extrabold text-emerald-600 outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15';
        document.getElementById('label-income').textContent = 'Rekening Penerima / Dompet';
        
        tabIncome.className = 'flex-1 rounded-lg py-2.5 text-xs font-bold transition shadow-sm bg-white text-emerald-600';
        tabExpense.className = 'flex-1 rounded-lg py-2.5 text-xs font-bold transition text-slate-600 hover:text-slate-900';
    }

    renderCategoriesSelect();
}

function switchType(type) {
    if (!isCreate) return; // tidak bisa ubah type saat edit
    updateUiForType(type);
}

function renderCategoriesSelect() {
    const select = document.getElementById('item-category');
    select.innerHTML = '<option value="">Pilih kategori...</option>';
    const filtered = categoriesList.filter(c => c.type === currentType || !c.type);
    filtered.forEach(cat => {
        const opt = document.createElement('option');
        opt.value = cat.id;
        opt.textContent = `${cat.icon || '📁'} ${cat.name}`;
        select.appendChild(opt);
    });
    if (existingData && existingData.category_id) {
        select.value = existingData.category_id;
    }
}

function renderIncomesSelect() {
    const select = document.getElementById('item-income');
    select.innerHTML = '<option value="">Pilih dompet...</option>';
    incomesList.forEach(inc => {
        const opt = document.createElement('option');
        opt.value = inc.id;
        opt.textContent = `${inc.name} ${inc.is_primary ? '⭐' : ''}`;
        select.appendChild(opt);
    });
    if (existingData && (existingData.income_id || incomeIdParam)) {
        select.value = existingData.income_id || incomeIdParam;
    } else if (incomesList.length > 0 && isCreate) {
        const primary = incomesList.find(i => i.is_primary) || incomesList[0];
        select.value = primary.id;
    }
}

async function init() {
    setStatus('Memuat data...');
    showError('');

    if (!isCreate) {
        document.getElementById('type-switcher').classList.add('hidden');
        document.getElementById('btn-delete').classList.remove('hidden');
        document.getElementById('page-title').textContent = currentType === 'income' ? 'Edit Pemasukan' : 'Edit Pengeluaran';
        document.getElementById('btn-save').textContent = 'Simpan Perubahan';
    } else {
        document.getElementById('page-title').textContent = 'Tambah Transaksi Baru';
        document.getElementById('item-date').value = new Date().toISOString().slice(0, 10);
    }

    try {
        const [catRes, incRes] = await Promise.all([
            authFetch('/api/v1/categories'),
            authFetch('/api/v1/incomes')
        ]);

        if (catRes && catRes.data) categoriesList = catRes.data;
        if (incRes && incRes.data) incomesList = incRes.data;

        renderIncomesSelect();
        updateUiForType(currentType);

        if (!isCreate) {
            let detailRes;
            if (currentType === 'income') {
                const targetInc = incomeIdParam || (incomesList[0] ? incomesList[0].id : null);
                detailRes = await authFetch(`/api/v1/incomes/${targetInc}/receipts`);
                if (detailRes && detailRes.data) {
                    existingData = detailRes.data.find(r => r.id === itemId);
                }
            } else {
                const res = await authFetch(`/api/v1/expenses/${itemId}`);
                if (res && res.data) existingData = res.data;
            }

            if (existingData) {
                document.getElementById('item-name').value = existingData.item || existingData.note || '';
                document.getElementById('item-amount').value = existingData.amount || '';
                const dt = existingData.spent_at || existingData.received_at;
                document.getElementById('item-date').value = dt ? dt.slice(0, 10) : '';
                document.getElementById('item-category').value = existingData.category_id || '';
                document.getElementById('item-income').value = existingData.income_id || incomeIdParam || '';
                document.getElementById('item-note').value = existingData.note || '';

                if (existingData.raw_input) {
                    document.getElementById('smart-entry-box').classList.remove('hidden');
                    document.getElementById('smart-entry-raw').textContent = `“${existingData.raw_input}”`;
                }
                setStatus('Data siap');
            } else {
                throw new Error('Transaksi tidak ditemukan.');
            }
        } else {
            setStatus('Formulir siap');
        }
    } catch (err) {
        console.error(err);
        showError(err.message || 'Gagal memuat data formulir transaksi.');
        setStatus('Gagal memuat', 'error');
    }
}

document.getElementById('transaksi-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;
    showError('');

    const itemName = document.getElementById('item-name').value.trim();
    const amountVal = parseInt(document.getElementById('item-amount').value, 10);
    const dateVal = document.getElementById('item-date').value;
    const catId = document.getElementById('item-category').value || null;
    const incId = document.getElementById('item-income').value || null;
    const noteVal = document.getElementById('item-note').value.trim() || null;

    if (!incId) {
        showError('Pilih dompet / sumber dana terlebih dahulu.');
        btn.textContent = 'Simpan Transaksi';
        btn.disabled = false;
        return;
    }

    try {
        let url = '';
        let method = 'POST';
        let payload = {};

        if (currentType === 'expense') {
            url = isCreate ? '/api/v1/expenses' : `/api/v1/expenses/${itemId}`;
            method = isCreate ? 'POST' : 'PATCH';
            payload = {
                income_id: incId,
                category_id: catId,
                item: itemName,
                amount: amountVal,
                spent_at: dateVal,
                note: noteVal,
                source: existingData?.source || 'manual',
                client_id: existingData?.client_id || crypto.randomUUID()
            };
        } else {
            // Income Receipt
            url = isCreate 
                ? `/api/v1/incomes/${incId}/receipts` 
                : `/api/v1/incomes/${incomeIdParam || incId}/receipts/${itemId}`;
            method = isCreate ? 'POST' : 'PATCH';
            payload = {
                category_id: catId,
                amount: amountVal,
                received_at: dateVal,
                note: itemName + (noteVal ? ` - ${noteVal}` : ''),
                client_id: existingData?.client_id || crypto.randomUUID()
            };
        }

        const res = await authFetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (!res) return;
        if (!res.ok) {
            const errData = await res.json().catch(() => ({}));
            throw new Error(errData.message || 'Gagal menyimpan transaksi.');
        }

        alert('Transaksi berhasil disimpan!');
        window.location.href = '/transaksi';
    } catch (err) {
        console.error(err);
        showError(err.message || 'Terjadi kesalahan sistem saat menyimpan.');
    } finally {
        btn.textContent = isCreate ? 'Simpan Transaksi' : 'Simpan Perubahan';
        btn.disabled = false;
    }
});

async function deleteCurrentItem() {
    if (!confirm('Yakin ingin menghapus transaksi ini? Tindakan tidak dapat dibatalkan.')) return;
    try {
        let url = '';
        if (currentType === 'expense') {
            url = `/api/v1/expenses/${itemId}`;
        } else {
            url = `/api/v1/incomes/${incomeIdParam}/receipts/${itemId}`;
        }

        const res = await authFetch(url, { method: 'DELETE' });
        if (!res) return;
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menghapus transaksi.');
        }
        alert('Transaksi telah dihapus.');
        window.location.href = '/transaksi';
    } catch (err) {
        showError(err.message || 'Gagal menghapus transaksi.');
    }
}

init();
</script>
@endpush
