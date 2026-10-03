@extends('layouts.mobile')

@section('content')
<div class="bg-cosmic stars pt-12 pb-24 px-6 text-white rounded-b-[32px]">
    <div class="flex gap-2 mb-8 justify-center">
        <div class="h-2 w-8 bg-[#48CAE4] rounded-full"></div>
        <div class="h-2 w-8 bg-white/20 rounded-full"></div>
        <div class="h-2 w-8 bg-white/20 rounded-full"></div>
    </div>
    <h2 class="text-2xl font-bold">Tambahkan pendapatanmu</h2>
    <p class="text-sm text-gray-300 mt-2 opacity-90">Mari mulai dengan mencatat dompet atau sumber uang utamamu.</p>
</div>

<form id="onboarding-form" class="flex-1 flex flex-col justify-between">
    @csrf
    <div class="px-6 -mt-16 mb-8">
        <div class="bg-white rounded-[16px] shadow-sm p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama sumber pendapatan</label>
                <input id="inc-name" type="text" placeholder="Mis. Rekening Utama / Gaji" required class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl focus:ring-2 focus:ring-[#1F4E79] outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nominal biasa</label>
                <div class="relative">
                    <span class="absolute left-4 top-3 text-gray-500 font-medium">Rp</span>
                    <input id="inc-amount" type="number" placeholder="5000000" class="w-full pl-12 pr-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl focus:ring-2 focus:ring-[#1F4E79] outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Frekuensi</label>
                    <select id="inc-freq" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl focus:ring-2 focus:ring-[#1F4E79] outline-none text-sm">
                        <option value="monthly">Bulanan</option>
                        <option value="weekly">Mingguan</option>
                        <option value="irregular">Tidak tetap</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tgl Gajian</label>
                    <input id="inc-payday" type="number" placeholder="25" min="1" max="31" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-100 rounded-xl focus:ring-2 focus:ring-[#1F4E79] outline-none">
                </div>
            </div>
            <div class="pt-2 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Jadikan dompet utama</span>
                <input id="inc-primary" type="checkbox" checked class="w-5 h-5 accent-[#48CAE4] cursor-pointer">
            </div>
        </div>
    </div>

    <div class="p-6 bg-white border-t border-gray-100">
        <button type="submit" id="btn-submit" class="w-full bg-[#1F4E79] hover:bg-[#163859] text-white font-semibold py-4 rounded-xl shadow-lg transition duration-200">
            Mulai Gunakan CatatDuit
        </button>
    </div>
</form>

<script>
    const token = localStorage.getItem('token');
    if (!token) window.location.href = '/login';

    document.getElementById('onboarding-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-submit');
        btn.innerText = 'Menyimpan...';
        btn.disabled = true;

        const payload = {
            name: document.getElementById('inc-name').value,
            default_amount: parseInt(document.getElementById('inc-amount').value || 0),
            frequency: document.getElementById('inc-freq').value,
            pay_day: document.getElementById('inc-payday').value ? parseInt(document.getElementById('inc-payday').value) : null,
            is_primary: document.getElementById('inc-primary').checked,
            is_active: true
        };

        try {
            const res = await fetch('/api/v1/incomes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                window.location.href = '/beranda';
            } else {
                const data = await res.json();
                alert(data.message || 'Gagal menyimpan sumber pendapatan.');
                btn.innerText = 'Mulai Gunakan CatatDuit';
                btn.disabled = false;
            }
        } catch (err) {
            alert('Terjadi kesalahan jaringan.');
            btn.innerText = 'Mulai Gunakan CatatDuit';
            btn.disabled = false;
        }
    });
</script>
@endsection