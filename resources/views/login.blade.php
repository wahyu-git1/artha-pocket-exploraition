<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CatatDuit</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        /* Elemen Antariksa / Nebula Background */
        .bg-space {
            background-color: #0a0f1d;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(31, 78, 121, 0.4) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(72, 202, 228, 0.15) 0%, transparent 40%);
        }
        
        /* Taburan Bintang Halus */
        .stars {
            background-image: url('data:image/svg+xml,%3Csvg width="400" height="400" xmlns="http://www.w3.org/2000/svg"%3E%3Ccircle cx="20" cy="20" r="1.5" fill="%23ffffff" opacity="0.2"/%3E%3Ccircle cx="150" cy="80" r="1" fill="%23ffffff" opacity="0.3"/%3E%3Ccircle cx="300" cy="250" r="2" fill="%23ffffff" opacity="0.1"/%3E%3Ccircle cx="80" cy="320" r="1" fill="%23ffffff" opacity="0.4"/%3E%3C/svg%3E');
        }
    </style>
</head>
<body class="bg-space stars min-h-screen flex items-center justify-center p-4">

    <!-- Card Putih Khas Fintech -->
    <div class="bg-white w-full max-w-md rounded-[16px] shadow-2xl p-8 relative overflow-hidden">
        
        <!-- Aksen Garis Atas (Teal) -->
        <div class="absolute top-0 left-0 w-full h-1 bg-[#48CAE4]"></div>

        <!-- Logo & Tagline -->
        <div class="text-center mb-8 mt-2">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#F6F8FB] mb-4 shadow-sm border border-gray-100">
                <!-- Ikon Planet / Roket Minimalis -->
                <svg class="w-6 h-6 text-[#1F4E79]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-[#1F4E79]">CatatDuit</h1>
            <p class="text-sm text-gray-500 mt-1">Catat, rencanakan, tenang.</p>
        </div>

        <!-- Form -->
        <form action="#" method="POST" class="space-y-5">
            @csrf
            
            <!-- Jika kamu membuat halaman Register, cukup tambahkan blok div ini untuk "Nama" di atas Email -->
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" placeholder="nama@email.com" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1F4E79] focus:border-[#1F4E79] focus:outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi</label>
                <div class="relative">
                    <input type="password" placeholder="••••••••" class="w-full px-4 py-3 bg-[#F6F8FB] border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#1F4E79] focus:border-[#1F4E79] focus:outline-none transition">
                    <!-- Tombol Show/Hide -->
                    <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-[#1F4E79]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Minimal 8 karakter
                </p>
            </div>

            <button type="submit" class="w-full bg-[#1F4E79] hover:bg-[#163859] text-white font-semibold py-3 px-4 rounded-xl shadow-md shadow-blue-900/20 transition duration-200">
                Masuk
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="#" class="text-sm text-gray-600 hover:text-[#1F4E79] font-medium transition">
                Belum punya akun? <span class="text-[#48CAE4] hover:underline">Daftar</span>
            </a>
        </div>
    </div>
</body>
</html>