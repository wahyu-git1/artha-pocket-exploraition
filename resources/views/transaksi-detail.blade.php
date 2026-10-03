@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-5xl">
    <a href="{{ route('transaksi') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-[#1F4E79]">
        <span class="text-lg">←</span> Kembali ke transaksi
    </a>

    <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="text-sm text-slate-500">Perbarui catatan finansialmu ✦</div>
            <h1 class="mt-1 text-3xl font-extrabold text-[#102A43]">Edit transaksi</h1>
            <p class="mt-2 text-sm text-slate-500">Pastikan nominal, kategori, dan sumber dana sudah sesuai.</p>
        </div>
        <span id="transaction-status" class="hidden rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-500">Memuat data...</span>
    </div>

    <div id="page-error" class="hidden mt-6 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

    <form id="edit-form" class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        @csrf

        <section class="card p-6 lg:col-span-2">
            <div id="smart-entry-box" class="hidden mb-6 rounded-2xl border border-blue-100 bg-blue-50 p-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-lg">⚡</div>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-[.14em] text-blue-800">Dicatat lewat Smart Entry</p>
                        <p id="smart-entry-raw" class="mt-2 text-sm italic leading-6 text-slate-600">“...”</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between border-b border-slate-100 pb-5">
                <div><h2 class="text-lg font-extrabold text-[#102A43]">Detail transaksi</h2><p class="mt-1 text-sm text-slate-500">Informasi dasar untuk transaksi ini.</p></div>
                <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-600">Pengeluaran</span>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="item-name" class="mb-2 block text-sm font-bold text-slate-700">Item transaksi</label>
                    <input id="item-name" type="text" required placeholder="Mis. Bakso" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15">
                </div>

                <div>
                    <label for="item-amount" class="mb-2 block text-sm font-bold text-slate-700">Nominal</label>
                    <div class="relative"><span class="absolute left-4 top-3 text-sm font-bold text-slate-500">Rp</span><input id="item-amount" type="number" min="1" required placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm font-extrabold text-rose-500 outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15"></div>
                </div>

                <div>
                    <label for="item-date" class="mb-2 block text-sm font-bold text-slate-700">Tanggal transaksi</label>
                    <input id="item-date" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15">
                </div>

                <div>
                    <label for="item-category" class="mb-2 block text-sm font-bold text-slate-700">Kategori</label>
                    <select id="item-category" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15"><option value="">Pilih kategori</option></select>
                </div>

                <div>
                    <label for="item-income" class="mb-2 block text-sm font-bold text-slate-700">Sumber dana / dompet</label>
                    <select id="item-income" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15"><option value="">Pilih dompet</option></select>
                </div>

                <div class="sm:col-span-2">
                    <label for="item-note" class="mb-2 block text-sm font-bold text-slate-700">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                    <textarea id="item-note" rows="4" placeholder="Tambahkan catatan jika diperlukan..." class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15"></textarea>
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <div class="card p-6"><h2 class="font-extrabold text-[#102A43]">Aksi transaksi</h2><p class="mt-2 text-sm leading-6 text-slate-500">Simpan perubahan setelah memeriksa seluruh informasi di formulir.</p><button type="submit" id="btn-save" class="mt-6 w-full rounded-xl bg-[#1F4E79] px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-900/15 transition hover:bg-[#163859] disabled:cursor-not-allowed disabled:opacity-60">Simpan perubahan</button><button type="button" onclick="deleteCurrentExpense()" class="mt-3 w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600 transition hover:bg-rose-100">Hapus transaksi</button></div>
            <div class="rounded-2xl border border-[#19B5A5]/20 bg-[#ECFAF8] p-5"><div class="flex gap-3"><span class="text-lg">✦</span><div><h3 class="text-sm font-extrabold text-[#147B71]">Tips Finance Orbit</h3><p class="mt-1 text-xs leading-5 text-slate-600">Pilih sumber dana yang benar agar sisa saldo per pendapatan tetap akurat.</p></div></div></div>
        </aside>
    </form>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
