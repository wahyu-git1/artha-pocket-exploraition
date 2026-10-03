@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Manajemen Pos Keuangan ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Alokasi Anggaran</h1>
        <p class="text-sm text-slate-500 mt-2">Bagi penghasilanmu ke pos-pos terencana agar pengeluaran tetap terkendali.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700">
            <span>Bulan:</span>
            <input id="selected-month" type="month" onchange="loadData()" class="outline-none text-xs font-bold text-[#1F4E79] bg-transparent">
        </div>
        <button onclick="openPlanModal()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#1F4E79] px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-900/15 transition hover:bg-[#163859]">
            ⚙ Buat / Sesuaikan Rencana
        </button>
    </div>
</div>

<div id="alloc-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<!-- TEMPLATES & REKOMENDASI PREVIEW -->
<div class="card p-6 mb-8 border-t-4 border-t-[#19B5A5]">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-base font-extrabold text-[#102A43] flex items-center gap-2">
                <span>✦</span> Template Alokasi Populer
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Pilih metode alokasi teruji atau sesuaikan dengan kebutuhanmu.</p>
        </div>
        <button onclick="openMappingModal()" class="text-xs font-bold text-[#1F4E79] hover:underline">
            📂 Kelola Mapping Kategori → Pos
        </button>
    </div>

    <div id="templates-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-400">Memuat template...</div>
    </div>
</div>

<!-- RINGKASAN REALISASI PER BUCKET -->
<div class="mb-4 flex items-center justify-between">
    <h2 class="text-lg font-extrabold text-[#102A43]">Realisasi Pos Anggaran Bulan Ini</h2>
    <span id="plan-status-badge" class="badge bg-slate-100 text-slate-500">Memeriksa rencana...</span>
</div>

<div id="summary-cards" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
    <div class="col-span-full card p-8 text-center text-xs text-slate-400">Memuat ringkasan alokasi...</div>
</div>

<!-- MODAL BUAT / SESUAIKAN RENCANA ALOKASI -->
<div id="plan-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-lg text-[#102A43]">Rencana Alokasi Anggaran</h3>
                <p class="text-xs text-slate-400 mt-0.5" id="modal-month-label">Bulan berjalan</p>
            </div>
            <button onclick="closePlanModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="plan-form" class="space-y-4">
            <div class="p-3 bg-teal-50 border border-teal-100 rounded-xl text-xs text-[#148B80]">
                💡 Total persentase dari keempat pos wajib bernilai tepat <b>100%</b>.
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">🥖</span>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Kebutuhan (Need)</div>
                            <div class="text-[10px] text-slate-400">Makan, sewa, tagihan listrik, dsb</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 w-24">
                        <input id="pct-need" type="number" min="0" max="100" value="50" oninput="updateTotalPct()" required class="w-full text-center rounded-lg border border-slate-200 bg-white py-1.5 text-xs font-bold outline-none focus:border-[#19B5A5]">
                        <span class="text-xs font-bold text-slate-500">%</span>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">☕</span>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Keinginan (Want)</div>
                            <div class="text-[10px] text-slate-400">Hiburan, belanja gaya hidup, ngopi</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 w-24">
                        <input id="pct-want" type="number" min="0" max="100" value="30" oninput="updateTotalPct()" required class="w-full text-center rounded-lg border border-slate-200 bg-white py-1.5 text-xs font-bold outline-none focus:border-[#19B5A5]">
                        <span class="text-xs font-bold text-slate-500">%</span>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">🏦</span>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Tabungan (Saving)</div>
                            <div class="text-[10px] text-slate-400">Dana darurat, target impian</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 w-24">
                        <input id="pct-saving" type="number" min="0" max="100" value="10" oninput="updateTotalPct()" required class="w-full text-center rounded-lg border border-slate-200 bg-white py-1.5 text-xs font-bold outline-none focus:border-[#19B5A5]">
                        <span class="text-xs font-bold text-slate-500">%</span>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">📈</span>
                        <div>
                            <div class="text-xs font-bold text-slate-800">Investasi (Investment)</div>
                            <div class="text-[10px] text-slate-400">Saham, reksa dana, emas</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 w-24">
                        <input id="pct-investment" type="number" min="0" max="100" value="10" oninput="updateTotalPct()" required class="w-full text-center rounded-lg border border-slate-200 bg-white py-1.5 text-xs font-bold outline-none focus:border-[#19B5A5]">
                        <span class="text-xs font-bold text-slate-500">%</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center p-3 rounded-xl bg-slate-100 text-xs font-bold">
                <span>Total Persentase:</span>
                <span id="total-pct-label" class="text-emerald-600 font-extrabold text-sm">100%</span>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closePlanModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-save-plan" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-[#1F4E79] hover:bg-[#163859] transition shadow-md shadow-blue-900/15">Simpan Alokasi</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL MAPPING KATEGORI KE BUCKET -->
<div id="mapping-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-xl rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-lg text-[#102A43]">Mapping Kategori ke Pos Anggaran</h3>
                <p class="text-xs text-slate-400 mt-0.5">Tentukan setiap kategori pengeluaran masuk ke pos mana</p>
            </div>
            <button onclick="closeMappingModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <div id="mapping-list" class="space-y-3 max-h-80 overflow-y-auto pr-1">
            <div class="text-center text-xs text-slate-400 py-6">Memuat kategori...</div>
        </div>

        <div class="pt-4 mt-3 border-t border-slate-100 flex justify-end gap-3">
            <button type="button" onclick="closeMappingModal()" class="px-5 py-2.5 rounded-xl font-bold text-xs text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
            <button type="button" onclick="saveCategoryMappings()" id="btn-save-map" class="px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-[#19B5A5] hover:bg-[#148B80] transition shadow-md shadow-teal-600/15">Simpan Mapping</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
if (!token) window.location.href = '/login';

let categoriesList = [];
let mappingsList = [];
let currentPlan = null;

const fmtRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(num || 0));

