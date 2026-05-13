<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Document Control System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #080f1e;
        }
        .font-serif { font-family: 'Instrument Serif', serif; }

        /* ── LEFT PANEL ── */
        .left-panel {
            position: relative;
            width: 52%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }

        /* hero-bg sebagai background kiri — pakai <img> agar Blade bisa proses asset() */
        .left-photo {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
            object-fit: cover; object-position: center top;
            z-index: 0;
        }

        /* Overlay gelap konsisten dengan welcome page */
        .left-overlay {
            position: absolute; inset: 0; z-index: 1;
            background:
                linear-gradient(to bottom, rgba(8,15,30,0.55) 0%, rgba(8,15,30,0.3) 40%, rgba(8,15,30,0.85) 100%),
                linear-gradient(to right, rgba(8,15,30,0.6) 0%, transparent 100%);
        }

        .left-content {
            position: relative; z-index: 2;
            display: flex; flex-direction: column;
            justify-content: space-between;
            height: 100%; padding: 40px 48px;
        }

        /* Dots texture */
        .dots-layer {
            position: absolute; inset: 0; z-index: 1;
            background-image: radial-gradient(circle, rgba(255,255,255,0.045) 1px, transparent 1px);
            background-size: 26px 26px;
            pointer-events: none;
        }

        /* Glass stat cards */
        .stat-card {
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 16px 20px;
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0b1220;
            padding: 40px 32px;
            position: relative;
        }

        /* Subtle glow behind form */
        .right-panel::before {
            content: '';
            position: absolute;
            top: 20%; left: 50%; transform: translateX(-50%);
            width: 340px; height: 340px;
            background: rgba(14,165,233,0.06);
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
        }

        /* Input styling */
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #334155; pointer-events: none;
        }
        .input-field {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 12px;
            padding: 13px 16px 13px 42px;
            font-size: 14px;
            color: #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            outline: none;
            transition: all 0.2s ease;
        }
        .input-field::placeholder { color: #334155; }
        .input-field:focus {
            border-color: #0ea5e9;
            background: rgba(14,165,233,0.05);
            box-shadow: 0 0 0 3px rgba(14,165,233,0.1);
        }

        /* Password toggle */
        .toggle-pw {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            cursor: pointer; color: #334155; transition: color 0.2s;
            background: none; border: none; padding: 0;
        }
        .toggle-pw:hover { color: #7dd3fc; }

        /* Submit button */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            box-shadow: 0 4px 24px rgba(14,165,233,0.35);
            border: none; border-radius: 12px;
            padding: 14px;
            color: white; font-weight: 700; font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: all 0.25s ease;
            letter-spacing: 0.3px;
        }
        .btn-login:hover {
            box-shadow: 0 8px 32px rgba(14,165,233,0.5);
            transform: translateY(-1px);
        }
        .btn-login:active { transform: translateY(0); }

        /* Checkbox custom */
        .checkbox-custom {
            width: 16px; height: 16px;
            border: 1.5px solid rgba(255,255,255,0.15);
            border-radius: 4px;
            background: rgba(255,255,255,0.04);
            accent-color: #0ea5e9;
            cursor: pointer;
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .form-appear { animation: fadeInUp 0.55s ease 0.1s both; }

        @keyframes floatY {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }
        .float-1 { animation: floatY 6s ease-in-out infinite; }
        .float-2 { animation: floatY 8s ease-in-out infinite 2s; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px;
            color: rgba(255,255,255,0.12); font-size: 11px;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px;
            background: rgba(255,255,255,0.07);
        }

        /* Label */
        .field-label {
            display: block; font-size: 12px; font-weight: 600;
            color: #94a3b8; margin-bottom: 7px; letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        /* Error / success alert */
        .alert-error {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 10px; padding: 12px 16px;
            font-size: 13px; color: #fca5a5;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-success {
            background: rgba(34,197,94,0.08);
            border: 1px solid rgba(34,197,94,0.2);
            border-radius: 10px; padding: 12px 16px;
            font-size: 13px; color: #86efac;
            display: flex; align-items: flex-start; gap: 10px;
        }

        @media (max-width: 900px) {
            .left-panel { display: none; }
            .right-panel { background: #080f1e; }
        }
    </style>
</head>
<body>

{{-- ══════════ LEFT PANEL ══════════ --}}
<div class="left-panel hidden lg:block">

    {{-- Hero photo background —  pakai <img> bukan CSS url() --}}
    <img src="{{ asset('asset/images/hero-bg.jpg') }}" alt="" class="left-photo">

    {{-- Dots texture --}}
    <div class="dots-layer"></div>

    {{-- Dark overlay --}}
    <div class="left-overlay"></div>

    {{-- Content --}}
    <div class="left-content">

        {{-- TOP: Logo --}}
        <div style="display:flex;align-items:center;gap:10px;">
            <img src="{{ asset('asset/images/logo.png') }}" alt="Logo"
                 style="width:40px;height:40px;border-radius:10px;object-fit:contain;">
            <div>
                <p style="font-size:14px;font-weight:700;color:white;line-height:1.2;">Document Control</p>
                <p style="font-size:10px;color:#7dd3fc;font-weight:600;letter-spacing:.8px;">SISTEM MANAJEMEN DOKUMEN</p>
            </div>
        </div>

        {{-- MIDDLE: Floating cards + headline --}}
        <div>
            {{-- Stat card 1 --}}
            <div class="stat-card float-1" style="width:260px;margin-bottom:14px;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <div style="width:7px;height:7px;background:#4ade80;border-radius:50%;box-shadow:0 0 6px #4ade80;"></div>
                    <span style="font-size:12px;color:#cbd5e1;font-weight:500;">SOP-QA-007 Approved</span>
                </div>
                <p style="font-size:11px;color:#475569;">Disetujui oleh Head QA · barusan</p>
            </div>

            {{-- Stat card 2 --}}
            <div class="stat-card float-2" style="width:280px;margin-bottom:36px;">
                <p style="font-size:10px;color:#475569;font-weight:600;letter-spacing:.8px;text-transform:uppercase;margin-bottom:10px;">Audit Trail</p>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach([['upload','#38bdf8'],['submit','#fbbf24'],['approve','#4ade80']] as [$action,$color])
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div style="width:6px;height:6px;border-radius:50%;background:{{ $color }};flex-shrink:0;"></div>
                        <span style="font-size:12px;color:#64748b;">{{ ucfirst($action) }} — {{ now()->subMinutes(rand(1,60))->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Headline --}}
            <h2 class="font-serif" style="font-size:38px;font-weight:400;color:white;line-height:1.1;margin-bottom:12px;">
                Sistem Kontrol<br>
                <em style="color:#7dd3fc;">Dokumen Modern</em>
            </h2>
            <p style="font-size:13px;color:rgba(255,255,255,0.4);line-height:1.75;max-width:300px;">
                Kelola, distribusi, dan arsipkan dokumen perusahaan secara efisien sesuai standar ISO 9001:2015.
            </p>
        </div>

        {{-- BOTTOM: copyright --}}
        <p style="font-size:11px;color:rgba(255,255,255,0.18);">© {{ date('Y') }} · Document Control System</p>
    </div>
</div>

{{-- ══════════ RIGHT PANEL (Form) ══════════ --}}
<div class="right-panel">
    <div class="form-appear" style="width:100%;max-width:400px;position:relative;z-index:1;">

        {{-- Mobile logo --}}
        <div class="lg:hidden" style="display:flex;align-items:center;gap:10px;margin-bottom:32px;">
            <img src="{{ asset('asset/images/logo.png') }}" alt="Logo"
                 style="width:36px;height:36px;border-radius:9px;object-fit:contain;">
            <span style="font-size:14px;font-weight:700;color:white;">Document Control <span style="color:#38bdf8;">DCS</span></span>
        </div>

        {{-- Heading --}}
        <div style="margin-bottom:32px;">
            <p style="font-size:11px;font-weight:700;color:#38bdf8;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px;">Selamat datang kembali</p>
            <h1 class="font-serif" style="font-size:32px;font-weight:400;color:white;line-height:1.15;margin-bottom:6px;">
                Masuk ke Akun<br><em>Anda</em>
            </h1>
            <p style="font-size:13px;color:rgba(255,255,255,0.3);">Lanjutkan kelola dokumen perusahaan</p>
        </div>

        {{-- Alerts --}}
        @if (session('status'))
        <div class="alert-success" style="margin-bottom:20px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('status') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="alert-error" style="margin-bottom:20px;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $errors->first() }}
        </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('login') }}" style="display:flex;flex-direction:column;gap:18px;">
            @csrf

            {{-- Email --}}
            <div>
                <label for="email" class="field-label">Email</label>
                <div class="input-wrap">
                    <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           required autofocus autocomplete="email"
                           class="input-field" placeholder="Masukkan Email">
                </div>
            </div>

            {{-- Password --}}
            <div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:7px;">
                    <label for="password" class="field-label" style="margin-bottom:0;">Password</label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       style="font-size:11px;color:#38bdf8;font-weight:500;text-decoration:none;transition:color 0.2s;"
                       onmouseover="this.style.color='#7dd3fc'" onmouseout="this.style.color='#38bdf8'">Lupa password?</a>
                    @endif
                </div>
                <div class="input-wrap">
                    <svg class="input-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <input id="password" type="password" name="password"
                           required autocomplete="current-password"
                           class="input-field" placeholder="Masukkan Password" id="pw-input">
                    <button type="button" class="toggle-pw" onclick="togglePw()" aria-label="Toggle password">
                        <svg id="eye-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Remember me --}}
            <div style="display:flex;align-items:center;gap:9px;">
                <input type="checkbox" id="remember_me" name="remember" class="checkbox-custom">
                <label for="remember_me" style="font-size:13px;color:rgba(255,255,255,0.35);cursor:pointer;">Ingat saya di perangkat ini</label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login" style="margin-top:4px;">
                Masuk ke Sistem
            </button>
        </form>

        {{-- Divider --}}
        <div class="divider" style="margin:24px 0;">atau</div>

        {{-- Back to home --}}
        <a href="{{ url('/') }}"
           style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:13px;border-radius:12px;border:1px solid rgba(255,255,255,0.07);background:rgba(255,255,255,0.02);color:rgba(255,255,255,0.35);font-size:13px;font-weight:500;text-decoration:none;transition:all 0.2s;"
           onmouseover="this.style.borderColor='rgba(56,189,248,0.3)';this.style.color='#7dd3fc';"
           onmouseout="this.style.borderColor='rgba(255,255,255,0.07)';this.style.color='rgba(255,255,255,0.35)';">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Beranda
        </a>

        <p style="text-align:center;font-size:11px;color:rgba(255,255,255,0.12);margin-top:28px;">
            © {{ date('Y') }} Document Control System · All rights reserved
        </p>
    </div>
</div>

<script>
function togglePw() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-icon');
    const isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    icon.innerHTML = isText
        ? '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>'
        : '<path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
}
</script>
</body>
</html>