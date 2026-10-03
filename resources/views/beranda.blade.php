@extends('layouts.mobile')

@section('content')
<!-- Header Kosmik -->
<div class="bg-cosmic stars pt-10 pb-20 px-6 text-white rounded-b-[32px]">
    <div class="flex justify-between items-center mb-6">
        <div>
            <p class="text-sm text-gray-300">Halo,</p>
            <h1 id="user-name" class="text-xl font-bold">... 👋</h1>
        </div>
        <div id="current-month" class="bg-white/10 px-4 py-1.5 rounded-full text-sm font-medium backdrop-blur-sm">
            ... ▼
        </div>
    </div>

    <!-- Hero Card -->
    <div class="bg-white/10 border border-white/20 p-5 rounded-[16px] backdrop-blur-md text-center">
        <p class="text-sm text-gray-200">Total Saldo</p>
        <h2 id="total-balance" class="text-4xl font-bold mt-1 mb-4">Rp...</h2>
        <div class="flex justify-between text-xs font-medium px-2">
            <div id="total-income" class="flex items-center gap-1 text-[#48CAE4]">↓ Pemasukan Rp...</div>
            <div id="total-expense" class="flex items-center gap-1 text-red-300">↑ Pengeluaran Rp...</div>
        </div>
    </div>
</div>

<div class="px-6 -mt-6 z-10 flex-1 pb-40">
    <!-- Scrollable Income Cards -->
    <div id="income-cards" class="flex gap-4 overflow-x-auto pb-4 pt-2 -mx-6 px-6 hide-scrollbar">
        <div class="min-w-[200px] bg-white p-4 rounded-[16px] shadow-sm animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
            <div class="h-6 bg-gray-300 rounded w-3/4"></div>
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="mt-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-[#1F4E79]">Transaksi terbaru</h3>
            <a href="/transaksi" class="text-xs font-medium text-[#48CAE4]">Lihat semua</a>
        </div>
        <div id="recent-transactions" class="bg-white rounded-[16px] p-2 shadow-sm space-y-1">
            <div class="p-3 text-center text-sm text-gray-400">Memuat transaksi...</div>
        </div>
    </div>
</div>

<!-- Sticky Input Bar (Di atas Navigasi) -->
<div class="absolute bottom-[72px] left-0 w-full px-4 pb-2 bg-gradient-to-t from-[#F6F8FB] to-transparent">
    <div class="bg-white p-2 rounded-2xl shadow-lg border border-blue-50 flex gap-2">
        <input id="smart-entry-input" type="text" placeholder="Ketik, mis. beli bakso 10k" class="flex-1 bg-[#F6F8FB] px-4 py-3 rounded-xl text-sm outline-none focus:ring-1 focus:ring-[#1F4E79]">
        <button id="smart-entry-btn" class="bg-[#1F4E79] w-12 h-12 rounded-xl flex items-center justify-center text-white shrink-0 transition hover:bg-[#163859]">
            ➤
        </button>
    </div>
</div>

<!-- Bottom Navigation Bar -->
<div class="fixed md:absolute bottom-0 left-0 w-full h-[72px] bg-white border-t border-gray-100 flex justify-around items-center px-4 pb-2 text-xs font-medium text-gray-400 z-30">
    <a href="/beranda" class="flex flex-col items-center text-[#1F4E79]"><span class="text-xl mb-1">🏠</span>Beranda</a>
    <a href="/transaksi" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📄</span>Transaksi</a>
    <a href="/kategori" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📁</span>Kategori</a>
    <a href="/laporan" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📊</span>Laporan</a>
    <a href="javascript:void(0)" onclick="logout()" class="flex flex-col items-center hover:text-red-500"><span class="text-xl mb-1">🚪</span>Keluar</a>
</div>

