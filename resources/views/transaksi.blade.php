@extends('layouts.mobile')

@section('content')
<div class="bg-white pt-10 pb-4 px-6 sticky top-0 z-20 shadow-sm">
    <div class="flex items-center gap-3 mb-4">
        <a href="/beranda" class="text-xl text-[#1F4E79] hover:text-[#48CAE4] font-bold">←</a>
        <h2 class="text-xl font-bold text-[#1F4E79]">Transaksi</h2>
    </div>
    
    <!-- Search Bar -->
    <div class="relative mb-4">
        <span class="absolute left-4 top-2.5 text-gray-400">🔍</span>
        <input id="search-input" type="text" placeholder="Cari transaksi..." class="w-full pl-10 pr-4 py-2.5 bg-[#F6F8FB] border border-gray-100 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
    </div>

    <!-- Filter Chips -->
    <div class="flex gap-2 overflow-x-auto hide-scrollbar -mx-6 px-6">
        <button onclick="filterType('all')" id="filter-all" class="filter-btn bg-[#1F4E79] text-white px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap">Semua</button>
        <button onclick="filterType('expense')" id="filter-expense" class="filter-btn bg-[#F6F8FB] text-gray-600 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-gray-200">Pengeluaran</button>
        <button onclick="filterType('income')" id="filter-income" class="filter-btn bg-[#F6F8FB] text-gray-600 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-gray-200">Pemasukan</button>
    </div>
</div>

<div class="px-4 py-4 flex-1 pb-24 overflow-y-auto">
    <!-- Summary Strip -->
    <div class="bg-red-50 rounded-xl p-4 mb-6 flex justify-between items-center border border-red-100">
        <span class="text-sm font-medium text-red-800">Total Pengeluaran Bulan Ini</span>
        <span id="monthly-expense-total" class="font-bold text-red-600">Rp0</span>
    </div>

    <!-- Transactions List Container -->
    <div class="mb-6">
        <h3 class="text-xs font-bold text-gray-400 mb-3 ml-2 uppercase">Daftar Transaksi</h3>
        <div id="transactions-container" class="bg-white rounded-[16px] shadow-sm overflow-hidden divide-y divide-gray-50">
            <div class="p-6 text-center text-sm text-gray-400">Memuat data transaksi...</div>
        </div>
    </div>
</div>

<!-- Bottom Navigation Bar -->
<div class="fixed md:absolute bottom-0 left-0 w-full h-[72px] bg-white border-t border-gray-100 flex justify-around items-center px-4 pb-2 text-xs font-medium text-gray-400 z-30">
    <a href="/beranda" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">🏠</span>Beranda</a>
    <a href="/transaksi" class="flex flex-col items-center text-[#1F4E79]"><span class="text-xl mb-1">📄</span>Transaksi</a>
    <a href="/kategori" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📁</span>Kategori</a>
    <a href="/laporan" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📊</span>Laporan</a>
    <a href="javascript:void(0)" onclick="logout()" class="flex flex-col items-center hover:text-red-500"><span class="text-xl mb-1">🚪</span>Keluar</a>
</div>

<script>
    const token = localStorage.getItem('token');
    if (!token) window.location.href = '/login';

    function logout() {
        if (confirm('Keluar dari aplikasi?')) {
            fetch('/api/v1/auth/logout', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            }).finally(() => {
                localStorage.removeItem('token');
                window.location.href = '/login';
            });
        }
    }

    const fmtRp = (num) => 'Rp' + parseInt(num || 0).toLocaleString('id-ID');
    let allExpenses = [];
    let currentFilter = 'all';

    async function loadData() {
        try {
            // Summary
            const sumRes = await fetch('/api/v1/summary', { headers: { 'Authorization': `Bearer ${token}` }});
            if (sumRes.ok) {
                const sumData = await sumRes.json();
                document.getElementById('monthly-expense-total').innerText = fmtRp(sumData.data.monthly_expense);
            }

            // Expenses
            const expRes = await fetch('/api/v1/expenses', { headers: { 'Authorization': `Bearer ${token}` }});
            if (expRes.status === 401) {
                localStorage.removeItem('token');
                window.location.href = '/login';
                return;
            }

            const expData = await expRes.json();
            allExpenses = expData.data || [];
            renderList();
        } catch (e) {
            console.error(e);
            document.getElementById('transactions-container').innerHTML = '<div class="p-6 text-center text-sm text-red-500">Gagal memuat transaksi.</div>';
        }
    }

    function renderList() {
        const query = document.getElementById('search-input').value.toLowerCase().trim();
        const container = document.getElementById('transactions-container');

        let filtered = allExpenses.filter(item => {
            const matchName = item.item.toLowerCase().includes(query) || (item.category?.name && item.category.name.toLowerCase().includes(query));
            return matchName;
        });

        if (filtered.length === 0) {
            container.innerHTML = '<div class="p-6 text-center text-sm text-gray-400">Tidak ada transaksi ditemukan.</div>';
            return;
        }

        container.innerHTML = filtered.map(item => `
            <div class="flex justify-between items-center p-4 hover:bg-gray-50 transition cursor-pointer">
                <div class="flex items-center gap-3 flex-1" onclick="window.location.href='/transaksi/detail?id=${item.id}'">
                    <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-xl shrink-0">🛒</div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">${item.item}</p>
                        <div class="flex gap-1 items-center mt-0.5">
                            <span class="text-[10px] text-gray-500">${item.category?.name || 'Umum'}</span>
                            <span class="text-[10px] bg-blue-50 text-blue-600 px-1.5 py-0.5 rounded">${item.spent_at}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <p class="font-bold text-red-500 text-sm">-${fmtRp(item.amount)}</p>
                    <button onclick="event.stopPropagation(); deleteExpense(${item.id})" class="text-gray-300 hover:text-red-500 p-1 text-xs" title="Hapus">✕</button>
                </div>
            </div>
        `).join('');
    }

    async function deleteExpense(id) {
        if (!confirm('Hapus pengeluaran ini?')) return;
        try {
            const res = await fetch(`/api/v1/expenses/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                allExpenses = allExpenses.filter(e => e.id !== id);
                renderList();
                loadData();
            } else {
                alert('Gagal menghapus item.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    }

    document.getElementById('search-input').addEventListener('input', renderList);

    function filterType(type) {
        currentFilter = type;
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.className = 'filter-btn bg-[#F6F8FB] text-gray-600 px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap border border-gray-200';
        });
        const active = document.getElementById(`filter-${type}`);
        if (active) {
            active.className = 'filter-btn bg-[#1F4E79] text-white px-4 py-1.5 rounded-full text-xs font-medium whitespace-nowrap';
        }
        renderList();
    }

    loadData();
</script>
@endsection