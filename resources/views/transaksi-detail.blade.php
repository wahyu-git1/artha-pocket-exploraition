@extends('layouts.mobile')

@section('content')
<div class="bg-white pt-10 pb-4 px-6 sticky top-0 z-20 shadow-sm flex items-center gap-4">
    <a href="/transaksi" class="text-2xl text-gray-400 hover:text-[#1F4E79]">←</a>
    <h2 class="text-xl font-bold text-[#1F4E79]">Ubah Transaksi</h2>
</div>

<form id="edit-form" class="flex-1 flex flex-col justify-between">
    @csrf
    <div class="px-6 py-6 overflow-y-auto">
        <!-- Read Only Section: Smart Entry Hint -->
        <div id="smart-entry-box" class="hidden bg-blue-50 border border-blue-100 rounded-2xl p-4 mb-6">
            <div class="flex items-start gap-3">
                <span class="text-xl mt-0.5">⚡</span>
                <div>
                    <p class="text-[10px] font-bold text-blue-800 uppercase tracking-wider mb-1">Dicatat lewat Smart Entry</p>
                    <p id="smart-entry-raw" class="text-sm text-gray-600 italic">"..."</p>
                </div>
            </div>
        </div>

        <!-- Form Edit -->
        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Item</label>
                <input id="item-name" type="text" required class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl font-medium focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nominal</label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-gray-500 font-medium">Rp</span>
                    <input id="item-amount" type="number" required class="w-full pl-12 pr-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl font-bold text-red-500 focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal</label>
                    <input id="item-date" type="date" required class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kategori</label>
                    <select id="item-category" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
                        <option value="">Pilih Kategori</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sumber Dompet</label>
                <select id="item-income" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
                    <option value="">Pilih Dompet</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Catatan (Opsional)</label>
                <textarea id="item-note" rows="2" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#1F4E79]"></textarea>
            </div>
        </div>
    </div>

    <div class="p-6 bg-white border-t border-gray-100 space-y-3">
        <button type="submit" id="btn-save" class="w-full bg-[#1F4E79] text-white font-semibold py-3.5 rounded-xl shadow-md hover:bg-[#163859] transition">
            Simpan Perubahan
        </button>
        <button type="button" onclick="deleteCurrentExpense()" class="w-full text-red-500 font-semibold py-2 text-sm hover:underline">
            Hapus Transaksi
        </button>
    </div>
</form>

<script>
    const token = localStorage.getItem('token');
    if (!token) window.location.href = '/login';

    const urlParams = new URLSearchParams(window.location.search);
    const expenseId = urlParams.get('id');

    if (!expenseId) {
        alert('ID Transaksi tidak ditemukan.');
        window.location.href = '/transaksi';
    }

    async function init() {
        try {
            // Load Categories
            const catRes = await fetch('/api/v1/categories?filter[type]=expense', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const catData = await catRes.json();
            const catSelect = document.getElementById('item-category');
            (catData.data || []).forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = `${c.icon || ''} ${c.name}`;
                catSelect.appendChild(opt);
            });

            // Load Incomes
            const incRes = await fetch('/api/v1/incomes', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const incData = await incRes.json();
            const incSelect = document.getElementById('item-income');
            (incData.data || []).forEach(i => {
                const opt = document.createElement('option');
                opt.value = i.id;
                opt.textContent = i.name;
                incSelect.appendChild(opt);
            });

            // Load Expense details
            const expRes = await fetch(`/api/v1/expenses/${expenseId}`, {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (!expRes.ok) throw new Error('Data tidak ditemukan');
            const expData = await expRes.json();
            const item = expData.data;

            document.getElementById('item-name').value = item.item;
            document.getElementById('item-amount').value = item.amount;
            document.getElementById('item-date').value = item.spent_at ? item.spent_at.slice(0, 10) : '';
            if (item.category_id) document.getElementById('item-category').value = item.category_id;
            if (item.income_id) document.getElementById('item-income').value = item.income_id;
            document.getElementById('item-note').value = item.note || '';

            if (item.raw_input) {
                document.getElementById('smart-entry-box').classList.remove('hidden');
                document.getElementById('smart-entry-raw').innerText = `"${item.raw_input}"`;
            }
        } catch (e) {
            alert('Gagal memuat detail transaksi.');
            window.location.href = '/transaksi';
        }
    }

    document.getElementById('edit-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save');
        btn.innerText = 'Menyimpan...';
        btn.disabled = true;

        const payload = {
            item: document.getElementById('item-name').value,
            amount: parseInt(document.getElementById('item-amount').value),
            spent_at: document.getElementById('item-date').value,
            category_id: document.getElementById('item-category').value ? parseInt(document.getElementById('item-category').value) : null,
            income_id: document.getElementById('item-income').value ? parseInt(document.getElementById('item-income').value) : null,
            note: document.getElementById('item-note').value || null
        };

        try {
            const res = await fetch(`/api/v1/expenses/${expenseId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                alert('Transaksi berhasil diperbarui!');
                window.location.href = '/transaksi';
            } else {
                const err = await res.json();
                alert(err.message || 'Gagal menyimpan transaksi.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        } finally {
            btn.innerText = 'Simpan Perubahan';
            btn.disabled = false;
        }
    });

    async function deleteCurrentExpense() {
        if (!confirm('Hapus transaksi ini?')) return;
        try {
            const res = await fetch(`/api/v1/expenses/${expenseId}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                alert('Transaksi dihapus.');
                window.location.href = '/transaksi';
            } else {
                alert('Gagal menghapus transaksi.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    }

    init();
</script>
@endsection