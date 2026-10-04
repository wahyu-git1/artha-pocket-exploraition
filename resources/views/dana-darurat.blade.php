@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Jaring Pengaman Finansial ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Dana Darurat</h1>
        <p class="text-sm text-slate-500 mt-2">Lindungi diri dan keluarga dari situasi tak terduga dengan cadangan likuid yang terukur.</p>
    </div>
    <div id="fund-actions-header" class="hidden flex items-center gap-3">
        <button onclick="openDepositModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/15 transition hover:bg-emerald-700">
            <span class="text-lg leading-none">+</span> Setor Dana
        </button>
        <button onclick="openWithdrawModal()" class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-bold text-rose-600 transition hover:bg-rose-100">
            <span class="text-lg leading-none">-</span> Tarik Dana
        </button>
    </div>
</div>

<div id="ef-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<!-- 1. SETUP WIZARD (Jika belum ada dana darurat) -->
<div id="setup-section" class="hidden card p-8 border-t-4 border-t-[#1F4E79] max-w-3xl mx-auto">
    <div class="text-center max-w-lg mx-auto mb-8">
        <div class="w-16 h-16 rounded-2xl bg-teal-50 text-[#19B5A5] flex items-center justify-center text-3xl mx-auto mb-4">
            🛡️
        </div>
        <h2 class="text-2xl font-extrabold text-[#102A43]">Bangun Pondasi Dana Darurat</h2>
        <p class="text-sm text-slate-500 mt-2">Sistem AI CatatDuit merekomendasikan target ideal berdasarkan profil pengeluaran bulananmu.</p>
    </div>

    <!-- AI Suggestion Box -->
    <div id="rec-box" class="bg-[#F4F8FC] border border-slate-200 rounded-2xl p-5 mb-6">
        <div class="flex items-center gap-2 text-xs font-bold text-[#1F4E79] uppercase tracking-wider mb-2">
            <span>⚡</span> Rekomendasi Pintar AI
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
            <div class="p-3 bg-white rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400">Rata-rata Pengeluaran</span>
                <div id="rec-avg-expense" class="text-base font-extrabold text-[#102A43] mt-1">Rp0</div>
            </div>
            <div class="p-3 bg-white rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400">Proteksi Disarankan</span>
                <div id="rec-months" class="text-base font-extrabold text-[#19B5A5] mt-1">6 Bulan</div>
            </div>
            <div class="p-3 bg-white rounded-xl border border-slate-100">
                <span class="text-xs text-slate-400">Rekomendasi Target</span>
                <div id="rec-target" class="text-base font-extrabold text-emerald-600 mt-1">Rp0</div>
            </div>
        </div>
    </div>

    <form id="setup-form" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Proteksi Waktu (Bulan)</label>
                <select id="setup-multiplier" onchange="recalculateSetup()" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold outline-none focus:bg-white focus:border-[#19B5A5]">
                    <option value="3">3 Bulan (Minimal Single)</option>
                    <option value="6" selected>6 Bulan (Standar Aman)</option>
                    <option value="9">9 Bulan (Keluarga Kecil)</option>
                    <option value="12">12 Bulan (Proteksi Maksimal)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Rata-rata Pengeluaran Bulanan (Rp)</label>
                <input id="setup-avg-expense" type="number" min="100000" oninput="recalculateSetup()" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Target Total Dana Darurat (Rp)</label>
                <input id="setup-target-amount" type="number" min="500000" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-extrabold text-[#102A43] outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Rencana Pengumpulan (Bulan)</label>
                <input id="setup-plan-months" type="number" min="1" max="60" value="12" oninput="recalculateSetup()" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
        </div>

        <div class="p-4 rounded-xl bg-teal-50 border border-teal-100 flex justify-between items-center text-xs text-[#148B80]">
            <span>Estimasi Alokasi Tabungan Bulanan:</span>
            <b id="setup-monthly-amount-preview" class="text-base font-extrabold">Rp0 / bulan</b>
        </div>

        <button type="submit" id="btn-save-setup" class="w-full mt-4 py-3.5 rounded-xl font-bold text-sm text-white bg-[#1F4E79] hover:bg-[#163859] transition shadow-lg shadow-blue-900/15">
            Aktifkan Dana Darurat Sekarang
        </button>
    </form>
</div>

<!-- 2. ACTIVE DASHBOARD (Jika dana darurat sudah ada) -->
<div id="active-section" class="hidden space-y-8">
    <!-- Big Progress Card -->
    <div class="card p-7 border-t-4 border-t-emerald-500">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <span class="badge bg-emerald-100 text-emerald-800" id="fund-status-badge">Aktif</span>
                <div class="text-xs text-slate-400 mt-2 font-semibold uppercase tracking-wider">Total Terkumpul</div>
                <div id="fund-saved-amount" class="text-4xl font-extrabold text-[#102A43] mt-1">Rp0</div>
            </div>
            <div class="text-left lg:text-right">
                <div class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Target Perlindungan</div>
                <div id="fund-target-amount" class="text-2xl font-extrabold text-slate-700 mt-1">Rp0</div>
                <div id="fund-protection-desc" class="text-xs text-slate-400 mt-1">Cakupan: 6 bulan biaya hidup</div>
            </div>
        </div>

        <div class="mt-6">
            <div class="flex justify-between text-xs font-bold text-slate-600 mb-2">
                <span>Progress Perlindungan</span>
                <span id="fund-progress-pct" class="text-emerald-600">0%</span>
            </div>
            <div class="h-3.5 bg-slate-100 rounded-full overflow-hidden">
                <div id="fund-progress-bar" class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: 0%"></div>
            </div>
        </div>

        <div class="mt-6 pt-5 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs text-slate-500">
            <div>
                <span>Target Setoran Bulanan:</span>
                <div id="fund-monthly-target" class="font-extrabold text-slate-800 text-sm mt-0.5">Rp0</div>
            </div>
            <div>
                <span>Rencana Pengumpulan:</span>
                <div id="fund-plan-months" class="font-extrabold text-slate-800 text-sm mt-0.5">12 Bulan</div>
            </div>
            <div class="flex sm:justify-end items-center">
                <button onclick="openEditTargetModal()" class="text-xs font-bold text-[#1F4E79] hover:underline">
                    ⚙ Perbarui Target
                </button>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi Dana Darurat -->
    <div class="card overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="font-extrabold text-[#102A43]">Riwayat Mutasi Dana Darurat</h3>
                <p class="text-xs text-slate-400 mt-0.5">Log setoran dan penarikan yang pernah dilakukan</p>
            </div>
            <span id="tx-count" class="text-xs font-bold text-slate-400">0 Transaksi</span>
        </div>

        <div class="table-wrap overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">Tipe</th>
                        <th class="px-5 py-3 text-left">Keterangan / Alasan</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody id="tx-tbody">
                    <tr><td colspan="4" class="p-6 text-center text-xs text-slate-400">Memuat mutasi...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL SETOR DANA -->
<div id="deposit-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <h3 class="font-extrabold text-lg text-[#102A43]">Setor Dana Darurat 🛡️</h3>
            <button onclick="closeDepositModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="deposit-form" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Dompet Sumber Dana</label>
                <select id="dep-income-id" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
                    <option value="">Pilih dompet...</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Setoran (Rp)</label>
                <input id="dep-amount" type="number" min="1000" required placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-extrabold text-emerald-600 outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal</label>
                <input id="dep-date" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Sumber (Opsional)</label>
                <input id="dep-reason" type="text" placeholder="Mis. Alokasi gaji bulanan" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closeDepositModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-save-dep" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-md shadow-emerald-700/15">Konfirmasi Setor</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL TARIK DANA -->
<div id="withdraw-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-lg text-rose-600">Tarik Dana Darurat ⚠️</h3>
                <p class="text-xs text-slate-500 mt-0.5">Gunakan hanya untuk kebutuhan mendesak</p>
            </div>
            <button onclick="closeWithdrawModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="withdraw-form" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nominal Penarikan (Rp)</label>
                <input id="wd-amount" type="number" min="1000" required placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-extrabold text-rose-500 outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal</label>
                <input id="wd-date" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penarikan (Wajib)</label>
                <textarea id="wd-reason" required rows="2" placeholder="Mis. Biaya medis mendesak, servis motor mogok..." class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]"></textarea>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closeWithdrawModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-save-wd" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-rose-600 hover:bg-rose-700 transition shadow-md shadow-rose-700/15">Tarik Dana</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
if (!token) window.location.href = '/login';

let currentFund = null;
let incomesList = [];

const fmtRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(num || 0));

function showError(msg) {
    const el = document.getElementById('ef-error');
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

async function init() {
    showError('');
    try {
        const [fundRes, incRes] = await Promise.all([
            authFetch('/api/v1/emergency-fund'),
            authFetch('/api/v1/incomes')
        ]);

        if (incRes) {
            const incJson = await incRes.json();
            incomesList = incJson.data || [];
            populateIncomes();
        }

        if (fundRes && fundRes.ok) {
            const fundJson = await fundRes.json();
            currentFund = fundJson.data;
            showActiveDashboard();
        } else {
            // Belum setup -> panggil recommendation
            showSetupWizard();
        }
    } catch (err) {
        console.error(err);
        showError('Gagal memuat informasi dana darurat.');
    }
}

async function showSetupWizard() {
    document.getElementById('setup-section').classList.remove('hidden');
    document.getElementById('active-section').classList.add('hidden');
    document.getElementById('fund-actions-header').classList.add('hidden');

    try {
        const recRes = await authFetch('/api/v1/emergency-fund/recommendation', { method: 'POST' });
        if (recRes && recRes.ok) {
            const recJson = await recRes.json();
            const r = recJson.data || {};

            const avgExp = r.avg_monthly_expense || 3000000;
            const mult = r.recommended_multiplier || 6;
            const tgt = r.target_amount || (avgExp * mult);

            document.getElementById('rec-avg-expense').textContent = fmtRp(avgExp);
            document.getElementById('rec-months').textContent = `${mult} Bulan`;
            document.getElementById('rec-target').textContent = fmtRp(tgt);

            document.getElementById('setup-multiplier').value = mult;
            document.getElementById('setup-avg-expense').value = avgExp;
            document.getElementById('setup-target-amount').value = tgt;
            recalculateSetup();
        }
    } catch (err) {
        console.error(err);
    }
}

function recalculateSetup() {
    const mult = parseInt(document.getElementById('setup-multiplier').value, 10);
    const avg = parseInt(document.getElementById('setup-avg-expense').value || 0, 10);
    const plan = parseInt(document.getElementById('setup-plan-months').value || 1, 10);

    const target = avg * mult;
    document.getElementById('setup-target-amount').value = target;

    const monthly = plan > 0 ? Math.round(target / plan) : target;
    document.getElementById('setup-monthly-amount-preview').textContent = `${fmtRp(monthly)} / bulan`;
}

document.getElementById('setup-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-setup');
    btn.textContent = 'Mengaktifkan...';
    btn.disabled = true;

    const mult = parseInt(document.getElementById('setup-multiplier').value, 10);
    const avg = parseInt(document.getElementById('setup-avg-expense').value, 10);
    const tgt = parseInt(document.getElementById('setup-target-amount').value, 10);
    const plan = parseInt(document.getElementById('setup-plan-months').value, 10);
    const monthly = Math.round(tgt / plan);

    try {
        const res = await authFetch('/api/v1/emergency-fund', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                multiplier: mult,
                avg_monthly_expense: avg,
                target_amount: tgt,
                plan_months: plan,
                monthly_amount: monthly
            })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal mengaktifkan dana darurat.');
        }

        alert('Dana Darurat berhasil diaktifkan!');
        init();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Aktifkan Dana Darurat Sekarang';
        btn.disabled = false;
    }
});

