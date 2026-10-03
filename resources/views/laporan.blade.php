@extends('layouts.mobile')

@section('content')
<div class="bg-cosmic stars pt-10 pb-16 px-6 text-white rounded-b-[32px] relative z-10">
    <div class="flex justify-between items-center mb-6">
        <div class="flex items-center gap-3">
            <a href="/beranda" class="text-xl text-white hover:text-[#48CAE4] font-bold">←</a>
            <h2 class="text-xl font-bold">Laporan</h2>
        </div>
        <div id="selected-month" class="bg-white/10 px-4 py-1.5 rounded-full text-xs font-medium backdrop-blur-sm">Okt 2026</div>
    </div>
    
    <!-- Bar Chart (6 Bulan) -->
    <div id="chart-bars" class="mt-6 flex items-end justify-between h-32 px-2 border-b border-white/20 pb-2">
        <div class="w-full text-center text-xs text-gray-300">Memuat grafik..</div>
    </div>
    <div id="chart-labels" class="flex justify-between text-[10px] text-gray-400 mt-2 px-2">
    </div>
</div>

<div class="px-6 -mt-6 z-20 flex-1 overflow-y-auto pb-36">
    <div class="bg-white rounded-[16px] shadow-sm p-5 mb-4">
        <h3 class="font-bold text-[#1F4E79] mb-4">Top Pengeluaran Bulan Ini</h3>
        
        <div id="categories-rank" class="space-y-4">
            <div class="p-4 text-center text-sm text-gray-400">Memuat analisis kategori...</div>
        </div>
    </div>
</div>

<div class="p-4 bg-white border-t border-gray-100 fixed md:absolute bottom-[72px] left-0 w-full z-20">
    <button id="btn-export" onclick="exportCsv()" class="w-full border-2 border-gray-200 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-50 flex justify-center items-center gap-2 text-sm transition">
        📥 Ekspor CSV Transaksi
    </button>
</div>

<!-- Bottom Navigation Bar -->
<div class="fixed md:absolute bottom-0 left-0 w-full h-[72px] bg-white border-t border-gray-100 flex justify-around items-center px-4 pb-2 text-xs font-medium text-gray-400 z-30">
    <a href="/beranda" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">🏠</span>Beranda</a>
    <a href="/transaksi" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📄</span>Transaksi</a>
    <a href="/kategori" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📁</span>Kategori</a>
    <a href="/laporan" class="flex flex-col items-center text-[#1F4E79]"><span class="text-xl mb-1">📊</span>Laporan</a>
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
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const now = new Date();
    document.getElementById('selected-month').innerText = `${monthNames[now.getMonth()]} ${now.getFullYear()}`;

    async function loadReports() {
        try {
            // 1. Monthly chart
            const mRes = await fetch('/api/v1/reports/monthly?months=6', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (mRes.status === 401) {
                localStorage.removeItem('token');
                window.location.href = '/login';
                return;
            }

            const mData = await mRes.json();
            const monthly = mData.data || [];
            
            const maxVal = Math.max(...monthly.map(m => m.total_expense), 100000);
            
            const barsHtml = monthly.map((m, idx) => {
                const heightPct = Math.max(10, Math.min(100, Math.round((m.total_expense / maxVal) * 100)));
                const isCurrent = idx === monthly.length - 1;
                return `
                    <div class="flex flex-col items-center flex-1 h-full justify-end group">
                        <span class="text-[9px] text-gray-300 opacity-0 group-hover:opacity-100 transition mb-1">${fmtRp(m.total_expense)}</span>
                        <div class="w-4 rounded-t-sm transition-all duration-300 ${isCurrent ? 'bg-[#48CAE4]' : 'bg-[#48CAE4]/40'}" style="height: ${heightPct}%;"></div>
                    </div>
                `;
            }).join('');
            document.getElementById('chart-bars').innerHTML = barsHtml;

            const labelsHtml = monthly.map((m, idx) => {
                const parts = m.month.split('-');
                const mIndex = parseInt(parts[1], 10) - 1;
                const isCurrent = idx === monthly.length - 1;
                return `<span class="${isCurrent ? 'text-white font-bold' : ''}">${monthNames[mIndex]}</span>`;
            }).join('');
            document.getElementById('chart-labels').innerHTML = labelsHtml;

            // 2. Category Breakdown
            const cRes = await fetch('/api/v1/reports/categories', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            const cData = await cRes.json();
            const categories = cData.data || [];

            const totalSpent = categories.reduce((acc, c) => acc + parseInt(c.total || 0), 0) || 1;
            const rankContainer = document.getElementById('categories-rank');

            if (categories.length === 0) {
                rankContainer.innerHTML = '<div class="p-4 text-center text-sm text-gray-400">Belum ada pengeluaran bulan ini.</div>';
                return;
            }

            rankContainer.innerHTML = categories.map(c => {
                const pct = Math.round((parseInt(c.total || 0) / totalSpent) * 100);
                return `
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">${c.icon || '📁'}</span>
                                <span class="font-bold text-sm text-gray-900">${c.name}</span>
                            </div>
                            <span class="font-bold text-red-500 text-sm">${fmtRp(c.total)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-[#1F4E79] h-2 rounded-full" style="width: ${pct}%;"></div>
                            </div>
                            <span class="text-xs font-bold text-gray-500 w-8 text-right">${pct}%</span>
                        </div>
                    </div>
                `;
            }).join('');

        } catch (e) {
            console.error(e);
        }
    }

    async function exportCsv() {
        const btn = document.getElementById('btn-export');
        btn.innerText = 'Mengunduh...';
        btn.disabled = true;

        try {
            const res = await fetch('/api/v1/exports/transactions', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                const blob = await res.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `transactions_${new Date().toISOString().slice(0, 10)}.csv`;
                document.body.appendChild(a);
                a.click();
                a.remove();
            } else {
                alert('Gagal mengekspor data.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        } finally {
            btn.innerText = '📥 Ekspor CSV Transaksi';
            btn.disabled = false;
        }
    }

    loadReports();
</script>
@endsection