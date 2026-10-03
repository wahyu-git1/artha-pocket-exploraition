@extends('layouts.mobile')

@section('content')
<div class="bg-[#1F4E79] pt-10 px-6 pb-4 text-white rounded-b-[24px] z-10 relative shadow-md">
    <div class="flex items-center gap-4 mb-4">
        <a href="/beranda" class="text-2xl hover:text-[#48CAE4]">←</a>
        <h2 class="text-xl font-bold">Kelola Kategori</h2>
    </div>
    
    <!-- Tabs -->
    <div class="flex justify-between border-b border-white/20">
        <button id="tab-expense" onclick="switchTab('expense')" class="flex-1 pb-3 text-center border-b-2 border-[#48CAE4] font-bold text-[#48CAE4] transition">Pengeluaran</button>
        <button id="tab-income" onclick="switchTab('income')" class="flex-1 pb-3 text-center text-gray-300 font-medium transition">Pemasukan</button>
    </div>
</div>

<div class="px-4 py-6 flex-1 overflow-y-auto pb-28">
    <h3 class="text-xs font-bold text-gray-400 mb-3 ml-2 uppercase">Bawaan Sistem</h3>
    <div id="system-categories" class="bg-white rounded-[16px] shadow-sm overflow-hidden mb-6 divide-y divide-gray-50">
        <div class="p-4 text-center text-sm text-gray-400">Memuat kategori...</div>
    </div>

    <div class="flex justify-between items-center mb-3 px-2">
        <h3 class="text-xs font-bold text-gray-400 uppercase">Kategori Kustom</h3>
        <button onclick="openModal()" class="text-xs font-bold text-[#1F4E79] hover:underline">+ Tambah</button>
    </div>
    <div id="custom-categories" class="bg-white rounded-[16px] shadow-sm overflow-hidden divide-y divide-gray-50">
        <div class="p-6 text-center text-sm text-gray-400">Belum ada kategori kustom.</div>
    </div>
</div>

<!-- Floating Action Button -->
<button onclick="openModal()" class="fixed md:absolute bottom-20 right-6 w-14 h-14 bg-[#48CAE4] rounded-full flex items-center justify-center text-white text-3xl font-bold shadow-lg shadow-teal-500/40 z-30 transition transform hover:scale-105">
    +
</button>

<!-- Bottom Navigation Bar -->
<div class="fixed md:absolute bottom-0 left-0 w-full h-[72px] bg-white border-t border-gray-100 flex justify-around items-center px-4 pb-2 text-xs font-medium text-gray-400 z-30">
    <a href="/beranda" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">🏠</span>Beranda</a>
    <a href="/transaksi" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📄</span>Transaksi</a>
    <a href="/kategori" class="flex flex-col items-center text-[#1F4E79]"><span class="text-xl mb-1">📁</span>Kategori</a>
    <a href="/laporan" class="flex flex-col items-center hover:text-[#1F4E79]"><span class="text-xl mb-1">📊</span>Laporan</a>
    <a href="javascript:void(0)" onclick="logout()" class="flex flex-col items-center hover:text-red-500"><span class="text-xl mb-1">🚪</span>Keluar</a>
</div>

