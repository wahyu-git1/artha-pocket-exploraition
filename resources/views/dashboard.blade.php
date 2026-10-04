@extends('layouts.app')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-7">
    <div>
        <div id="greeting-text" class="text-sm text-slate-500">Selamat datang di CatatDuit ✦</div>
        <h1 class="text-2xl font-extrabold mt-1 text-[#102A43]">Dashboard Finansial</h1>
        <p class="text-sm text-slate-500 mt-1">Pantau orbit keuanganmu secara realtime.</p>
    </div>
    <a href="{{ route('transaksi.detail') }}" class="inline-flex items-center justify-center gap-2 bg-[#1F4E79] hover:bg-[#163859] text-white rounded-xl px-4 py-2.5 text-sm font-bold shadow-md shadow-blue-900/15 transition">
        + Catat transaksi
    </a>
</div>

<div class="kpis grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    <div class="card kpi p-5 border-l-4 border-l-[#1F4E79]">
        <div class="text-xs font-semibold text-slate-500">Total saldo</div>
        <div id="kpi-total-balance" class="text-2xl font-extrabold mt-3 text-[#102A43]">Rp0</div>
        <div class="text-xs text-slate-400 mt-3">Akumulasi seluruh dompet</div>
    </div>
    <div class="card kpi p-5 border-l-4 border-l-[#19B5A5]">
        <div class="text-xs font-semibold text-slate-500">Pemasukan bulan ini</div>
        <div id="kpi-income" class="text-2xl font-extrabold mt-3 text-emerald-600">Rp0</div>
        <div id="kpi-income-month" class="text-xs text-slate-400 mt-3">Bulan berjalan</div>
    </div>
    <div class="card kpi p-5 border-l-4 border-l-rose-400">
        <div class="text-xs font-semibold text-slate-500">Pengeluaran bulan ini</div>
        <div id="kpi-expense" class="text-2xl font-extrabold mt-3 text-rose-500">Rp0</div>
        <div class="text-xs text-rose-500 mt-3">Pengeluaran tercatat</div>
    </div>
    <div class="card kpi p-5 border-l-4 border-l-amber-400">
        <div class="text-xs font-semibold text-slate-500">Sisa alokasi / Arus kas</div>
        <div id="kpi-net" class="text-2xl font-extrabold mt-3 text-[#148B80]">Rp0</div>
        <div id="kpi-net-sub" class="text-xs text-slate-400 mt-3">Selisih masuk & keluar</div>
    </div>
</div>

<div class="main-grid grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- Bar Chart Arus Kas -->
    <div class="card p-5 lg:col-span-2">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="font-bold text-[#102A43]">Arus kas</h2>
                <p class="text-xs text-slate-400">Pemasukan vs pengeluaran 6 bulan terakhir</p>
            </div>
            <span class="badge bg-slate-100 text-slate-500">6 bulan terakhir</span>
        </div>
        <div class="h-64 relative">
            <canvas id="cashChart"></canvas>
        </div>
    </div>

    <!-- Donut Chart Kategori -->
    <div class="card p-5">
        <h2 class="font-bold text-[#102A43]">Kategori pengeluaran</h2>
        <p class="text-xs text-slate-400 mb-3">Bulan ini</p>
        <div class="h-44 relative">
            <canvas id="donutChart"></canvas>
        </div>
        <div id="donut-legend" class="space-y-2 text-xs mt-3 max-h-32 overflow-y-auto">
            <div class="text-slate-400 text-center py-2">Memuat kategori...</div>
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="card p-5 lg:col-span-2">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="font-bold text-[#102A43]">Transaksi terbaru</h2>
                <p class="text-xs text-slate-400">Aktivitas pengeluaran terkini</p>
            </div>
            <a href="{{ route('transaksi') }}" class="text-xs text-[#1F4E79] font-bold hover:underline">Lihat semua →</a>
        </div>
        <div class="table-wrap">
            <table class="w-full text-sm">
                <thead class="text-xs text-slate-400 border-b">
                    <tr>
                        <th class="text-left py-3">Item</th>
                        <th class="text-left">Kategori</th>
                        <th class="text-left">Tanggal</th>
                        <th class="text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody id="recent-transactions-tbody">
                    <tr><td colspan="4" class="p-6 text-center text-sm text-slate-400">Memuat transaksi...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sumber Saldo / Dompet -->
    <div class="card p-5">
        <div class="flex justify-between items-center mb-2">
            <h2 class="font-bold text-[#102A43]">Sumber saldo</h2>
            <span class="text-xs text-slate-400">Dompet aktif</span>
        </div>
        <p class="text-xs text-slate-400 mb-4">Distribusi dana aktif</p>
        <div id="wallet-distribution" class="space-y-4">
            <div class="text-slate-400 text-xs text-center py-4">Memuat dompet...</div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = localStorage.getItem('token');
if (!token) window.location.href = '/login';

const fmtRp = (num) => 'Rp' + parseInt(num || 0).toLocaleString('id-ID');

let cashChartInstance = null;
let donutChartInstance = null;

async function authFetch(url) {
    const res = await fetch(url, {
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    if (res.status === 401) {
        localStorage.removeItem('token');
        window.location.href = '/login';
        return null;
    }
    return res.json();
}

async function loadDashboard() {
    try {
        // 1. User Info Greeting
        authFetch('/api/v1/users/me').then(data => {
            if (data && data.data) {
                const hour = new Date().getHours();
                let timeGreeting = 'Selamat pagi';
                if (hour >= 11 && hour < 15) timeGreeting = 'Selamat siang';
                else if (hour >= 15 && hour < 18) timeGreeting = 'Selamat sore';
                else if (hour >= 18 || hour < 4) timeGreeting = 'Selamat malam';
                
                const firstName = data.data.name.split(' ')[0];
                document.getElementById('greeting-text').textContent = `${timeGreeting}, ${firstName} ✦`;
            }
        });

        // 2. Summary
        const sumData = await authFetch('/api/v1/summary');
        if (sumData && sumData.data) {
            const d = sumData.data;
            document.getElementById('kpi-total-balance').textContent = fmtRp(d.total_balance);
            document.getElementById('kpi-income').textContent = fmtRp(d.monthly_income);
            document.getElementById('kpi-expense').textContent = fmtRp(d.monthly_expense);
            
            const net = (d.monthly_income || 0) - (d.monthly_expense || 0);
            document.getElementById('kpi-net').textContent = fmtRp(net);
            document.getElementById('kpi-net').className = net >= 0 ? 'text-2xl font-extrabold mt-3 text-emerald-600' : 'text-2xl font-extrabold mt-3 text-rose-500';
        }

        // 3. Monthly Cash Flow Chart
        authFetch('/api/v1/reports/monthly?months=6').then(res => {
            if (!res || !res.data) return;
            const rows = res.data;
            const labels = rows.map(r => {
                const parts = r.month.split('-');
                const mIdx = parseInt(parts[1], 10) - 1;
                const mNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                return mNames[mIdx] || r.month;
            });
            const expenses = rows.map(r => Number(r.total_expense || 0));

            if (cashChartInstance) cashChartInstance.destroy();
            const ctx = document.getElementById('cashChart');
            cashChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Pengeluaran',
                            data: expenses,
                            backgroundColor: '#F07C7C',
                            borderRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: {
                        y: {
                            grid: { color: '#eef2f6' },
                            ticks: { callback: v => fmtRp(v) }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        });

        // 4. Categories Donut Chart
        authFetch('/api/v1/reports/categories?type=expense').then(res => {
            if (!res || !res.data) return;
            const items = res.data;
            const labels = items.map(i => i.name || 'Lainnya');
            const values = items.map(i => Number(i.total || 0));
            const total = values.reduce((a, b) => a + b, 0) || 1;
            const colors = ['#F59E0B', '#3B82F6', '#8B5CF6', '#19B5A5', '#F07C7C', '#1F4E79', '#10B981'];

            if (donutChartInstance) donutChartInstance.destroy();
            const ctx = document.getElementById('donutChart');
            donutChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels.length ? labels : ['Belum ada data'],
                    datasets: [{
                        data: values.length ? values : [1],
                        backgroundColor: values.length ? colors : ['#E5ECF3'],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '72%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            });

            const legendEl = document.getElementById('donut-legend');
            if (items.length === 0) {
                legendEl.innerHTML = '<div class="text-slate-400 text-center py-2">Belum ada pengeluaran bulan ini.</div>';
            } else {
                legendEl.innerHTML = items.slice(0, 5).map((it, idx) => {
                    const pct = Math.round((Number(it.total || 0) / total) * 100);
                    return `
                        <div class="flex justify-between items-center">
                            <span class="truncate"><span style="color:${colors[idx % colors.length]}">●</span> ${it.name}</span>
                            <b class="ml-2">${pct}%</b>
                        </div>
                    `;
                }).join('');
            }
        });

        // 5. Recent Expenses Table
        authFetch('/api/v1/expenses?limit=5').then(res => {
            const tbody = document.getElementById('recent-transactions-tbody');
            if (!res || !res.data || res.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="p-6 text-center text-sm text-slate-400">Belum ada transaksi pengeluaran.</td></tr>';
                return;
            }
            tbody.innerHTML = res.data.slice(0, 5).map(item => `
                <tr class="border-b hover:bg-slate-50 transition cursor-pointer" onclick="window.location.href='/transaksi/detail?id=${item.id}'">
                    <td class="py-3 font-bold text-slate-800">${item.item}</td>
                    <td class="text-slate-500">${item.category?.name || 'Umum'}</td>
                    <td class="text-slate-500 text-xs">${item.spent_at ? item.spent_at.slice(0, 10) : '-'}</td>
                    <td class="text-right text-rose-500 font-bold">-${fmtRp(item.amount)}</td>
                </tr>
            `).join('');
        });

        // 6. Incomes (Wallets) Distribution
        authFetch('/api/v1/incomes').then(res => {
            const container = document.getElementById('wallet-distribution');
            if (!res || !res.data || res.data.length === 0) {
                container.innerHTML = '<div class="text-slate-400 text-xs text-center py-4">Belum ada dompet terdaftar.</div>';
                return;
            }

            const incomes = res.data;
            const totalBalance = incomes.reduce((a, b) => a + Number(b.balance || 0), 0) || 1;
            const barColors = ['#19B5A5', '#1F4E79', '#F59E0B', '#8B5CF6'];

            container.innerHTML = incomes.map((inc, idx) => {
                const bal = Number(inc.balance || 0);
                const pct = Math.max(5, Math.min(100, Math.round((bal / totalBalance) * 100)));
                const color = barColors[idx % barColors.length];
                return `
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="font-medium text-slate-700">${inc.name} ${inc.is_primary ? '⭐' : ''}</span>
                            <b>${fmtRp(bal)}</b>
                        </div>
                        <div class="h-2 bg-slate-100 rounded-full mt-2 overflow-hidden">
                            <div class="h-2 rounded-full" style="width: ${pct}%; background-color: ${color}"></div>
                        </div>
                    </div>
                `;
            }).join('');
        });

    } catch (e) {
        console.error('Error loading dashboard:', e);
    }
}

loadDashboard();
</script>
@endpush