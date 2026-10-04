@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Pertumbuhan Aset ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Portofolio Investasi</h1>
        <p class="text-sm text-slate-500 mt-2">Pantau akumulasi modal dan diversifikasi instrumen investasimu di satu tempat.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openInvestModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1F4E79] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-900/15 transition hover:bg-[#163859]">
            <span class="text-lg leading-none">+</span> Catat Investasi Baru
        </button>
    </div>
</div>

<div id="invest-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
    <div class="card p-5 border-l-4 border-l-[#1F4E79]">
        <div class="text-xs font-semibold text-slate-500">Total Modal Investasi</div>
        <div id="kpi-total-invested" class="text-2xl font-extrabold text-[#102A43] mt-2">Rp0</div>
        <div class="text-xs text-slate-400 mt-2">Akumulasi seluruh instrumen</div>
    </div>
    <div class="card p-5 border-l-4 border-l-[#19B5A5]">
        <div class="text-xs font-semibold text-slate-500">Jumlah Aset Aktif</div>
        <div id="kpi-total-count" class="text-2xl font-extrabold text-[#148B80] mt-2">0</div>
        <div class="text-xs text-slate-400 mt-2">Instrumen tercatat</div>
    </div>
    <div class="card p-5 border-l-4 border-l-purple-500">
        <div class="text-xs font-semibold text-slate-500">Instrumen Terbanyak</div>
        <div id="kpi-top-type" class="text-2xl font-extrabold text-purple-700 mt-2">—</div>
        <div class="text-xs text-slate-400 mt-2">Dominasi portofolio</div>
    </div>
</div>

<!-- Table Card -->
<div class="card overflow-hidden">
    <div class="border-b border-slate-100 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 flex-wrap">
            <button onclick="filterInvestType('all')" class="inv-filter-btn rounded-full bg-[#1F4E79] px-4 py-1.5 text-xs font-bold text-white transition">Semua</button>
            <button onclick="filterInvestType('stock')" class="inv-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">📈 Saham</button>
            <button onclick="filterInvestType('mutual_fund')" class="inv-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">💼 Reksa Dana</button>
            <button onclick="filterInvestType('gold')" class="inv-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">🪙 Emas</button>
            <button onclick="filterInvestType('crypto')" class="inv-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">🪙 Kripto</button>
            <button onclick="filterInvestType('deposit')" class="inv-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">🏦 Deposito</button>
            <button onclick="filterInvestType('bond')" class="inv-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition">📜 SBN / Obligasi</button>
        </div>
        <span id="invest-count-label" class="text-xs font-bold text-slate-400">0 Catatan</span>
    </div>

    <div class="table-wrap overflow-x-auto">
        <table class="w-full min-w-[850px] text-sm">
            <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                <tr>
                    <th class="px-5 py-4 text-left">Tanggal</th>
                    <th class="px-5 py-4 text-left">Tipe</th>
                    <th class="px-5 py-4 text-left">Nama Instrumen</th>
                    <th class="px-5 py-4 text-left">Dompet Sumber</th>
                    <th class="px-5 py-4 text-left">Catatan</th>
                    <th class="px-5 py-4 text-right">Modal Masuk</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody id="invest-tbody">
                <tr><td colspan="7" class="p-8 text-center text-xs text-slate-400">Memuat portofolio investasi...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT INVESTASI -->
<div id="invest-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <h3 id="invest-modal-title" class="font-extrabold text-lg text-[#102A43]">Catat Investasi Baru</h3>
            <button onclick="closeInvestModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="invest-form" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Instrumen</label>
                <select id="inv-type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold outline-none focus:bg-white focus:border-[#19B5A5]">
                    <option value="stock">📈 Saham</option>
                    <option value="mutual_fund" selected>💼 Reksa Dana</option>
                    <option value="gold">🪙 Emas Fisik / Digital</option>
                    <option value="bond">📜 SBN / Obligasi Negara</option>
                    <option value="deposit">🏦 Deposito Bank</option>
                    <option value="crypto">🌐 Aset Kripto</option>
                    <option value="other">📦 Instrumen Lainnya</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Instrumen / Kode Emiten</label>
                <input id="inv-name" type="text" required placeholder="Mis. BBCA, Sucorinvest Sharia, Antam 5g" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Investasi (Rp)</label>
                <input id="inv-amount" type="number" min="1000" required placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-extrabold text-[#1F4E79] outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Dompet Sumber Dana (Opsional)</label>
                <select id="inv-income-id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
                    <option value="">Pilih dompet...</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Investasi</label>
                <input id="inv-date" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                <input id="inv-note" type="text" placeholder="Mis. Beli saat koreksi pasar, DCA bulanan" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closeInvestModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-save-inv" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-[#1F4E79] hover:bg-[#163859] transition shadow-md shadow-blue-900/15">Simpan Investasi</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
if (!token) window.location.href = '/login';

let investmentsList = [];
let incomesList = [];
let activeFilter = 'all';
let editInvestId = null;

const fmtRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(num || 0));

const typeMeta = {
    stock: { label: 'Saham', badge: 'bg-emerald-50 text-emerald-700 border-emerald-100', icon: '📈' },
    mutual_fund: { label: 'Reksa Dana', badge: 'bg-blue-50 text-blue-700 border-blue-100', icon: '💼' },
    gold: { label: 'Emas', badge: 'bg-amber-50 text-amber-700 border-amber-100', icon: '🪙' },
    bond: { label: 'SBN / Obligasi', badge: 'bg-teal-50 text-teal-700 border-teal-100', icon: '📜' },
    deposit: { label: 'Deposito', badge: 'bg-slate-100 text-slate-700 border-slate-200', icon: '🏦' },
    crypto: { label: 'Kripto', badge: 'bg-purple-50 text-purple-700 border-purple-100', icon: '🌐' },
    other: { label: 'Lainnya', badge: 'bg-slate-50 text-slate-600 border-slate-200', icon: '📦' }
};

function showError(msg) {
    const el = document.getElementById('invest-error');
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

async function loadData() {
    showError('');
    try {
        const [invRes, incRes] = await Promise.all([
            authFetch('/api/v1/investments'),
            authFetch('/api/v1/incomes')
        ]);

        if (incRes && incRes.ok) {
            const incJson = await incRes.json();
            incomesList = incJson.data || [];
            populateIncomes();
        }

        if (invRes && invRes.ok) {
            const invJson = await invRes.json();
            investmentsList = invJson.data || [];
            updateKpis();
            renderList();
        }
    } catch (err) {
        console.error(err);
        showError('Gagal memuat daftar investasi.');
    }
}

function updateKpis() {
    const total = investmentsList.reduce((sum, it) => sum + Number(it.amount || 0), 0);
    document.getElementById('kpi-total-invested').textContent = fmtRp(total);
    document.getElementById('kpi-total-count').textContent = investmentsList.length;

    // Hitung tipe dominan
    const counts = {};
    investmentsList.forEach(it => {
        counts[it.instrument_type] = (counts[it.instrument_type] || 0) + 1;
    });
    let topType = '—';
    let max = 0;
    for (const [k, v] of Object.entries(counts)) {
        if (v > max) { max = v; topType = typeMeta[k]?.label || k; }
    }
    document.getElementById('kpi-top-type').textContent = topType;
}

function renderList() {
    const tbody = document.getElementById('invest-tbody');
    const filtered = investmentsList.filter(it => activeFilter === 'all' || it.instrument_type === activeFilter);
    document.getElementById('invest-count-label').textContent = `${filtered.length} Catatan`;

    if (!filtered.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="p-10 text-center text-xs text-slate-400">Belum ada portofolio investasi pada kategori ini.</td></tr>';
        return;
    }

    tbody.innerHTML = filtered.map(it => {
        const meta = typeMeta[it.instrument_type] || typeMeta.other;
        const dt = it.invested_at ? new Date(it.invested_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '-';
        const inc = incomesList.find(i => i.id === it.income_id);

        return `
            <tr class="border-t border-slate-100 hover:bg-slate-50/60 transition">
                <td class="px-5 py-4 text-slate-500 text-xs whitespace-nowrap">${dt}</td>
                <td class="px-5 py-4 whitespace-nowrap">
                    <span class="badge border ${meta.badge}">${meta.icon} ${meta.label}</span>
                </td>
                <td class="px-5 py-4 font-bold text-slate-800">${it.instrument_name}</td>
                <td class="px-5 py-4 text-slate-600 text-xs">${inc ? inc.name : '—'}</td>
                <td class="px-5 py-4 text-slate-500 text-xs">${it.note || '—'}</td>
                <td class="px-5 py-4 text-right whitespace-nowrap font-extrabold text-[#1F4E79]">${fmtRp(it.amount)}</td>
                <td class="px-5 py-4 text-right whitespace-nowrap">
                    <button onclick="editInvest('${it.id}')" class="mr-3 font-bold text-[#1F4E79] hover:underline text-xs">Edit</button>
                    <button onclick="deleteInvest('${it.id}')" class="font-bold text-rose-500 hover:underline text-xs">Hapus</button>
                </td>
            </tr>
        `;
    }).join('');
}

function filterInvestType(type) {
    activeFilter = type;
    document.querySelectorAll('.inv-filter-btn').forEach(btn => {
        btn.className = 'inv-filter-btn rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition';
    });
    event.target.className = 'inv-filter-btn rounded-full bg-[#1F4E79] px-4 py-1.5 text-xs font-bold text-white transition';
    renderList();
}

function populateIncomes() {
    const sel = document.getElementById('inv-income-id');
    sel.innerHTML = '<option value="">Pilih dompet (opsional)...</option>';
    incomesList.forEach(inc => {
        const opt = document.createElement('option');
        opt.value = inc.id;
        opt.textContent = `${inc.name} (Saldo: ${fmtRp(inc.balance || 0)})`;
        sel.appendChild(opt);
    });
}

function openInvestModal() {
    editInvestId = null;
    document.getElementById('invest-modal-title').textContent = 'Catat Investasi Baru';
    document.getElementById('invest-form').reset();
    document.getElementById('inv-date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('invest-modal').classList.remove('hidden');
}

function closeInvestModal() {
    document.getElementById('invest-modal').classList.add('hidden');
}

function editInvest(id) {
    const it = investmentsList.find(x => x.id === id);
    if (!it) return;
    editInvestId = id;
    document.getElementById('invest-modal-title').textContent = 'Edit Catatan Investasi';
    document.getElementById('inv-type').value = it.instrument_type;
    document.getElementById('inv-name').value = it.instrument_name;
    document.getElementById('inv-amount').value = it.amount;
    document.getElementById('inv-income-id').value = it.income_id || '';
    document.getElementById('inv-date').value = it.invested_at ? it.invested_at.slice(0, 10) : '';
    document.getElementById('inv-note').value = it.note || '';
    document.getElementById('invest-modal').classList.remove('hidden');
}

document.getElementById('invest-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-inv');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const payload = {
        instrument_type: document.getElementById('inv-type').value,
        instrument_name: document.getElementById('inv-name').value.trim(),
        amount: parseInt(document.getElementById('inv-amount').value, 10),
        income_id: document.getElementById('inv-income-id').value || null,
        invested_at: document.getElementById('inv-date').value,
        note: document.getElementById('inv-note').value.trim() || null,
        client_id: crypto.randomUUID()
    };

    try {
        const url = editInvestId ? `/api/v1/investments/${editInvestId}` : '/api/v1/investments';
        const method = editInvestId ? 'PATCH' : 'POST';

        const res = await authFetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menyimpan investasi.');
        }

        alert('Investasi berhasil disimpan!');
        closeInvestModal();
        loadData();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Simpan Investasi';
        btn.disabled = false;
    }
});

async function deleteInvest(id) {
    if (!confirm('Hapus catatan investasi ini?')) return;
    try {
        const res = await authFetch(`/api/v1/investments/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error('Gagal menghapus catatan.');
        loadData();
    } catch (err) {
        alert(err.message);
    }
}

loadData();
</script>
@endpush