<!-- Modal Tambah Kategori -->
<div id="add-modal" class="hidden fixed inset-0 bg-gray-900/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-sm rounded-[20px] p-6 shadow-2xl relative">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-[#1F4E79] text-lg">Tambah Kategori</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form id="category-form" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Kategori</label>
                <input id="cat-name" type="text" placeholder="Mis. Langganan Streaming" required class="w-full px-3 py-2.5 bg-[#F6F8FB] border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Ikon (Emoji)</label>
                <input id="cat-icon" type="text" value="📁" required class="w-full px-3 py-2.5 bg-[#F6F8FB] border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
            </div>
            <div id="bucket-group">
                <label class="block text-xs font-medium text-gray-700 mb-1">Kelompok (Bucket)</label>
                <select id="cat-bucket" class="w-full px-3 py-2.5 bg-[#F6F8FB] border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-[#1F4E79]">
                    <option value="needs">Kebutuhan (Needs)</option>
                    <option value="wants">Keinginan (Wants)</option>
                    <option value="savings_debt">Tabungan / Investasi</option>
                </select>
            </div>
            <div class="flex gap-2 pt-2">
                <button type="button" onclick="closeModal()" class="flex-1 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-semibold text-sm">Batal</button>
                <button type="submit" id="btn-save-cat" class="flex-1 py-2.5 rounded-xl bg-[#1F4E79] text-white font-semibold text-sm hover:bg-[#163859]">Simpan</button>
            </div>
        </form>
    </div>
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

    let allCategories = [];
    let activeTab = 'expense';

    async function loadCategories() {
        try {
            const res = await fetch('/api/v1/categories', {
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.status === 401) {
                localStorage.removeItem('token');
                window.location.href = '/login';
                return;
            }
            const data = await res.json();
            allCategories = data.data || [];
            renderCategories();
        } catch (e) {
            console.error(e);
        }
    }

    function renderCategories() {
        const sysContainer = document.getElementById('system-categories');
        const customContainer = document.getElementById('custom-categories');

        const filtered = allCategories.filter(c => c.type === activeTab);
        const system = filtered.filter(c => c.is_default || !c.user_id);
        const custom = filtered.filter(c => !c.is_default && c.user_id);

        sysContainer.innerHTML = system.length ? system.map(c => `
            <div class="flex justify-between items-center p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-xl shrink-0">
                        ${c.icon || '📁'}
                    </div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">${c.name}</p>
                        ${c.bucket ? `<span class="text-[10px] bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium inline-block mt-0.5">${c.bucket}</span>` : ''}
                    </div>
                </div>
                <span class="text-gray-300 text-xs">🔒 Sistem</span>
            </div>
        `).join('') : '<div class="p-4 text-center text-sm text-gray-400">Tidak ada kategori bawaan.</div>';

        customContainer.innerHTML = custom.length ? custom.map(c => `
            <div class="flex justify-between items-center p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-teal-50 rounded-full flex items-center justify-center text-xl shrink-0">
                        ${c.icon || '📁'}
                    </div>
                    <div>
                        <p class="font-bold text-sm text-gray-900">${c.name}</p>
                        ${c.bucket ? `<span class="text-[10px] bg-teal-50 text-teal-700 px-2 py-0.5 rounded-full font-medium inline-block mt-0.5">${c.bucket}</span>` : ''}
                    </div>
                </div>
                <button onclick="deleteCategory(${c.id})" class="text-red-400 hover:text-red-600 text-xs font-semibold">Hapus</button>
            </div>
        `).join('') : '<div class="p-6 text-center text-sm text-gray-400 border border-dashed border-gray-200 rounded-xl m-2">Belum ada kategori kustom.</div>';
    }

    function switchTab(tab) {
        activeTab = tab;
        const expBtn = document.getElementById('tab-expense');
        const incBtn = document.getElementById('tab-income');
        const bucketGroup = document.getElementById('bucket-group');

        if (tab === 'expense') {
            expBtn.className = 'flex-1 pb-3 text-center border-b-2 border-[#48CAE4] font-bold text-[#48CAE4] transition';
            incBtn.className = 'flex-1 pb-3 text-center text-gray-300 font-medium transition';
            if (bucketGroup) bucketGroup.classList.remove('hidden');
        } else {
            incBtn.className = 'flex-1 pb-3 text-center border-b-2 border-[#48CAE4] font-bold text-[#48CAE4] transition';
            expBtn.className = 'flex-1 pb-3 text-center text-gray-300 font-medium transition';
            if (bucketGroup) bucketGroup.classList.add('hidden');
        }
        renderCategories();
    }

    function openModal() {
        document.getElementById('add-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('add-modal').classList.add('hidden');
        document.getElementById('category-form').reset();
    }

    document.getElementById('category-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-save-cat');
        btn.innerText = 'Menyimpan...';
        btn.disabled = true;

        const payload = {
            name: document.getElementById('cat-name').value,
            icon: document.getElementById('cat-icon').value || '📁',
            type: activeTab,
            bucket: activeTab === 'expense' ? document.getElementById('cat-bucket').value : null,
            color: '#1F4E79'
        };

        try {
            const res = await fetch('/api/v1/categories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeModal();
                loadCategories();
            } else {
                const err = await res.json();
                alert(err.message || 'Gagal membuat kategori.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        } finally {
            btn.innerText = 'Simpan';
            btn.disabled = false;
        }
    });

    async function deleteCategory(id) {
        if (!confirm('Hapus kategori ini?')) return;
        try {
            const res = await fetch(`/api/v1/categories/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': `Bearer ${token}` }
            });
            if (res.ok) {
                loadCategories();
            } else {
                alert('Gagal menghapus kategori.');
            }
        } catch (e) {
            alert('Terjadi kesalahan jaringan.');
        }
    }

    loadCategories();
</script>
@endsection