function showError(msg) {
    const el = document.getElementById('alloc-error');
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

// 1. Initial Setup
function getCurrentMonth() {
    const d = new Date();
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
}

async function init() {
    document.getElementById('selected-month').value = getCurrentMonth();
    await loadTemplates();
    await loadData();
}

async function loadTemplates() {
    try {
        const res = await authFetch('/api/v1/allocations/templates');
        if (!res || !res.ok) return;
        const json = await res.json();
        const templates = json.data || [];

        const container = document.getElementById('templates-container');
        container.innerHTML = templates.map(t => `
            <div class="p-4 rounded-xl border border-slate-200 bg-white hover:border-[#19B5A5] transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="font-extrabold text-[#102A43] text-sm">${t.name}</span>
                        <span class="badge bg-blue-50 text-blue-700">${t.code}</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">${t.description}</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        ${t.items.map(it => `
                            <span class="text-[11px] font-semibold bg-slate-50 border border-slate-100 px-2 py-0.5 rounded text-slate-600">
                                ${it.bucket.toUpperCase()}: ${it.percent}%
                            </span>
                        `).join('')}
                    </div>
                </div>
                <button type="button" onclick="applyTemplate('${t.code}', ${JSON.stringify(t.items).replace(/"/g, '&quot;')})" class="mt-4 w-full py-2 rounded-lg text-xs font-bold text-[#1F4E79] border border-[#1F4E79]/20 hover:bg-[#1F4E79] hover:text-white transition">
                    Gunakan Template Ini
                </button>
            </div>
        `).join('');
    } catch (err) {
        console.error(err);
    }
}

async function loadData() {
    const month = document.getElementById('selected-month').value;
    showError('');

    try {
        const [planRes, sumRes] = await Promise.all([
            authFetch(`/api/v1/allocations?month=${month}`),
            authFetch(`/api/v1/allocations/summary?month=${month}`)
        ]);

        const planJson = planRes && planRes.ok ? await planRes.json() : { data: null };
        currentPlan = planJson.data;

        const badge = document.getElementById('plan-status-badge');
        if (currentPlan) {
            badge.textContent = `Rencana Aktif (${currentPlan.template_code || 'Kustom'})`;
            badge.className = 'badge bg-teal-50 text-[#148B80] border border-teal-200';
        } else {
            badge.textContent = 'Belum Ada Rencana';
            badge.className = 'badge bg-amber-50 text-amber-700 border border-amber-200';
        }

        if (sumRes && sumRes.ok) {
            const sumJson = await sumRes.json();
            renderSummary(sumJson.data);
        }
    } catch (err) {
        console.error(err);
        showError('Gagal memuat ringkasan pos alokasi.');
    }
}

function renderSummary(summary) {
    const container = document.getElementById('summary-cards');
    if (!summary || !summary.buckets) {
        container.innerHTML = `
            <div class="col-span-full card p-8 text-center">
                <p class="text-xs text-slate-400">Belum ada data anggaran untuk bulan ini.</p>
                <button onclick="openPlanModal()" class="mt-3 text-xs font-bold text-[#1F4E79] hover:underline">
                    + Buat Rencana Alokasi Sekarang
                </button>
            </div>
        `;
        return;
    }

    const bucketMeta = {
        need: { name: 'Kebutuhan (Need)', icon: '🥖', color: 'border-l-blue-500' },
        want: { name: 'Keinginan (Want)', icon: '☕', color: 'border-l-amber-500' },
        saving: { name: 'Tabungan (Saving)', icon: '🏦', color: 'border-l-emerald-500' },
        investment: { name: 'Investasi (Investment)', icon: '📈', color: 'border-l-purple-500' }
    };

    const buckets = summary.buckets || {};
    const keys = ['need', 'want', 'saving', 'investment'];

    container.innerHTML = keys.map(k => {
        const b = buckets[k] || { budget: 0, actual: 0, percent: 0, remaining: 0 };
        const meta = bucketMeta[k] || { name: k, icon: '📁', color: 'border-l-slate-400' };
        const budget = b.budget || 0;
        const actual = b.actual || 0;
        const pctUsed = budget > 0 ? Math.round((actual / budget) * 100) : 0;
        const isOver = actual > budget && budget > 0;

        return `
            <div class="card p-5 border-l-4 ${meta.color} flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span class="font-bold flex items-center gap-1.5">${meta.icon} ${meta.name}</span>
                        <span class="badge ${isOver ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600'}">${pctUsed}%</span>
                    </div>

                    <div class="mt-3 text-xl font-extrabold text-[#102A43]">
                        ${fmtRp(actual)}
                    </div>
                    <div class="text-[11px] text-slate-400 mt-0.5">
                        Plafon: ${fmtRp(budget)} (${b.percent || 0}%)
                    </div>

                    <div class="h-2 bg-slate-100 rounded-full mt-3 overflow-hidden">
                        <div class="h-full rounded-full ${isOver ? 'bg-rose-500' : 'bg-[#19B5A5]'}" style="width: ${Math.min(100, pctUsed)}%"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between text-[11px]">
                    <span class="text-slate-400">${isOver ? 'Kelebihan:' : 'Sisa kuota:'}</span>
                    <b class="${isOver ? 'text-rose-500' : 'text-emerald-600'}">${fmtRp(Math.abs(b.remaining || (budget - actual)))}</b>
                </div>
            </div>
        `;
    }).join('');
}

// 2. Plan Modal & Template Application
function openPlanModal() {
    const month = document.getElementById('selected-month').value;
    document.getElementById('modal-month-label').textContent = `Target bulan: ${month}`;

    if (currentPlan && currentPlan.items) {
        currentPlan.items.forEach(it => {
            const input = document.getElementById(`pct-${it.bucket}`);
            if (input) input.value = it.percent;
        });
    }
    updateTotalPct();
    document.getElementById('plan-modal').classList.remove('hidden');
}

function closePlanModal() {
    document.getElementById('plan-modal').classList.add('hidden');
}

function applyTemplate(code, items) {
    items.forEach(it => {
        const input = document.getElementById(`pct-${it.bucket}`);
        if (input) input.value = it.percent;
    });
    openPlanModal();
}

function updateTotalPct() {
    const need = parseInt(document.getElementById('pct-need').value || 0, 10);
    const want = parseInt(document.getElementById('pct-want').value || 0, 10);
    const saving = parseInt(document.getElementById('pct-saving').value || 0, 10);
    const inv = parseInt(document.getElementById('pct-investment').value || 0, 10);
    const total = need + want + saving + inv;

    const label = document.getElementById('total-pct-label');
    label.textContent = `${total}%`;
    label.className = total === 100 ? 'text-emerald-600 font-extrabold text-sm' : 'text-rose-500 font-extrabold text-sm';
}

document.getElementById('plan-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const need = parseInt(document.getElementById('pct-need').value, 10);
    const want = parseInt(document.getElementById('pct-want').value, 10);
    const saving = parseInt(document.getElementById('pct-saving').value, 10);
    const inv = parseInt(document.getElementById('pct-investment').value, 10);

    if (need + want + saving + inv !== 100) {
        alert('Total persentase harus tepat 100%!');
        return;
    }

    const btn = document.getElementById('btn-save-plan');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const month = document.getElementById('selected-month').value;

    const payload = {
        month: month,
        items: [
            { bucket: 'need', percent: need },
            { bucket: 'want', percent: want },
            { bucket: 'saving', percent: saving },
            { bucket: 'investment', percent: inv }
        ]
    };

    try {
        const res = await authFetch('/api/v1/allocations', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menyimpan alokasi.');
        }

        alert('Alokasi anggaran berhasil diperbarui!');
        closePlanModal();
        loadData();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Simpan Alokasi';
        btn.disabled = false;
    }
});

