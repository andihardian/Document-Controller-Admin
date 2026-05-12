<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — NexaStudio DCS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }

        .left-panel {
            background: linear-gradient(145deg, #0f172a 0%, #0c2a4a 50%, #0f172a 100%);
        }

        .dots-grid {
            background-image: radial-gradient(circle, rgba(148,163,184,0.15) 1px, transparent 1px);
            background-size: 24px 24px;
        }

        .input-field {
            transition: all 0.2s ease;
        }
        .input-field:focus {
            border-color: #0ea5e9;
            box-shadow: 0 0 0 3px rgba(14,165,233,0.12);
        }

        .btn-login {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            box-shadow: 0 4px 20px rgba(14,165,233,0.35);
            transition: all 0.25s ease;
        }
        .btn-login:hover {
            box-shadow: 0 8px 30px rgba(14,165,233,0.5);
            transform: translateY(-1px);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        @keyframes fadeInRight {
            from { opacity: 0; transform: translateX(20px); }
            to   { opacity: 1; transform: translateX(0); }
        }
        .animate-in { animation: fadeInRight 0.6s ease forwards; }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }
        .float { animation: float 5s ease-in-out infinite; }
        .float-2 { animation: float 7s ease-in-out infinite 1.5s; }
    </style>
</head>
<body class="antialiased min-h-screen flex">

    {{-- ─── LEFT PANEL ─────────────────────────────── --}}
    <div class="left-panel hidden lg:flex flex-col justify-between w-1/2 p-12 relative overflow-hidden">

        {{-- Dot grid --}}
        <div class="dots-grid absolute inset-0"></div>

        {{-- Glow --}}
        <div class="absolute top-1/3 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Logo --}}
        <div class="relative z-10 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-lg shadow-sky-900/50">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-white font-semibold">Document Control</span>
                <span class="text-sky-400 font-semibold"> DCS</span>
            </div>
        </div>

        {{-- Center content --}}
        <div class="relative z-10">

            {{-- Floating stat cards --}}
            <div class="float mb-8">
                <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-4 w-64 mb-4">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-xs text-slate-300">SOP-QA-007 Approved</span>
                    </div>
                    <p class="text-xs text-slate-500">Disetujui oleh Head QA · barusan</p>
                </div>
            </div>

            <div class="float-2">
                <div class="bg-white/5 backdrop-blur border border-white/10 rounded-2xl p-5 w-72">
                    <p class="text-xs text-slate-400 mb-1">Audit Trail</p>
                    <div class="space-y-2">
                        @foreach(['upload', 'submit', 'approve'] as $action)
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 rounded-full {{ $action === 'approve' ? 'bg-green-400' : ($action === 'submit' ? 'bg-yellow-400' : 'bg-sky-400') }}"></div>
                            <span class="text-xs text-slate-400">{{ ucfirst($action) }} — {{ now()->subMinutes(rand(1,60))->diffForHumans() }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h2 class="font-display text-3xl text-white font-bold leading-snug mb-3">
                    Sistem Kontrol<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-blue-400">Dokumen Modern</span>
                </h2>
                <p class="text-sm text-slate-400 leading-relaxed max-w-xs">
                    Kelola, distribusi, dan arsipkan dokumen perusahaan secara efisien sesuai standar ISO 9001:2015.
                </p>
            </div>
        </div>

        {{-- Bottom --}}
        <div class="relative z-10">
            <p class="text-xs text-slate-600">© {{ date('Y') }} · Document Control System</p>
        </div>
    </div>

    {{-- ─── RIGHT PANEL (Form) ──────────────────────── --}}
    <div class="flex-1 flex items-center justify-center bg-slate-50 px-6 py-12">
        <div class="w-full max-w-md animate-in">

            {{-- Mobile logo --}}
            <div class="lg:hidden flex items-center gap-2 mb-8">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="font-semibold text-slate-800">NexaStudio <span class="text-sky-500">DCS</span></span>
            </div>

            <div class="mb-8">
                <h1 class="text-2xl font-bold text-slate-800 mb-1">Selamat datang kembali</h1>
                <p class="text-sm text-slate-500">Masuk ke akun Anda untuk melanjutkan</p>
            </div>

            {{-- Session Status --}}
            @if (session('status'))
            <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl">
                {{ session('status') }}
            </div>
            @endif

            {{-- Error --}}
            @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="input-field w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 bg-white placeholder-slate-400 focus:outline-none"
                           placeholder="Masukkan Email">
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-sky-500 hover:text-sky-600">Lupa password?</a>
                        @endif
                    </div>
                    <input id="password" type="password" name="password" required
                           class="input-field w-full border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-800 bg-white placeholder-slate-400 focus:outline-none"
                           placeholder="Masukkan Password">
                </div>

                {{-- Remember me --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember_me" name="remember"
                           class="w-4 h-4 text-sky-500 border-slate-300 rounded focus:ring-sky-500">
                    <label for="remember_me" class="text-sm text-slate-600">Ingat saya</label>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-login w-full text-white font-semibold py-3.5 rounded-xl text-sm mt-2">
                    Masuk ke Sistem
                </button>
            </form>

        

            <p class="text-center text-xs text-slate-400 mt-6">
                <a href="{{ url('/') }}" class="text-sky-500 hover:underline">← Kembali ke Beranda</a>
            </p>
        </div>
    </div>

</body>
</html>