const urlParams = new URLSearchParams(window.location.search);
const expenseId = urlParams.get('id');

if (!token) window.location.href = '/login';
if (!expenseId) {
    alert('ID transaksi tidak ditemukan.');
    window.location.href = '/transaksi';
}

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

async function authorizedFetch(url, options = {}) {
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

function populateSelect(selectId, items, formatter) {
    const select = document.getElementById(selectId);
    (items || []).forEach(item => {
        const option = document.createElement('option');
        option.value = item.id;
        option.textContent = formatter(item);
        select.appendChild(option);
    });
}

async function init() {
    setStatus('Memuat data...');
    showError('');

    try {
        const [categoryResponse, incomeResponse, expenseResponse] = await Promise.all([
            authorizedFetch('/api/v1/categories?filter[type]=expense'),
            authorizedFetch('/api/v1/incomes'),
            authorizedFetch(`/api/v1/expenses/${expenseId}`)
        ]);

        if (!categoryResponse || !incomeResponse || !expenseResponse) return;
        if (!expenseResponse.ok) throw new Error('Data transaksi tidak ditemukan.');

        const [categoryData, incomeData, expenseData] = await Promise.all([
            categoryResponse.json(), incomeResponse.json(), expenseResponse.json()
        ]);

        populateSelect('item-category', categoryData.data || [], category => `${category.icon || '📁'} ${category.name}`);
        populateSelect('item-income', incomeData.data || [], income => income.name);

        const item = expenseData.data;
        document.getElementById('item-name').value = item.item || '';
        document.getElementById('item-amount').value = item.amount || '';
        document.getElementById('item-date').value = item.spent_at ? item.spent_at.slice(0, 10) : '';
        document.getElementById('item-category').value = item.category_id || '';
        document.getElementById('item-income').value = item.income_id || '';
        document.getElementById('item-note').value = item.note || '';

        if (item.raw_input) {
            document.getElementById('smart-entry-box').classList.remove('hidden');
            document.getElementById('smart-entry-raw').textContent = `“${item.raw_input}”`;
        }

        setStatus('Data siap diedit');
    } catch (error) {
        console.error(error);
        showError(error.message || 'Gagal memuat detail transaksi.');
        setStatus('Gagal memuat', 'error');
    }
}

document.getElementById('edit-form').addEventListener('submit', async function (event) {
    event.preventDefault();
    const button = document.getElementById('btn-save');
    button.textContent = 'Menyimpan...';
    button.disabled = true;
    showError('');

    const payload = {
        item: document.getElementById('item-name').value.trim(),
        amount: parseInt(document.getElementById('item-amount').value, 10),
        spent_at: document.getElementById('item-date').value,
        category_id: document.getElementById('item-category').value ? parseInt(document.getElementById('item-category').value, 10) : null,
        income_id: document.getElementById('item-income').value ? parseInt(document.getElementById('item-income').value, 10) : null,
        note: document.getElementById('item-note').value.trim() || null
    };

    try {
        const response = await authorizedFetch(`/api/v1/expenses/${expenseId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        if (!response) return;
        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || 'Gagal menyimpan transaksi.');
        }
        alert('Transaksi berhasil diperbarui!');
        window.location.href = '/transaksi';
    } catch (error) {
        showError(error.message || 'Terjadi kesalahan jaringan.');
    } finally {
        button.textContent = 'Simpan perubahan';
        button.disabled = false;
    }
});

async function deleteCurrentExpense() {
    if (!confirm('Hapus transaksi ini? Tindakan ini tidak dapat dibatalkan.')) return;
    try {
        const response = await authorizedFetch(`/api/v1/expenses/${expenseId}`, { method: 'DELETE' });
        if (!response) return;
        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || 'Gagal menghapus transaksi.');
        }
        alert('Transaksi dihapus.');
        window.location.href = '/transaksi';
    } catch (error) {
        showError(error.message || 'Terjadi kesalahan jaringan.');
    }
}

init();
</script>
@endpush
