@extends('layouts.app')

@section('content')
<div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-5 mb-8">
    <div>
        <div class="text-sm text-slate-500">Konfigurasi Sistem ✦</div>
        <h1 class="text-3xl font-extrabold text-[#102A43] mt-1">Pengaturan</h1>
        <p class="text-sm text-slate-500 mt-2">Kelola profil identitasmu serta sumber rekening dan dompet finansial aktif.</p>
    </div>
</div>

<div id="settings-error" class="hidden mb-5 rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Profil Pengguna Form -->
    <div class="card p-6 lg:col-span-1 border-t-4 border-t-[#1F4E79] flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="w-12 h-12 rounded-full bg-[#1F4E79] text-white flex items-center justify-center font-extrabold text-lg">
                    👤
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-[#102A43]">Profil Akun</h2>
                    <p class="text-xs text-slate-400">Informasi pengguna CatatDuit</p>
                </div>
            </div>

            <form id="profile-form" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input id="user-name" type="text" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold outline-none focus:bg-white focus:border-[#19B5A5]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email</label>
                    <input id="user-email" type="email" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-semibold outline-none focus:bg-white focus:border-[#19B5A5]">
                </div>

                <button type="submit" id="btn-save-profile" class="w-full mt-2 py-3 rounded-xl font-bold text-xs text-white bg-[#1F4E79] hover:bg-[#163859] transition shadow-md shadow-blue-900/15">
                    Perbarui Profil
                </button>
            </form>
        </div>

        <div class="mt-8 pt-4 border-t border-slate-100">
            <button onclick="deleteAccount()" class="w-full py-2.5 rounded-xl font-bold text-xs text-rose-600 hover:bg-rose-50 transition border border-rose-200">
                ⚠️ Hapus Akun Permanen
            </button>
        </div>
    </div>

    <!-- Kelola Dompet / Rekening -->
    <div class="card p-6 lg:col-span-2 border-t-4 border-t-[#19B5A5]">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
            <div>
                <h2 class="text-base font-extrabold text-[#102A43]">Dompet & Rekening Keuangan</h2>
                <p class="text-xs text-slate-400">Atur rekening bank, e-wallet, atau kas fisik untuk transaksi</p>
            </div>
            <button onclick="openWalletModal()" class="inline-flex items-center gap-1.5 rounded-xl bg-[#19B5A5] px-4 py-2 text-xs font-bold text-white shadow-md shadow-teal-500/15 hover:bg-[#148B80] transition">
                <span>+</span> Tambah Dompet
            </button>
        </div>

        <div id="wallets-list" class="space-y-3">
            <div class="text-center text-xs text-slate-400 py-8">Memuat daftar dompet...</div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH/EDIT DOMPET -->
<div id="wallet-modal" class="hidden fixed inset-0 bg-slate-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl relative animate-in fade-in">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100">
            <h3 id="wallet-modal-title" class="font-extrabold text-lg text-[#102A43]">Tambah Dompet Baru</h3>
            <button onclick="closeWalletModal()" class="text-slate-400 hover:text-slate-600 text-lg">✕</button>
        </div>

        <form id="wallet-form" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Dompet / Rekening</label>
                <input id="wallet-name" type="text" required placeholder="Mis. BCA Utama, GoPay, Dompet Tunai" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi Pemasukan Default (Rp)</label>
                <input id="wallet-amount" type="number" min="0" placeholder="0" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:bg-white focus:border-[#19B5A5]">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Frekuensi</label>
                    <select id="wallet-freq" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold outline-none focus:bg-white focus:border-[#19B5A5]">
                        <option value="monthly">Bulanan</option>
                        <option value="weekly">Mingguan</option>
                        <option value="irregular" selected>Tidak Rutin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Masuk (1-31)</label>
                    <input id="wallet-payday" type="number" min="1" max="31" placeholder="Mis. 25" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs outline-none focus:bg-white focus:border-[#19B5A5]">
                </div>
            </div>

            <div class="pt-2">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input id="wallet-is-primary" type="checkbox" class="rounded text-[#19B5A5] focus:ring-0">
                    Jadikan sebagai Dompet Utama (Default)
                </label>
            </div>

            <div class="pt-3 flex gap-3">
                <button type="button" onclick="closeWalletModal()" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-600 border border-slate-200 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" id="btn-save-wallet" class="flex-1 py-3 rounded-xl font-bold text-sm text-white bg-[#1F4E79] hover:bg-[#163859] transition shadow-md shadow-blue-900/15">Simpan Dompet</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
if (!token) window.location.href = '/login';

let walletsList = [];
let editWalletId = null;

const fmtRp = (num) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(Number(num || 0));

function showError(msg) {
    const el = document.getElementById('settings-error');
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
        const [userRes, incRes] = await Promise.all([
            authFetch('/api/v1/users/me'),
            authFetch('/api/v1/incomes')
        ]);

        if (userRes && userRes.ok) {
            const userJson = await userRes.json();
            const u = userJson.data;
            document.getElementById('user-name').value = u.name || '';
            document.getElementById('user-email').value = u.email || '';
        }

        if (incRes && incRes.ok) {
            const incJson = await incRes.json();
            walletsList = incJson.data || [];
            renderWallets();
        }
    } catch (err) {
        console.error(err);
        showError('Gagal memuat pengaturan akun.');
    }
}

function renderWallets() {
    const box = document.getElementById('wallets-list');
    if (!walletsList.length) {
        box.innerHTML = '<div class="text-center text-xs text-slate-400 py-6">Belum ada dompet atau rekening tercatat.</div>';
        return;
    }

    box.innerHTML = walletsList.map(w => {
        const isPrimary = w.is_primary;
        return `
            <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-slate-300 transition">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-lg font-bold">
                        💳
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-[#102A43] text-sm">${w.name}</span>
                            ${isPrimary ? '<span class="badge bg-amber-100 text-amber-800 text-[10px]">Utama ⭐</span>' : ''}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">Saldo aktif: <b class="text-[#102A43]">${fmtRp(w.balance || 0)}</b></div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="editWallet('${w.id}')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-[#1F4E79] hover:bg-slate-200 transition">
                        Edit
                    </button>
                    <button onclick="deleteWallet('${w.id}')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-rose-500 hover:bg-rose-50 transition">
                        Hapus
                    </button>
                </div>
            </div>
        `;
    }).join('');
}

// Profil update
document.getElementById('profile-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-profile');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    try {
        const res = await authFetch('/api/v1/users/me', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: document.getElementById('user-name').value.trim(),
                email: document.getElementById('user-email').value.trim()
            })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal memperbarui profil.');
        }

        alert('Profil berhasil diperbarui!');
        loadData();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Perbarui Profil';
        btn.disabled = false;
    }
});

// Wallet Modal
function openWalletModal() {
    editWalletId = null;
    document.getElementById('wallet-modal-title').textContent = 'Tambah Dompet Baru';
    document.getElementById('wallet-form').reset();
    document.getElementById('wallet-modal').classList.remove('hidden');
}

function closeWalletModal() {
    document.getElementById('wallet-modal').classList.add('hidden');
}

function editWallet(id) {
    const w = walletsList.find(x => x.id === id);
    if (!w) return;
    editWalletId = id;
    document.getElementById('wallet-modal-title').textContent = 'Edit Dompet';
    document.getElementById('wallet-name').value = w.name;
    document.getElementById('wallet-amount').value = w.default_amount || '';
    document.getElementById('wallet-freq').value = w.frequency || 'irregular';
    document.getElementById('wallet-payday').value = w.pay_day || '';
    document.getElementById('wallet-is-primary').checked = Boolean(w.is_primary);
    document.getElementById('wallet-modal').classList.remove('hidden');
}

document.getElementById('wallet-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-save-wallet');
    btn.textContent = 'Menyimpan...';
    btn.disabled = true;

    const payload = {
        name: document.getElementById('wallet-name').value.trim(),
        default_amount: parseInt(document.getElementById('wallet-amount').value || 0, 10),
        frequency: document.getElementById('wallet-freq').value,
        pay_day: document.getElementById('wallet-payday').value ? parseInt(document.getElementById('wallet-payday').value, 10) : null,
        is_primary: document.getElementById('wallet-is-primary').checked
    };

    try {
        const url = editWalletId ? `/api/v1/incomes/${editWalletId}` : '/api/v1/incomes';
        const method = editWalletId ? 'PATCH' : 'POST';

        const res = await authFetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menyimpan dompet.');
        }

        alert('Dompet berhasil disimpan!');
        closeWalletModal();
        loadData();
    } catch (err) {
        alert(err.message);
    } finally {
        btn.textContent = 'Simpan Dompet';
        btn.disabled = false;
    }
});

async function deleteWallet(id) {
    if (!confirm('Hapus dompet ini?')) return;
    try {
        const res = await authFetch(`/api/v1/incomes/${id}`, { method: 'DELETE' });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Gagal menghapus dompet. Pastikan tidak ada transaksi yang terhubung.');
        }
        loadData();
    } catch (err) {
        alert(err.message);
    }
}

async function deleteAccount() {
    if (!confirm('PERINGATAN: Menghapus akun akan menghapus seluruh data finansialmu secara permanen. Apakah kamu yakin?')) return;
    try {
        const res = await authFetch('/api/v1/users/me', { method: 'DELETE' });
        if (!res.ok) throw new Error('Gagal menghapus akun.');
        localStorage.removeItem('token');
        alert('Akun berhasil dihapus.');
        window.location.href = '/register';
    } catch (err) {
        alert(err.message);
    }
}

loadData();
</script>
@endpush