<!-- MODAL SMART ENTRY -->
<div id="smart-entry-modal" class="hidden absolute inset-0 bg-gray-900/40 z-50 flex flex-col justify-end">
    <div class="bg-white rounded-t-[24px] p-6 pb-8 shadow-2xl animate-slide-up">
        <h3 class="font-bold text-[#1F4E79] mb-4">Konfirmasi Pencatatan</h3>
        
        <div id="smart-entry-items" class="space-y-3 mb-6 max-h-60 overflow-y-auto">
            <!-- Items dynamically added here -->
        </div>

        <div class="flex justify-between items-center mb-6 px-2">
            <span class="text-gray-500 text-sm">Total dicatat</span>
            <span id="smart-entry-total" class="font-bold text-xl text-[#1F4E79]">Rp0</span>
        </div>

        <div class="flex gap-3">
            <button id="smart-entry-cancel" class="flex-1 py-3.5 rounded-xl font-semibold text-gray-500 bg-gray-100">Batal</button>
            <button id="smart-entry-save" class="flex-1 py-3.5 rounded-xl font-semibold text-white bg-[#1F4E79]">Simpan semua</button>
        </div>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');
    if (!token) window.location.href = '/login';

    function logout() {
        if (confirm('Apakah Anda yakin ingin keluar?')) {
            fetch('/api/v1/auth/logout', {
                method: 'POST',
                headers: { 'Authorization': `Bearer ${token}` }
            }).finally(() => {
                localStorage.removeItem('token');
                window.location.href = '/login';
            });
        }
    }

    const fmtRp = (num) => 'Rp' + parseInt(num).toLocaleString('id-ID');
    let parsedSmartEntryItems = [];

    async function fetchDashboardData() {
        try {
            // 1. Fetch User
            const meRes = await fetch('/api/v1/users/me', { headers: { 'Authorization': `Bearer ${token}` }});
            if (!meRes.ok) throw new Error('Unauthorized');
            const meData = await meRes.json();
            document.getElementById('user-name').innerText = meData.data.name.split(' ')[0] + ' 👋';

            // 2. Fetch Summary
            const sumRes = await fetch('/api/v1/summary', { headers: { 'Authorization': `Bearer ${token}` }});
            const sumData = await sumRes.json();
            
            document.getElementById('total-balance').innerText = fmtRp(sumData.data.total_balance);
            document.getElementById('total-income').innerText = '↓ Pemasukan ' + fmtRp(sumData.data.monthly_income);
            document.getElementById('total-expense').innerText = '↑ Pengeluaran ' + fmtRp(sumData.data.monthly_expense);

            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const d = new Date();
            document.getElementById('current-month').innerText = `${months[d.getMonth()]} ${d.getFullYear()} ▼`;

            // 3. Incomes list (Cards)
            const incRes = await fetch('/api/v1/incomes', { headers: { 'Authorization': `Bearer ${token}` }});
            const incData = await incRes.json();
            const cardsHtml = incData.data.map(inc => `
                <div class="min-w-[200px] bg-white p-4 rounded-[16px] shadow-sm">
                    <div class="flex justify-between mb-2">
                        <h3 class="font-bold text-[#1F4E79]">${inc.name}</h3>
                        ${inc.is_primary ? '<span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Utama</span>' : ''}
                    </div>
                    <p class="text-lg font-bold text-gray-900">${fmtRp(inc.balance)}</p>
                </div>
            `).join('');
            document.getElementById('income-cards').innerHTML = cardsHtml || '<div class="p-4 text-sm text-gray-400">Belum ada dompet.</div>';

            // 4. Recent Expenses
            const expRes = await fetch('/api/v1/expenses?limit=3', { headers: { 'Authorization': `Bearer ${token}` }});
            const expData = await expRes.json();
            const expHtml = expData.data.map(ex => `
                <div class="flex justify-between items-center p-3 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-xl">🛒</div>
                        <div>
                            <p class="font-semibold text-sm">${ex.item}</p>
                            <p class="text-xs text-gray-500">${ex.category?.name || 'Lain-lain'} • ${ex.spent_at}</p>
                        </div>
                    </div>
                    <p class="font-bold text-red-500 text-sm">-${fmtRp(ex.amount)}</p>
                </div>
            `).join('');
            document.getElementById('recent-transactions').innerHTML = expHtml || '<div class="p-3 text-center text-sm text-gray-400">Belum ada transaksi</div>';

        } catch (e) {
            console.error(e);
            if (e.message === 'Unauthorized') {
                localStorage.removeItem('token');
                window.location.href = '/login';
            }
        }
    }

    // Initialize
    fetchDashboardData();

    // Smart Entry Logic
    const seInput = document.getElementById('smart-entry-input');
    const seBtn = document.getElementById('smart-entry-btn');
    const seModal = document.getElementById('smart-entry-modal');
    const seItems = document.getElementById('smart-entry-items');
    const seTotal = document.getElementById('smart-entry-total');

    seBtn.addEventListener('click', async () => {
        const text = seInput.value.trim();
        if (!text) return;

        seBtn.innerText = '...';
        seBtn.disabled = true;

        try {
            const res = await fetch('/api/v1/smart-entry/parse', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ text })
            });
            const data = await res.json();
            if (res.ok) {
                parsedSmartEntryItems = data.data.items;
                let html = '';
                let total = 0;
                
                parsedSmartEntryItems.forEach((item, idx) => {
                    const isExpense = item.type === 'expense';
                    const color = isExpense ? 'text-red-500' : 'text-green-500';
                    const sign = isExpense ? '-' : '+';
                    total += isExpense ? item.amount : 0; // Just sum up expenses for display, or show net

                    html += `
                    <div class="flex justify-between items-center bg-[#F6F8FB] p-4 rounded-xl border border-gray-100">
                        <div>
                            <p class="font-bold">${item.item}</p>
                            <p class="text-xs text-gray-500">${isExpense ? 'Pengeluaran' : 'Pemasukan'}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <p class="font-bold ${color}">${sign}${fmtRp(item.amount)}</p>
                            <button onclick="removeSmartEntryItem(${idx})" class="text-red-400 text-lg">×</button>
                        </div>
                    </div>`;
                });
                
                seItems.innerHTML = html;
                seTotal.innerText = fmtRp(total);
                seModal.classList.remove('hidden');
            } else {
                alert(data.error?.message || 'Gagal mengenali teks.');
            }
        } catch (err) {
            alert('Kesalahan jaringan: ' + err.message);
        } finally {
            seBtn.innerText = '➤';
            seBtn.disabled = false;
        }
    });

    window.removeSmartEntryItem = (index) => {
        parsedSmartEntryItems.splice(index, 1);
        if (parsedSmartEntryItems.length === 0) {
            seModal.classList.add('hidden');
            return;
        }
        // Re-render
        let html = '';
        let total = 0;
        parsedSmartEntryItems.forEach((item, idx) => {
            const isExpense = item.type === 'expense';
            const color = isExpense ? 'text-red-500' : 'text-green-500';
            const sign = isExpense ? '-' : '+';
            total += isExpense ? item.amount : 0;
            html += `
            <div class="flex justify-between items-center bg-[#F6F8FB] p-4 rounded-xl border border-gray-100">
                <div>
                    <p class="font-bold">${item.item}</p>
                    <p class="text-xs text-gray-500">${isExpense ? 'Pengeluaran' : 'Pemasukan'}</p>
                </div>
                <div class="flex items-center gap-4">
                    <p class="font-bold ${color}">${sign}${fmtRp(item.amount)}</p>
                    <button onclick="removeSmartEntryItem(${idx})" class="text-red-400 text-lg">×</button>
                </div>
            </div>`;
        });
        seItems.innerHTML = html;
        seTotal.innerText = fmtRp(total);
    };

    document.getElementById('smart-entry-cancel').addEventListener('click', () => {
        seModal.classList.add('hidden');
    });

    document.getElementById('smart-entry-save').addEventListener('click', async () => {
        if (parsedSmartEntryItems.length === 0) return;
        
        const btn = document.getElementById('smart-entry-save');
        btn.innerText = 'Menyimpan...';
        btn.disabled = true;

        try {
            const res = await fetch('/api/v1/smart-entry/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
                body: JSON.stringify({ items: parsedSmartEntryItems })
            });
            if (res.ok) {
                seModal.classList.add('hidden');
                seInput.value = '';
                alert('Berhasil dicatat!');
                fetchDashboardData(); // Refresh UI
            } else {
                const data = await res.json();
                alert(data.error?.message || 'Gagal menyimpan.');
            }
        } catch (err) {
            alert('Kesalahan jaringan: ' + err.message);
        } finally {
            btn.innerText = 'Simpan semua';
            btn.disabled = false;
        }
    });
</script>
@endsection