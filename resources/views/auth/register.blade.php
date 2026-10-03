<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - CatatDuit</title>
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
        <form action="#" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                <input type="text" placeholder="Rina" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1F4E79] focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" placeholder="nama@email.com" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1F4E79] focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                <div class="relative">
                    <input type="password" placeholder="••••••••" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1F4E79] focus:outline-none">
                    <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400">Tampilkan</button>
                </div>
                <p class="text-xs text-gray-400 mt-2">Minimal 8 karakter</p>
            </div>
            <button class="w-full bg-[#1F4E79] hover:bg-[#163859] text-white font-semibold py-3 px-4 rounded-xl mt-2">Daftar</button>
        </form>
        <div class="mt-6 text-center">
            <a href="/login" class="text-sm text-gray-600">Sudah punya akun? <span class="text-[#48CAE4] font-medium hover:underline">Masuk</span></a>
        </div>
    </div>
</body>
</html>