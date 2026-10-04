<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CatatDuit</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .bg-cosmic { background-color: #0a0f1d; background-image: radial-gradient(circle at 20% 30%, rgba(31, 78, 121, 0.6) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(72, 202, 228, 0.2) 0%, transparent 50%); }
        .stars { background-image: url('data:image/svg+xml,%3Csvg width="400" height="400" xmlns="http://www.w3.org/2000/svg"%3E%3Ccircle cx="20" cy="20" r="1.5" fill="%23ffffff" opacity="0.2"/%3E%3Ccircle cx="150" cy="80" r="1" fill="%23ffffff" opacity="0.3"/%3E%3C/svg%3E'); }
    </style>
</head>
<body class="bg-cosmic stars min-h-screen flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-[16px] shadow-2xl p-8 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-[#48CAE4]"></div>
        <div class="text-center mb-6 mt-2">
            <h1 class="text-2xl font-bold text-[#1F4E79]">CatatDuit</h1>
            <p class="text-sm text-gray-500 mt-1">Catat, rencanakan, tenang.</p>
        </div>
        <form id="register-form" action="#" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input id="name" type="text" placeholder="Rina" required class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1F4E79] focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input id="email" type="email" placeholder="nama@email.com" required class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1F4E79] focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                <div class="relative">
                    <input id="password" type="password" placeholder="••••••••" required class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1F4E79] focus:outline-none">
                    <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#1F4E79]">Tampilkan</button>
                </div>
                <p class="text-xs text-gray-400 mt-2">Minimal 8 karakter</p>
            </div>
            <button type="submit" id="btn-register" class="w-full bg-[#1F4E79] hover:bg-[#163859] text-white font-semibold py-3 px-4 rounded-xl mt-2 transition duration-200">Daftar</button>
        </form>
        <div class="mt-6 text-center">
            <a href="/login" class="text-sm text-gray-600">Sudah punya akun? <span class="text-[#48CAE4] font-medium hover:underline">Masuk</span></a>
        </div>
    </div>

    <script>
        document.getElementById('register-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-register');
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            btn.innerText = 'Mendaftar...';
            btn.disabled = true;

            try {
                const res = await fetch('/api/v1/auth/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ name, email, password })
                });

                const data = await res.json();

                if (res.ok) {
                    if (data.data && data.data.access_token) {
                        localStorage.setItem('token', data.data.access_token);
                    }
                    alert('Pendaftaran berhasil! Mengarahkan ke beranda...');
                    window.location.href = '/beranda';
                } else {
                    let errMsg = data.error?.message || data.message || 'Pendaftaran gagal.';
                    if (data.error?.details && typeof data.error.details === 'object') {
                        const detailMessages = Object.values(data.error.details).flat().join('\n');
                        if (detailMessages) errMsg += '\n' + detailMessages;
                    }
                    alert(errMsg);
                    btn.innerText = 'Daftar';
                    btn.disabled = false;
                }
            } catch (err) {
                alert('Terjadi kesalahan koneksi.');
                btn.innerText = 'Daftar';
                btn.disabled = false;
            }
        });

        // Toggle password
        document.getElementById('toggle-password').addEventListener('click', function() {
            const input = document.getElementById('password');
            if (input.type === 'password') {
                input.type = 'text';
                this.innerText = 'Sembunyikan';
            } else {
                input.type = 'password';
                this.innerText = 'Tampilkan';
            }
        });
    </script>
</body>
</html>