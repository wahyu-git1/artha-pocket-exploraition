<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title ?? 'CatatDuit' }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{--navy:#1F4E79;--teal:#19B5A5;--ink:#102A43;--muted:#6B7C93;--line:#E5ECF3;--space:#071A35}
*{box-sizing:border-box}body{margin:0;font-family:Inter,system-ui;background:#F4F8FC;color:var(--ink)}
.cosmic{background:radial-gradient(circle at 85% 15%,#23538a 0,transparent 32%),radial-gradient(circle at 20% 0,#123b69 0,transparent 35%),linear-gradient(135deg,#071A35,#0b2b50 55%,#1F4E79);position:relative;overflow:hidden}.cosmic:before{content:'';position:absolute;inset:0;background-image:radial-gradient(#fff 1px,transparent 1px);background-size:42px 42px;opacity:.18;animation:drift 18s linear infinite}.cosmic>*{position:relative}@keyframes drift{to{background-position:42px 42px}}@keyframes float{50%{transform:translateY(-9px)}}.float{animation:float 5s ease-in-out infinite}.glass{background:rgba(255,255,255,.11);border:1px solid rgba(255,255,255,.17);backdrop-filter:blur(12px)}.card{background:#fff;border:1px solid var(--line);border-radius:16px;box-shadow:0 8px 26px rgba(20,54,88,.06)}.sidebar{width:250px}.nav-item{display:flex;align-items:center;gap:12px;border-radius:12px;padding:11px 14px;color:#B9C8D8;font-size:13px;font-weight:600;transition:all .15s}.nav-item:hover,.nav-active{background:rgba(25,181,165,.15);color:#fff}.kpi{min-height:128px}.badge{font-size:11px;border-radius:999px;padding:4px 8px;font-weight:700}.table-wrap{overflow-x:auto}@media(max-width:900px){.sidebar{width:78px}.nav-label,.brand-label,.side-footer{display:none}.nav-item{justify-content:center}.main-grid{grid-template-columns:1fr!important}.kpis{grid-template-columns:repeat(2,1fr)!important}}
</style>
</head>
<body>
<div class="min-h-screen flex">
    <aside class="sidebar bg-[#071A35] text-white p-5 flex flex-col shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 mb-9">
            <div class="w-10 h-10 rounded-xl bg-[#19B5A5] flex items-center justify-center text-xl shadow-lg shadow-teal-500/20">✦</div>
            <div class="brand-label">
                <div class="font-extrabold text-base">Catat<span class="text-[#19B5A5]">Duit</span></div>
                <div class="text-[10px] text-slate-400">FINANCE ORBIT</div>
            </div>
        </a>

        <nav class="space-y-1 flex-1">
            @php($links=[
                ['dashboard','⌂','Dashboard'],
                ['transaksi','▤','Transaksi'],
                ['laporan','◔','Laporan'],
                ['kategori','⊙','Kategori'],
                ['target','◎','Target'],
                ['dana-darurat','◈','Dana Darurat'],
                ['alokasi','◫','Alokasi'],
                ['investasi','◇','Investasi'],
                ['pengaturan','⚙','Pengaturan']
            ])
            @foreach($links as $link)
                <a href="{{ route($link[0]) }}" class="nav-item {{ request()->routeIs($link[0])?'nav-active':'' }}">
                    <span class="text-lg">{{ $link[1] }}</span>
                    <span class="nav-label">{{ $link[2] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="side-footer glass rounded-xl p-3 text-xs text-slate-300 mb-3">
            🚀 Ruang finansialmu<br><span class="text-[#19B5A5]">Tetap pada orbit.</span>
        </div>

        <button onclick="logout()" class="nav-item text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-xl p-2.5 flex items-center gap-2 text-xs font-bold transition">
            <span>🚪</span><span class="nav-label">Keluar</span>
        </button>
    </aside>

    <main class="flex-1 min-w-0">
        <header class="h-[76px] bg-white border-b border-slate-200 px-8 flex items-center gap-5 sticky top-0 z-30">
            <div id="header-date" class="flex items-center gap-2 text-sm font-bold text-slate-700 whitespace-nowrap">
                Oktober 2026
            </div>

            <!-- Smart Entry Top Bar -->
            <div class="flex-1 max-w-2xl relative">
                <span class="absolute left-4 top-3 text-[#19B5A5]">✦</span>
                <input id="header-smart-entry" class="w-full rounded-xl bg-[#F4F8FC] border border-slate-200 py-3 pl-11 pr-12 text-sm outline-none transition focus:border-[#19B5A5] focus:bg-white focus:ring-2 focus:ring-[#19B5A5]/15" placeholder="Ketik, mis. beli bakso 10k lalu tekan Enter">
                <button type="button" onclick="submitSmartEntryHeader()" class="absolute right-3 top-2.5 p-1 rounded-lg text-slate-400 hover:text-[#19B5A5]">↵</button>
            </div>

            <!-- User Profile Header -->
            <div class="flex items-center gap-3 ml-auto">
                <div class="text-right hidden sm:block">
                    <div id="header-user-name" class="text-sm font-bold text-slate-800">Pengguna</div>
                    <div id="header-user-email" class="text-[11px] text-slate-400">Navigator Finansial</div>
                </div>
                <div id="header-user-avatar" class="w-10 h-10 rounded-full bg-[#1F4E79] text-white flex items-center justify-center font-bold text-sm shadow-sm cursor-pointer" onclick="logout()" title="Klik untuk keluar">
                    CD
                </div>
            </div>
        </header>

        <div class="p-8">
            @yield('content')
        </div>
    </main>
</div>

<!-- MODAL SMART ENTRY DESKTOP -->
<div id="se-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="font-extrabold text-lg text-[#102A43]">Konfirmasi Smart Entry ⚡</h3>
                <p class="text-xs text-slate-400 mt-0.5">Hasil analisis AI dari teksmu</p>
            </div>
            <button onclick="closeSmartModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <div id="se-modal-items" class="space-y-3 mb-6 max-h-64 overflow-y-auto pr-1">
            <!-- Dynamic Parsed Items -->
        </div>

        <div class="flex justify-between items-center bg-[#F4F8FC] p-4 rounded-xl mb-6">
            <span class="text-sm font-semibold text-slate-600">Total Nominal</span>
            <span id="se-modal-total" class="text-xl font-extrabold text-[#1F4E79]">Rp0</span>
        </div>

        <div class="flex gap-3">
            <button onclick="closeSmartModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
            <button id="se-btn-save" onclick="saveSmartEntryItems()" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-[#1F4E79] hover:bg-[#163859] transition shadow-md shadow-blue-900/15">Simpan Semua</button>
        </div>
    </div>
</div>

<script>
const appToken = localStorage.getItem('token');
if (!appToken && !['/login', '/register', '/'].includes(window.location.pathname)) {
    window.location.href = '/login';
}

function logout() {
    if (confirm('Keluar dari akun CatatDuit?')) {
        fetch('/api/v1/auth/logout', {
            method: 'POST',
            headers: { 'Authorization': `Bearer ${appToken}` }
        }).finally(() => {
            localStorage.removeItem('token');
            window.location.href = '/login';
        });
    }
}

// User Info Setup
if (appToken) {
    fetch('/api/v1/users/me', {
        headers: { 'Authorization': `Bearer ${appToken}`, 'Accept': 'application/json' }
    }).then(res => {
        if (res.status === 401) {
            localStorage.removeItem('token');
            window.location.href = '/login';
            return null;
        }
        return res.json();
    }).then(data => {
        if (data && data.data) {
            const user = data.data;
            const nameEl = document.getElementById('header-user-name');
            const emailEl = document.getElementById('header-user-email');
            const avatarEl = document.getElementById('header-user-avatar');
            if (nameEl) nameEl.textContent = user.name;
            if (emailEl) emailEl.textContent = user.email || 'Navigator Finansial';
            if (avatarEl) {
                const initials = user.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
                avatarEl.textContent = initials || 'CD';
            }
        }
    }).catch(e => console.error(e));
}

// Header Date Display
const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const today = new Date();
const dateHeader = document.getElementById('header-date');
if (dateHeader) dateHeader.textContent = `${monthNames[today.getMonth()]} ${today.getFullYear()}`;

// Smart Entry Header Integration
let parsedHeaderItems = [];

async function submitSmartEntryHeader() {
    const input = document.getElementById('header-smart-entry');
    const text = input.value.trim();
    if (!text) return;

    input.disabled = true;
    input.placeholder = 'Menganalisis teks dengan AI...';

    try {
        const res = await fetch('/api/v1/smart-entry/parse', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${appToken}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ text })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            alert(err.message || 'Gagal menganalisis input.');
            return;
        }

        const data = await res.json();
        parsedHeaderItems = data.data?.items || [];
        if (parsedHeaderItems.length === 0) {
            alert('Tidak ditemukan item pengeluaran pada teks tersebut.');
            return;
        }

        openSmartModal(parsedHeaderItems);
        input.value = '';
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        input.disabled = false;
        input.placeholder = 'Ketik, mis. beli bakso 10k lalu tekan Enter';
    }
}

document.getElementById('header-smart-entry')?.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        submitSmartEntryHeader();
    }
});

function openSmartModal(items) {
    const container = document.getElementById('se-modal-items');
    let total = 0;

    container.innerHTML = items.map((it, idx) => {
        total += Number(it.amount || 0);
        return `
            <div class="flex justify-between items-center p-3 rounded-xl border border-slate-100 bg-slate-50">
                <div>
                    <p class="font-bold text-sm text-slate-800">${it.item}</p>
                    <p class="text-xs text-slate-400">${it.category_name || 'Umum'} • ${it.type === 'income' ? 'Pemasukan' : 'Pengeluaran'}</p>
                </div>
                <span class="font-bold text-sm ${it.type === 'income' ? 'text-emerald-600' : 'text-rose-500'}">
                    ${it.type === 'income' ? '+' : '-'}Rp${parseInt(it.amount || 0).toLocaleString('id-ID')}
                </span>
            </div>
        `;
    }).join('');

    document.getElementById('se-modal-total').textContent = 'Rp' + total.toLocaleString('id-ID');
    document.getElementById('se-modal').classList.remove('hidden');
}

function closeSmartModal() {
    document.getElementById('se-modal').classList.add('hidden');
}

async function saveSmartEntryItems() {
    const btn = document.getElementById('se-btn-save');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    try {
        const res = await fetch('/api/v1/smart-entry/submit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${appToken}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: parsedHeaderItems })
        });

        if (res.ok) {
            let expTotal = 0, incTotal = 0;
            parsedHeaderItems.forEach(i => {
                if (i.type === 'income') incTotal += (parseInt(i.amount) || 0);
                else expTotal += (parseInt(i.amount) || 0);
            });
            let notifMsg = '';
            if (parsedHeaderItems.length === 1) {
                const item = parsedHeaderItems[0];
                const label = item.type === 'income' ? 'Pemasukan' : 'Pengeluaran';
                notifMsg = `Berhasil mencatat ${label} "${item.item}" sebesar Rp${(parseInt(item.amount) || 0).toLocaleString('id-ID')}!`;
            } else {
                const parts = [];
                if (expTotal > 0) parts.push(`Pengeluaran Rp${expTotal.toLocaleString('id-ID')}`);
                if (incTotal > 0) parts.push(`Pemasukan Rp${incTotal.toLocaleString('id-ID')}`);
                notifMsg = `Berhasil mencatat ${parsedHeaderItems.length} transaksi (${parts.join(', ')})!`;
            }
            alert(notifMsg);
            closeSmartModal();
            window.location.reload();
        } else {
            const err = await res.json().catch(() => ({}));
            alert(err.message || 'Gagal menyimpan transaksi.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    } finally {
        btn.textContent = 'Simpan Semua';
        btn.disabled = false;
    }
}
</script>
@stack('scripts')
</body>
</html>