function showActiveDashboard() {
    document.getElementById('setup-section').classList.add('hidden');
    document.getElementById('active-section').classList.remove('hidden');
    document.getElementById('fund-actions-header').classList.remove('hidden');

    const f = currentFund;
    document.getElementById('fund-saved-amount').textContent = fmtRp(f.saved_amount);
    document.getElementById('fund-target-amount').textContent = fmtRp(f.target_amount);
    document.getElementById('fund-protection-desc').textContent = `Cakupan: ${f.multiplier} bulan biaya hidup`;

    const pct = Math.min(100, Math.round(((f.saved_amount || 0) / (f.target_amount || 1)) * 100));
    document.getElementById('fund-progress-pct').textContent = `${pct}%`;
    document.getElementById('fund-progress-bar').style.width = `${pct}%`;

    document.getElementById('fund-monthly-target').textContent = `${fmtRp(f.monthly_amount)} / bulan`;
    document.getElementById('fund-plan-months').textContent = `${f.plan_months} Bulan`;

    loadTransactions();
}

async function loadTransactions() {
    try {
        const res = await authFetch('/api/v1/emergency-fund/transactions');
        const json = await res.json();
        const list = json.data || [];
        const tbody = document.getElementById('tx-tbody');
        document.getElementById('tx-count').textContent = `${list.length} Mutasi`;

        if (!list.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="p-8 text-center text-xs text-slate-400">Belum ada riwayat mutasi dana darurat.</td></tr>';
            return;
        }

        tbody.innerHTML = list.map(tx => {
            const isDep = tx.type === 'deposit';
            const badge = isDep 
                ? '<span class="badge bg-emerald-50 text-emerald-700">Setoran</span>'
                : '<span class="badge bg-rose-50 text-rose-600">Penarikan</span>';
            const amountDisp = isDep
                ? `<span class="font-extrabold text-emerald-600">+${fmtRp(tx.amount)}</span>`
                : `<span class="font-extrabold text-rose-500">-${fmtRp(tx.amount)}</span>`;
            const dateFmt = tx.occurred_at ? new Date(tx.occurred_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '-';

            return `
                <tr class="border-t border-slate-100 hover:bg-slate-50/60">
                    <td class="px-5 py-3.5 text-slate-500 text-xs">${dateFmt}</td>
                    <td class="px-5 py-3.5">${badge}</td>
                    <td class="px-5 py-3.5 font-medium text-slate-800">${tx.reason || (isDep ? 'Setoran Dana' : 'Penarikan Dana')}</td>
                    <td class="px-5 py-3.5 text-right whitespace-nowrap">${amountDisp}</td>
                </tr>
            `;
        }).join('');
    } catch (err) {
        console.error(err);
    }
}

function populateIncomes() {
    const sel = document.getElementById('dep-income-id');
    sel.innerHTML = '<option value="">Pilih dompet...</option>';
    incomesList.forEach(inc => {
        const opt = document.createElement('option');
        opt.value = inc.id;
        opt.textContent = `${inc.name} (Saldo: ${fmtRp(inc.balance || 0)})`;
        sel.appendChild(opt);
    });
}

function openDepositModal() {
    document.getElementById('dep-date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('dep-amount').value = '';
    document.getElementById('dep-reason').value = '';
    document.getElementById('deposit-modal').classList.remove('hidden');
}

function closeDepositModal() {
    document.getElementById('deposit-modal').classList.add('hidden');
}

document.getElementById('deposit-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-dep');
    btn.textContent = 'Menyetor...';
    btn.disabled = true;

    try {
        const res = await authFetch('/api/v1/emergency-fund/deposits', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                income_id: document.getElementById('dep-income-id').value,
                amount: parseInt(document.getElementById('dep-amount').value, 10),
                occurred_at: document.getElementById('dep-date').value,
                reason: document.getElementById('dep-reason').value.trim() || null,
                client_id: crypto.randomUUID()
            })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menyetor dana darurat.');
        }

        alert('Setoran dana darurat berhasil dicatat!');
        closeDepositModal();
        init();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Konfirmasi Setor';
        btn.disabled = false;
    }
});

function openWithdrawModal() {
    document.getElementById('wd-date').value = new Date().toISOString().slice(0, 10);
    document.getElementById('wd-amount').value = '';
    document.getElementById('wd-reason').value = '';
    document.getElementById('withdraw-modal').classList.remove('hidden');
}

function closeWithdrawModal() {
    document.getElementById('withdraw-modal').classList.add('hidden');
}

document.getElementById('withdraw-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-wd');
    btn.textContent = 'Memproses...';
    btn.disabled = true;

    try {
        const res = await authFetch('/api/v1/emergency-fund/withdrawals', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                amount: parseInt(document.getElementById('wd-amount').value, 10),
                occurred_at: document.getElementById('wd-date').value,
                reason: document.getElementById('wd-reason').value.trim(),
                client_id: crypto.randomUUID()
            })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal melakukan penarikan.');
        }

        alert('Penarikan dana darurat berhasil dicatat.');
        closeWithdrawModal();
        init();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Tarik Dana';
        btn.disabled = false;
    }
});

init();
</script>
@endpush
