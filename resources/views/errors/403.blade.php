<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Akses Ditolak</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="text-center px-4">
        <p class="text-6xl font-bold text-gray-200 mb-4">403</p>
        <h1 class="text-2xl font-semibold text-gray-700 mb-2">Akses Ditolak</h1>
        <p class="text-gray-500 mb-6">{{ $message ?? 'Anda tidak memiliki akses ke halaman ini.' }}</p>
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
            ← Kembali
        </a>
    </div>
</body>
</html>