// 3. Mapping Modal
async function openMappingModal() {
    document.getElementById('mapping-modal').classList.remove('hidden');
    const box = document.getElementById('mapping-list');
    box.innerHTML = '<div class="text-center text-xs text-slate-400 py-6">Memuat data kategori...</div>';

    try {
        const [catRes, mapRes] = await Promise.all([
            authFetch('/api/v1/categories?filter[type]=expense'),
            authFetch('/api/v1/allocations/category-mapping')
        ]);

        const catJson = await catRes.json();
        const mapJson = await mapRes.json();

        categoriesList = catJson.data || [];
        mappingsList = mapJson.data || [];

        box.innerHTML = categoriesList.map(cat => {
            const m = mappingsList.find(x => x.category_id === cat.id);
            const currentBucket = m ? m.bucket : 'need';

            return `
                <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">${cat.icon || '🛒'}</span>
                        <span class="font-bold text-slate-800">${cat.name}</span>
                    </div>
                    <select data-cat-id="${cat.id}" class="cat-bucket-select rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold outline-none focus:border-[#19B5A5]">
                        <option value="need" ${currentBucket === 'need' ? 'selected' : ''}>🥖 Kebutuhan (Need)</option>
                        <option value="want" ${currentBucket === 'want' ? 'selected' : ''}>☕ Keinginan (Want)</option>
                        <option value="saving" ${currentBucket === 'saving' ? 'selected' : ''}>🏦 Tabungan (Saving)</option>
                        <option value="investment" ${currentBucket === 'investment' ? 'selected' : ''}>📈 Investasi (Investment)</option>
                    </select>
                </div>
            `;
        }).join('');
    } catch (err) {
        box.innerHTML = '<div class="text-center text-xs text-rose-500 py-4">Gagal memuat kategori.</div>';
    }
}

function closeMappingModal() {
    document.getElementById('mapping-modal').classList.add('hidden');
}

async function saveCategoryMappings() {
    const btn = document.getElementById('btn-save-map');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const selects = document.querySelectorAll('.cat-bucket-select');
    const mappings = Array.from(selects).map(sel => ({
        category_id: sel.getAttribute('data-cat-id'),
        bucket: sel.value
    }));

    try {
        const res = await authFetch('/api/v1/allocations/category-mapping', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ mappings })
        });

        if (!res.ok) throw new Error('Gagal menyimpan mapping.');
        alert('Mapping kategori ke pos anggaran berhasil disimpan!');
        closeMappingModal();
        loadData();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Simpan Mapping';
        btn.disabled = false;
    }
}

init();
</script>
@endpush
