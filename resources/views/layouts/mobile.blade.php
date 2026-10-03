<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CatatDuit</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #E5E7EB; }
        .bg-cosmic { background-color: #0a0f1d; background-image: radial-gradient(circle at 20% 30%, rgba(31, 78, 121, 0.8) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(72, 202, 228, 0.3) 0%, transparent 50%); }
        .stars { background-image: url('data:image/svg+xml,%3Csvg width="400" height="400" xmlns="http://www.w3.org/2000/svg"%3E%3Ccircle cx="20" cy="20" r="1.5" fill="%23ffffff" opacity="0.2"/%3E%3Ccircle cx="150" cy="80" r="1" fill="%23ffffff" opacity="0.3"/%3E%3C/svg%3E'); }
        /* Hilangkan scrollbar untuk estetika mobile */
        ::-webkit-scrollbar { width: 0px; background: transparent; }
    </style>
</head>
<body class="flex justify-center items-start min-h-screen">
    <!-- Kontainer seukuran Mobile App -->
    <div class="w-full max-w-md bg-[#F6F8FB] min-h-screen shadow-2xl relative overflow-x-hidden flex flex-col">
        @yield('content')
    </div>
</body>
</html>