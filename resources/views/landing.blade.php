<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CatatDuit - Catat, rencanakan, tenang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .bg-deepspace { background-color: #060913; background-image: radial-gradient(circle at 50% 0%, rgba(31, 78, 121, 0.8) 0%, transparent 60%), radial-gradient(circle at 10% 90%, rgba(72, 202, 228, 0.2) 0%, transparent 40%); }
        .stars { background-image: url('data:image/svg+xml,%3Csvg width="400" height="400" xmlns="http://www.w3.org/2000/svg"%3E%3Ccircle cx="50" cy="50" r="1.5" fill="%23ffffff" opacity="0.3"/%3E%3Ccircle cx="250" cy="150" r="1" fill="%23ffffff" opacity="0.4"/%3E%3Ccircle cx="100" cy="300" r="2" fill="%23ffffff" opacity="0.2"/%3E%3C/svg%3E'); animation: twinkle 4s infinite alternate; }
    </style>
</head>
<body class="bg-deepspace stars min-h-screen flex flex-col items-center justify-center text-white p-6">
    
    <div class="text-center max-w-lg mx-auto z-10">
        <!-- Logo -->
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-white/10 backdrop-blur-md mb-6 border border-white/20 shadow-[0_0_30px_rgba(72,202,228,0.3)]">
            <span class="text-4xl">🚀</span>
        </div>
        
        <h1 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight">CatatDuit</h1>
        <p class="text-lg text-gray-300 mb-10 opacity-90">Catat, rencanakan, tenang. <br> Kelola keuanganmu secepat kecepatan cahaya.</p>
        
        <!-- Tombol Aksi -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/login" class="bg-[#1F4E79] hover:bg-[#163859] text-white font-semibold py-4 px-8 rounded-2xl shadow-lg shadow-blue-900/50 transition transform hover:-translate-y-1">
                🌐 Masuk via Web
            </a>
            <a href="#" class="bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white font-semibold py-4 px-8 rounded-2xl transition transform hover:-translate-y-1">
                📱 Unduh Aplikasi Android 
            </a>
        </div>
    </div>

</body>
</html>