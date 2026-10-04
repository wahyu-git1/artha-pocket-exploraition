@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Impian & Rencana ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Target Tabungan</h1>
        <p class="text-sm text-slate-500 mt-2">Simulasikan dan wujudkan setiap tujuan finansialmu dengan disiplin.</p>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openGoalModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1F4E79] px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-900/15 transition hover:bg-[#163859]">
            <span class="text-lg leading-none">+</span> Buat Target Baru
        </button>
    </div>
</div>

<div id="target-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<!-- Simulator Box Card -->
<div class="card p-6 mb-8 border-t-4 border-t-[#19B5A5]">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
        <div>
            <h2 class="text-lg font-extrabold text-[#102A43] flex items-center gap-2">
                <span>⚡</span> Simulator Target Tabungan AI
            </h2>
            <p class="text-xs text-slate-500 mt-1">Hitung estimasi alokasi tabungan bulanan yang realistis sebelum menetapkan target.</p>
        </div>
        <span class="badge bg-teal-50 text-teal-700 border border-teal-200">Kalkulator Cepat</span>
    </div>

    <form id="sim-form" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Target Harga (Rp)</label>
            <input id="sim-price" type="number" min="10000" step="5000" placeholder="Contoh: 15000000" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5] focus:ring-2 focus:ring-[#19B5A5]/15">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Target Tanggal Tercapai</label>
            <input id="sim-target-date" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5] focus:ring-2 focus:ring-[#19B5A5]/15">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Saldo Awal (Opsional)</label>
            <input id="sim-saved-amount" type="number" min="0" placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5] focus:ring-2 focus:ring-[#19B5A5]/15">
        </div>
        <div>
            <button type="submit" id="btn-sim" class="w-full rounded-xl bg-[#19B5A5] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#148B80] shadow-md shadow-teal-600/15">
                Hitung Simulasi
            </button>
        </div>
    </form>

    <!-- Hasil Simulasi -->
    <div id="sim-result" class="hidden mt-5 p-4 rounded-xl bg-[#F4F8FC] border border-slate-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <span class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Rekomendasi Setoran:</span>
                <div class="text-2xl font-extrabold text-[#1F4E79] mt-0.5" id="sim-res-monthly">Rp0 / bulan</div>
            </div>
            <div id="sim-res-warning" class="text-xs text-amber-700 bg-amber-50 border border-amber-200 px-3 py-2 rounded-lg max-w-md hidden">
                <!-- Warning Text -->
            </div>
        </div>
    </div>
</div>

<!-- Daftar Goals Grid -->
<div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-extrabold text-[#102A43]">Target Aktif & Terencana</h2>
    <span id="goals-count" class="text-xs font-bold text-slate-400">0 Target</span>
</div>

<div id="goals-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <div class="col-span-full card p-10 text-center text-sm text-slate-400">Memuat target tabungan...</div>
</div>

<!-- MODAL CREATE/EDIT TARGET -->
<div id="goal-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <h3 id="goal-modal-title" class="font-extrabold text-lg text-[#102A43]">Target Tabungan Baru</h3>
            <button onclick="closeGoalModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="goal-form" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Target</label>
                <input id="goal-name" type="text" required placeholder="Mis. Beli Laptop Baru, Dana Liburan" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5] focus:ring-2 focus:ring-[#19B5A5]/15">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Target Harga (Rp)</label>
                <input id="goal-price" type="number" min="10000" required placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-extrabold text-[#102A43] outline-none focus:bg-white focus:border-[#19B5A5] focus:ring-2 focus:ring-[#19B5A5]/15">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Target Tanggal</label>
                <input id="goal-target-date" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5] focus:ring-2 focus:ring-[#19B5A5]/15">
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closeGoalModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-save-goal" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-[#1F4E79] hover:bg-[#163859] transition shadow-md shadow-blue-900/15">Simpan Target</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DEPOSIT TABUNGAN -->
<div id="deposit-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-lg text-[#102A43]">Setor Tabungan 💰</h3>
                <p id="deposit-goal-name" class="text-xs text-slate-500 mt-0.5">Memuat...</p>
            </div>
            <button onclick="closeDepositModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="deposit-form" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Dompet Sumber Dana</label>
                <select id="deposit-income-id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
                    <option value="">Pilih dompet...</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Setoran (Rp)</label>
                <input id="deposit-amount" type="number" min="1000" required placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-extrabold text-emerald-600 outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Setor</label>
                <input id="deposit-date" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan (Opsional)</label>
                <input id="deposit-note" type="text" placeholder="Mis. Tabungan bonus" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closeDepositModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-save-deposit" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-md shadow-emerald-700/15">Konfirmasi Setor</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL RIWAYAT DEPOSIT -->
<div id="history-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-lg text-[#102A43]">Riwayat Setoran Tabungan</h3>
                <p id="history-goal-name" class="text-xs text-slate-500 mt-0.5">Daftar transaksi masuk</p>
            </div>
            <button onclick="closeHistoryModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <div id="history-container" class="space-y-3 max-h-72 overflow-y-auto pr-1">
            <div class="text-center text-sm text-slate-400 py-6">Memuat riwayat...</div>
        </div>

        <div class="pt-4 mt-3 border-t border-slate-100 flex justify-end">
            <button type="button" onclick="closeHistoryModal()" class="px-5 py-2.5 rounded-xl font-bold text-sm text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
if (!token) window.location.href = '/login';

let goalsList = [];
let incomesList = [];
let activeGoalId = null;
let editGoalId = null;

const fmtRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(num || 0));

function showError(msg) {
    const el = document.getElementById('target-error');
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

// 1. Initial Load
async function loadGoals() {
    showError('');
    try {
        const [gRes, incRes] = await Promise.all([
            authFetch('/api/v1/savings-goals'),
            authFetch('/api/v1/incomes')
        ]);
        if (!gRes || !incRes) return;

        const gData = await gRes.json();
        const incData = await incRes.json();

        goalsList = gData.data || [];
        incomesList = incData.data || [];

        renderGoals();
        populateIncomes();
    } catch (err) {
        console.error(err);
        showError('Gagal memuat target tabungan.');
    }
}

function renderGoals() {
    const container = document.getElementById('goals-container');
    document.getElementById('goals-count').textContent = `${goalsList.length} Target`;

    if (!goalsList.length) {
        container.innerHTML = `
            <div class="col-span-full card p-12 text-center">
                <div class="text-4xl mb-3">🎯</div>
                <h3 class="font-extrabold text-[#102A43] text-lg">Belum Ada Target Tabungan</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Mulai susun tujuan finansialmu, pantau perkembangannya, dan capai impianmu tepat waktu.</p>
                <button onclick="openGoalModal()" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#1F4E79] px-4 py-2.5 text-xs font-bold text-white shadow-md hover:bg-[#163859]">
                    + Buat Target Pertama
                </button>
            </div>
        `;
        return;
    }

    container.innerHTML = goalsList.map(g => {
        const pct = Math.min(100, Math.round(((g.saved_amount || 0) / (g.price || 1)) * 100));
        const isDone = g.status === 'completed' || pct >= 100;
        const statusBadge = isDone 
            ? '<span class="badge bg-emerald-100 text-emerald-700">Tercapai 🎉</span>'
            : '<span class="badge bg-blue-100 text-blue-800">Berjalan</span>';

        const targetDateFmt = g.target_date ? new Date(g.target_date).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '—';

        return `
            <div class="card p-6 flex flex-col justify-between border-t-4 ${isDone ? 'border-t-emerald-500' : 'border-t-[#1F4E79]'} hover:shadow-lg transition">
                <div>
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-extrabold text-[#102A43] text-base line-clamp-1">${g.name}</h3>
                        ${statusBadge}
                    </div>
                    <div class="mt-4 flex items-baseline justify-between text-xs">
                        <span class="text-slate-500">Terkumpul: <b class="text-slate-800">${fmtRp(g.saved_amount)}</b></span>
                        <span class="font-extrabold text-[#1F4E79]">${pct}%</span>
                    </div>
                    <div class="h-2.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                        <div class="h-full rounded-full ${isDone ? 'bg-emerald-500' : 'bg-[#19B5A5]'}" style="width: ${pct}%"></div>
                    </div>
                    <div class="mt-3 flex justify-between text-[11px] text-slate-400">
                        <span>Target: ${fmtRp(g.price)}</span>
                        <span>Tempo: ${targetDateFmt}</span>
                    </div>
                    ${g.monthly_amount ? `<div class="mt-2 text-[11px] text-[#148B80] bg-teal-50 px-2.5 py-1 rounded-md">Alokasi ideal: ${fmtRp(g.monthly_amount)} / bln</div>` : ''}
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                    <button onclick="openDepositModal('${g.id}', '${g.name}')" ${isDone ? 'disabled' : ''} class="flex-1 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 transition">
                        + Setor
                    </button>
                    <button onclick="openHistoryModal('${g.id}', '${g.name}')" class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                        Riwayat
                    </button>
                    <button onclick="editGoal('${g.id}')" class="px-2.5 py-2 text-xs font-bold text-[#1F4E79] hover:underline">
                        Edit
                    </button>
                    <button onclick="deleteGoal('${g.id}')" class="px-2 py-2 text-xs font-bold text-rose-500 hover:underline">
                        Hapus
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

function populateIncomes() {
    const sel = document.getElementById('deposit-income-id');
    sel.innerHTML = '<option value="">Pilih dompet...</option>';
    incomesList.forEach(inc => {
        const opt = document.createElement('option');
        opt.value = inc.id;
        opt.textContent = `${inc.name} (Saldo: ${fmtRp(inc.balance || 0)})`;
        sel.appendChild(opt);
    });
}

// 2. Simulator Logic
document.getElementById('sim-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-sim');
    btn.textContent = 'Menghitung...';
    btn.disabled = true;

    try {
        const res = await authFetch('/api/v1/savings-goals/simulate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                price: parseInt(document.getElementById('sim-price').value, 10),
                target_date: document.getElementById('sim-target-date').value,
                saved_amount: parseInt(document.getElementById('sim-saved-amount').value || 0, 10)
            })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menghitung simulasi.');
        }

        const data = await res.json();
        const r = data.data;

        document.getElementById('sim-res-monthly').textContent = `${fmtRp(r.monthly_amount)} / bulan`;
        const warnBox = document.getElementById('sim-res-warning');
        if (r.warning) {
            warnBox.textContent = `⚠️ ${r.warning}`;
            warnBox.classList.remove('hidden');
        } else {
            warnBox.classList.add('hidden');
        }
        document.getElementById('sim-result').classList.remove('hidden');
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Hitung Simulasi';
        btn.disabled = false;
    }
});

// 3. Goal Modal Logic
function openGoalModal() {
    editGoalId = null;
    document.getElementById('goal-modal-title').textContent = 'Target Tabungan Baru';
    document.getElementById('goal-form').reset();
    document.getElementById('goal-modal').classList.remove('hidden');
}

function closeGoalModal() {
    document.getElementById('goal-modal').classList.add('hidden');
}

function editGoal(id) {
    const g = goalsList.find(i => i.id === id);
    if (!g) return;
    editGoalId = id;
    document.getElementById('goal-modal-title').textContent = 'Edit Target Tabungan';
    document.getElementById('goal-name').value = g.name;
    document.getElementById('goal-price').value = g.price;
    document.getElementById('goal-target-date').value = g.target_date ? g.target_date.slice(0, 10) : '';
    document.getElementById('goal-modal').classList.remove('hidden');
}

document.getElementById('goal-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-goal');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const payload = {
        name: document.getElementById('goal-name').value.trim(),
        price: parseInt(document.getElementById('goal-price').value, 10),
        target_date: document.getElementById('goal-target-date').value
    };

    try {
        const url = editGoalId ? `/api/v1/savings-goals/${editGoalId}` : '/api/v1/savings-goals';
        const method = editGoalId ? 'PATCH' : 'POST';

        const res = await authFetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menyimpan target.');
        }

        closeGoalModal();
        loadGoals();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Simpan Target';
        btn.disabled = false;
    }
});

async function deleteGoal(id) {
    if (!confirm('Hapus target tabungan ini?')) return;
    try {
        const res = await authFetch(`/api/v1/savings-goals/${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error('Gagal menghapus.');
        loadGoals();
    } catch (err) {
        alert(err.message);
    }
}

// 4. Deposit Modal Logic
function openDepositModal(goalId, goalName) {
    activeGoalId = goalId;
    document.getElementById('deposit-goal-name').textContent = `Target: ${goalName}`;
    document.getElementById('deposit-date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('deposit-amount').value = '';
    document.getElementById('deposit-note').value = '';
    document.getElementById('deposit-modal').classList.remove('hidden');
}

function closeDepositModal() {
    document.getElementById('deposit-modal').classList.add('hidden');
}

document.getElementById('deposit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-deposit');
    btn.textContent = 'Menyetor...';
    btn.disabled = true;

    const payload = {
        income_id: document.getElementById('deposit-income-id').value,
        amount: parseInt(document.getElementById('deposit-amount').value, 10),
        deposited_at: document.getElementById('deposit-date').value,
        note: document.getElementById('deposit-note').value.trim() || null,
        client_id: crypto.randomUUID()
    };

    try {
        const res = await authFetch(`/api/v1/savings-goals/${activeGoalId}/deposits`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal melakukan setoran.');
        }

        alert('Setoran berhasil ditambahkan!');
        closeDepositModal();
        loadGoals();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Konfirmasi Setor';
        btn.disabled = false;
    }
});

// 5. History Modal Logic
async function openHistoryModal(goalId, goalName) {
    document.getElementById('history-goal-name').textContent = `Target: ${goalName}`;
    const box = document.getElementById('history-container');
    box.innerHTML = '<div class="text-center text-sm text-slate-400 py-6">Memuat riwayat...</div>';
    document.getElementById('history-modal').classList.remove('hidden');

    try {
        const res = await authFetch(`/api/v1/savings-goals/${goalId}/deposits`);
        const json = await res.json();
        const list = json.data || [];

        if (!list.length) {
            box.innerHTML = '<div class="text-center text-xs text-slate-400 py-6">Belum ada riwayat setoran untuk target ini.</div>';
            return;
        }

        box.innerHTML = list.map(d => {
            const dt = d.deposited_at ? new Date(d.deposited_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '-';
            return `
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                    <div>
                        <div class="font-extrabold text-slate-800">${dt}</div>
                        <div class="text-slate-500">${d.note || 'Setoran tabungan'}</div>
                    </div>
                    <div class="font-extrabold text-emerald-600 text-sm">+${fmtRp(d.amount)}</div>
                </div>
            `;
        }).join('');
    } catch (err) {
        box.innerHTML = '<div class="text-center text-xs text-rose-500 py-4">Gagal memuat riwayat.</div>';
    }
}

function closeHistoryModal() {
    document.getElementById('history-modal').classList.add('hidden');
}

loadGoals();
</script>
@endpush
