@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Klasifikasi Keuangan ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Kategori Transaksi</h1>
        <p class="text-sm text-slate-500 mt-2">Kelola kategori pengeluaran dan pemasukan untuk mempermudah pencatatan dan pelaporan.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openCatModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1F4E79] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-900/15 transition hover:bg-[#163859]">
            <span class="text-lg leading-none">+</span> Tambah Kategori
        </button>
    </div>
</div>

<div id="cat-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<!-- Filter Tabs -->
<div class="card overflow-hidden">
    <div class="border-b border-slate-100 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button id="cat-filter-all" onclick="filterTab('all')" class="cat-filter-btn rounded-full bg-[#1F4E79] px-4 py-1.5 text-xs font-bold text-white transition">Semua</button>
            <button id="cat-filter-expense" onclick="filterTab('expense')" class="cat-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">💸 Pengeluaran</button>
            <button id="cat-filter-income" onclick="filterTab('income')" class="cat-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">💰 Pemasukan</button>
        </div>
        <span id="cat-count-badge" class="text-xs font-bold text-slate-400">0 Kategori</span>
    </div>

    <div class="table-wrap overflow-x-auto">
        <table class="w-full min-w-[750px] text-sm">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                <tr>
                    <th class="px-5 py-4 text-left">Ikon</th>
                    <th class="px-5 py-4 text-left">Nama Kategori</th>
                    <th class="px-5 py-4 text-left">Tipe</th>
                    <th class="px-5 py-4 text-left">Pos Bawaan</th>
                    <th class="px-5 py-4 text-left">Status</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="categories-tbody">
                <tr><td colspan="6" class="p-8 text-center text-xs text-slate-400">Memuat kategori...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT KATEGORI -->
<div id="cat-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <h3 id="cat-modal-title" class="font-extrabold text-lg text-[#102A43]">Tambah Kategori Baru</h3>
            <button onclick="closeCatModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="cat-form" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Kategori</label>
                <select id="modal-cat-type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold outline-none focus:bg-white focus:border-[#19B5A5]">
                    <option value="expense" selected>💸 Pengeluaran</option>
                    <option value="income">💰 Pemasukan</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori</label>
                <input id="modal-cat-name" type="text" required placeholder="Mis. Belanja Bulanan, Gaji Pokok" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ikon Emoji</label>
                    <input id="modal-cat-icon" type="text" value="📁" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-center outline-none focus:bg-white focus:border-[#19B5A5]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Warna Aksen</label>
                    <input id="modal-cat-color" type="color" value="#19B5A5" class="w-full h-10 rounded-xl border border-slate-200 bg-slate-50 p-1 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Pos Alokasi (Khusus Pengeluaran)</label>
                <select id="modal-cat-bucket" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
                    <option value="">Tidak ditentukan</option>
                    <option value="need">🥖 Kebutuhan (Need)</option>
                    <option value="want">☕ Keinginan (Want)</option>
                </select>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closeCatModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-save-cat" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-[#1F4E79] hover:bg-[#163859] transition shadow-md shadow-blue-900/15">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
if (!token) window.location.href = '/login';

let allCategories = [];
let currentFilter = 'all';
let editCatId = null;

function showError(msg) {
    const el = document.getElementById('cat-error');
    el.textContent = msg;
    el.classList.toggle('hidden', !msg);
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

async function loadCategories() {
    showError('');
    try {
        const res = await authFetch('/api/v1/categories');
        if (!res || !res.ok) throw new Error('Gagal memuat kategori.');
        const json = await res.json();
        allCategories = json.data || [];
        renderList();
    } catch (err) {
        console.error(err);
        showError('Gagal memuat daftar kategori.');
    }
}

function renderList() {
    const tbody = document.getElementById('categories-tbody');
    const filtered = allCategories.filter(c => currentFilter === 'all' || c.type === currentFilter);
    document.getElementById('cat-count-badge').textContent = `${filtered.length} Kategori`;

    if (!filtered.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-xs text-slate-400">Tidak ada kategori yang cocok.</td></tr>';
        return;
    }

    tbody.innerHTML = filtered.map(c => {
        const isExpense = c.type === 'expense';
        const typeBadge = isExpense
            ? '<span class="badge bg-rose-50 text-rose-600 border border-rose-100">Pengeluaran</span>'
            : '<span class="badge bg-emerald-50 text-emerald-600 border border-emerald-100">Pemasukan</span>';

        const isDefault = Boolean(c.is_default);
        const statusBadge = isDefault
            ? '<span class="badge bg-slate-100 text-slate-500">Bawaan Sistem</span>'
            : '<span class="badge bg-blue-50 text-blue-700">Kustom</span>';

        const bucketLabel = c.bucket === 'need' ? '🥖 Kebutuhan' : (c.bucket === 'want' ? '☕ Keinginan' : '—');

        return `
            <tr class="border-t border-slate-100 hover:bg-slate-50/60 transition">
                <td class="px-5 py-3.5">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-lg" style="border-left: 3px solid ${c.color || '#19B5A5'}">
                        ${c.icon || '📁'}
                    </div>
                </td>
                <td class="px-5 py-3.5 font-bold text-slate-800">${c.name}</td>
                <td class="px-5 py-3.5">${typeBadge}</td>
                <td class="px-5 py-3.5 text-xs text-slate-500">${bucketLabel}</td>
                <td class="px-5 py-3.5">${statusBadge}</td>
                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                    <button onclick="editCat('${c.id}')" class="mr-3 font-bold text-[#1F4E79] hover:underline text-xs">Edit</button>
                    ${!isDefault ? `<button onclick="deleteCat('${c.id}')" class="font-bold text-rose-500 hover:underline text-xs">Hapus</button>` : '<span class="text-slate-300 text-xs">—</span>'}
                </td>
            </tr>
        `;
    }).join('');
}

function filterTab(tab) {
    currentFilter = tab;
    document.querySelectorAll('.cat-filter-btn').forEach(btn => {
        btn.className = 'cat-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition';
    });
    document.getElementById(`cat-filter-${tab}`).className = 'cat-filter-btn rounded-full bg-[#1F4E79] px-4 py-1.5 text-xs font-bold text-white transition';
    renderList();
}

function openCatModal() {
    editCatId = null;
    document.getElementById('cat-modal-title').textContent = 'Tambah Kategori Baru';
    document.getElementById('cat-form').reset();
    document.getElementById('modal-cat-icon').value = '📁';
    document.getElementById('modal-cat-color').value = '#19B5A5';
    document.getElementById('cat-modal').classList.remove('hidden');
}

function closeCatModal() {
    document.getElementById('cat-modal').classList.add('hidden');
}

function editCat(id) {
    const c = allCategories.find(x => x.id === id);
    if (!c) return;
    editCatId = id;
    document.getElementById('cat-modal-title').textContent = 'Edit Kategori';
    document.getElementById('modal-cat-type').value = c.type || 'expense';
    document.getElementById('modal-cat-name').value = c.name || '';
    document.getElementById('modal-cat-icon').value = c.icon || '📁';
    document.getElementById('modal-cat-color').value = c.color || '#19B5A5';
    document.getElementById('modal-cat-bucket').value = c.bucket || '';
    document.getElementById('cat-modal').classList.remove('hidden');
}

document.getElementById('cat-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-cat');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const payload = {
        name: document.getElementById('modal-cat-name').value.trim(),
        type: document.getElementById('modal-cat-type').value,
        icon: document.getElementById('modal-cat-icon').value.trim() || '📁',
        color: document.getElementById('modal-cat-color').value || '#19B5A5',
        bucket: document.getElementById('modal-cat-bucket').value || null
    };

    try {
        const url = editCatId ? `/api/v1/categories/${editCatId}` : '/api/v1/categories';
        const method = editCatId ? 'PATCH' : 'POST';

        const res = await authFetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menyimpan kategori.');
        }

        alert('Kategori berhasil disimpan!');
        closeCatModal();
        loadCategories();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Simpan Kategori';
        btn.disabled = false;
    }
});

async function deleteCat(id) {
    if (!confirm('Hapus kategori ini?')) return;
    try {
        const res = await authFetch(`/api/v1/categories/${id}`, { method: 'DELETE' });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menghapus kategori.');
        }
        loadCategories();
    } catch (err) {
        alert(err.message);
    }
}

loadCategories();
</script>
@endpush