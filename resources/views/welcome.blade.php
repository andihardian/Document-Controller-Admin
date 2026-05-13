<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Control System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; background: #0f172a; }
        .font-serif { font-family: 'Instrument Serif', serif; }

        /* ── NAVBAR ── */
        .navbar {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            backdrop-filter: blur(20px);
            background: rgba(15, 23, 42, 0.55);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        /* ── HERO ── */
        .hero-section {
            position: relative;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            overflow: hidden;
        }

        /* Photo background */
        .hero-photo {
            position: absolute; inset: 0;
            background-image: url('{{ asset("asset/images/hero-bg.jpg") }}');
            background-size: cover;
            background-position: center top;
            z-index: 0;
        }

        /* Gradient overlays — same palette as dashboard (slate-900 → blue-900) */
        .hero-overlay {
            position: absolute; inset: 0; z-index: 1;
            background:
                linear-gradient(to right, rgba(15,23,42,0.82) 0%, rgba(15,23,42,0.45) 55%, rgba(15,23,42,0.15) 100%),
                linear-gradient(to top, rgba(15,23,42,0.9) 0%, transparent 60%);
        }

        /* Badge */
        .badge-official {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            border-radius: 9999px;
            padding: 6px 16px;
            font-size: 11px; font-weight: 600; color: #bae6fd; letter-spacing: 1px;
        }

        /* Profile card — glassmorphism */
        .profile-card {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px;
            padding: 28px;
            width: 300px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.4);
        }

        /* Buttons */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            box-shadow: 0 4px 20px rgba(14,165,233,0.4);
            color: white; font-weight: 600; font-size: 14px;
            padding: 13px 28px; border-radius: 9999px;
            text-decoration: none; transition: all 0.25s;
        }
        .btn-primary:hover { box-shadow: 0 8px 30px rgba(14,165,233,0.6); transform: translateY(-2px); }

        .btn-ghost {
            display: inline-flex; align-items: center; gap: 8px;
            border: 1.5px solid rgba(255,255,255,0.25);
            color: rgba(255,255,255,0.85); font-weight: 500; font-size: 14px;
            padding: 12px 24px; border-radius: 9999px;
            text-decoration: none; transition: all 0.2s;
            backdrop-filter: blur(8px);
        }
        .btn-ghost:hover { border-color: #38bdf8; color: #38bdf8; background: rgba(56,189,248,0.06); }

        /* Feature cards below hero */
        .feature-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 24px;
            transition: all 0.3s;
        }
        .feature-card:hover {
            background: rgba(14,165,233,0.07);
            border-color: rgba(14,165,233,0.3);
            transform: translateY(-4px);
        }

        /* Step cards */
        .step-num {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, #0ea5e9, #0369a1);
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 12px; letter-spacing: 1px;
            margin: 0 auto 14px;
            box-shadow: 0 6px 20px rgba(14,165,233,0.35);
        }

        /* Role cards */
        .role-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px; padding: 24px;
            transition: all 0.3s;
        }
        .role-card:hover { transform: translateY(-4px); border-color: rgba(14,165,233,0.3); }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(145deg, #0f172a, #0c2244, #0f172a);
            position: relative; overflow: hidden;
        }

        /* Logo box */
        .logo-box {
            width: 38px; height: 38px; border-radius: 10px;
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 14px rgba(14,165,233,0.4);
            flex-shrink: 0;
        }

        /* App logo image slot — swap with <img> when you have an actual logo file */
        .logo-img-slot {
            width: 38px; height: 38px; border-radius: 10px;
            overflow: hidden; flex-shrink: 0;
        }

        /* Info row in profile card */
        .info-row {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            font-size: 13px; color: #cbd5e1;
        }

        /* Nav link */
        .nav-link { font-size: 13px; color: rgba(255,255,255,0.65); font-weight: 500; text-decoration: none; transition: color 0.2s; }
        .nav-link:hover { color: white; }
        .nav-link.active { color: white; font-weight: 600; }

        /* Dots bg */
        .dots-bg {
            background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* Animations */
        @keyframes fadeUp { from { opacity:0; transform:translateY(22px); } to { opacity:1; transform:translateY(0); } }
        .fade-1 { animation: fadeUp 0.65s ease 0.1s both; }
        .fade-2 { animation: fadeUp 0.65s ease 0.25s both; }
        .fade-3 { animation: fadeUp 0.65s ease 0.4s both; }
        .fade-4 { animation: fadeUp 0.65s ease 0.55s both; }
        .fade-5 { animation: fadeUp 0.65s ease 0.7s both; }

        .reveal { opacity:0; transform:translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .reveal.show { opacity:1; transform:translateY(0); }

        a { text-decoration: none; }
    </style>
</head>
<body>

{{-- ══════════════════ NAVBAR ══════════════════ --}}
<nav class="navbar">
    <div style="max-width:1160px;margin:0 auto;padding:0 24px;height:66px;display:flex;align-items:center;justify-content:space-between;">

            {{-- Logo + App Name --}}
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="logo-img-slot">
                <img src="{{ asset('asset/images/logo.png') }}" alt="Logo" style="width:38px;height:38px;object-fit:contain;border-radius:10px;">
            </div>
            <div>
                <p style="font-size:14px;font-weight:700;color:white;margin:0;line-height:1.2;">Document Control</p>
                <p style="font-size:10px;color:#7dd3fc;margin:0;font-weight:500;letter-spacing:.5px;">SISTEM MANAJEMEN DOKUMEN</p>
            </div>
        </div>

        {{-- Nav Links --}}
        <div style="display:flex;align-items:center;gap:28px;">
            <a href="#" class="nav-link active">Home</a>
            <a href="#fitur" class="nav-link">Fitur</a>
            <a href="#alur" class="nav-link">Alur Kerja</a>
            <a href="#role" class="nav-link">Role</a>
            @auth
            <a href="{{ route('dashboard') }}" class="btn-primary" style="padding:9px 20px;">Dashboard</a>
            @else
            <a href="{{ route('login') }}" class="nav-link">Masuk</a>
            <a href="{{ route('login') }}" class="btn-primary" style="padding:9px 20px;">Mulai Sekarang</a>
            @endauth
        </div>
    </div>
</nav>

{{-- ══════════════════ HERO — photo background ══════════════════ --}}
<section class="hero-section">

    {{-- Background photo --}}
    {{--
        Replace the CSS background-image in .hero-photo with your actual photo.
        Or swap the div below with an <img> tag:
        <img src="{{ asset('images/hero-bg.jpg') }}" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;" alt="">
    --}}
    <div class="hero-photo"></div>
    <div class="hero-overlay"></div>

    {{-- Hero content --}}
    <div style="position:relative;z-index:2;max-width:1160px;margin:0 auto;padding:0 24px 80px;width:100%;display:flex;align-items:flex-end;justify-content:space-between;gap:40px;flex-wrap:wrap;">

        {{-- Left — main text --}}
        <div style="flex:1;min-width:280px;max-width:620px;">

            <div class="badge-official fade-1" style="margin-bottom:24px;">
                <div style="width:6px;height:6px;background:#38bdf8;border-radius:50%;"></div>
                Website Resmi Sistem
            </div>

            <h1 class="font-serif fade-2" style="font-size:clamp(42px,5.5vw,72px);font-weight:400;color:white;line-height:1.08;margin:0 0 20px;">
                Kelola Dokumen<br>
                <em style="color:#7dd3fc;">Lebih Terstruktur</em><br>
                &amp; Terkontrol
            </h1>

            <p class="fade-3" style="font-size:15px;color:rgba(255,255,255,0.65);line-height:1.75;max-width:480px;margin:0 0 36px;">
                Menyajikan manajemen dokumen, workflow approval, audit trail, dan kontrol akses berbasis peran dalam satu platform terintegrasi.
            </p>

            <div class="fade-4" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:48px;">
                <a href="{{ route('login') }}" class="btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7l5 5m0 0l-5 5m5-5H4"/></svg>
                    Lihat Profil
                </a>
                <a href="#fitur" class="btn-ghost">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                    Baca Dokumen
                </a>
            </div>

            {{-- Quick stats --}}
            <div class="fade-5" style="display:flex;align-items:center;gap:28px;flex-wrap:wrap;padding-top:28px;border-top:1px solid rgba(255,255,255,0.1);">
                @foreach(['✓ ISO 9001:2015', '✓ Version Control', '✓ Audit Trail', '✓ Role-Based Access'] as $s)
                <span style="font-size:12px;color:rgba(255,255,255,0.5);font-weight:500;">{{ $s }}</span>
                @endforeach
            </div>
        </div>

        {{-- Right — Profile card (like SMKN 7 style) --}}
        <div class="profile-card fade-3" style="flex-shrink:0;">
            <div style="margin-bottom:18px;">
                <span style="font-size:10px;font-weight:700;letter-spacing:1.5px;color:#7dd3fc;text-transform:uppercase;background:rgba(14,165,233,0.15);padding:4px 10px;border-radius:6px;">Profil Singkat</span>
            </div>

            {{-- Logo inside card --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                <img src="{{ asset('asset/images/logo.png') }}" alt="Logo" style="width:52px;height:52px;border-radius:14px;object-fit:contain;background:rgba(255,255,255,0.05);padding:4px;">
                <div>
                    <p style="font-size:15px;font-weight:700;color:white;margin:0;line-height:1.2;">DOCUMENT CONTROL</p>
                    <p style="font-size:11px;color:#7dd3fc;margin:0;font-weight:500;">SISTEM</p>
                </div>
            </div>

            <p style="font-size:12.5px;color:rgba(255,255,255,0.55);line-height:1.7;margin:0 0 18px;">
                Platform manajemen dokumen perusahaan — terstruktur, terkontrol, dan sesuai standar ISO 9001:2015.
            </p>

            <div style="display:flex;flex-direction:column;gap:8px;">
                <div class="info-row">
                    <svg width="14" height="14" fill="none" stroke="#38bdf8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Divisi Operasional &amp; Kualitas</span>
                </div>
                <div class="info-row">
                    <svg width="14" height="14" fill="none" stroke="#38bdf8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Terakreditasi ISO 9001:2015</span>
                </div>
                <div class="info-row">
                    <svg width="14" height="14" fill="none" stroke="#38bdf8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>ardihardi57@gmail.com</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════ FEATURES ══════════════════ --}}
<section id="fitur" style="padding:96px 24px;background:#0f172a;">
    <div style="max-width:1160px;margin:0 auto;">

        <div class="reveal" style="text-align:center;margin-bottom:56px;">
            <p style="font-size:11px;font-weight:700;color:#38bdf8;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 12px;">Fitur Unggulan</p>
            <h2 class="font-serif" style="font-size:40px;font-weight:400;color:white;margin:0 0 12px;">Semua yang Anda Butuhkan</h2>
            <p style="font-size:15px;color:rgba(255,255,255,0.45);max-width:440px;margin:0 auto;">Dirancang untuk kebutuhan document control perusahaan modern</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px;">
            @php
            $features = [
                ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Upload & Version Control', 'desc' => 'Upload PDF dan kelola versi dokumen secara otomatis. Setiap revisi tercatat dengan rapi.'],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'title' => 'Approval Workflow', 'desc' => 'Alur persetujuan dokumen yang jelas. Department Head dapat approve atau reject.'],
                ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'title' => 'Role-Based Access', 'desc' => 'Admin, Department Head, Employee, dan Viewer — masing-masing dengan hak akses sesuai.'],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2', 'title' => 'Audit Trail Lengkap', 'desc' => 'Setiap aktivitas tercatat: siapa melakukan apa dan kapan. Rekam jejak untuk audit.'],
                ['icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'title' => 'Kategorisasi Dokumen', 'desc' => 'Organisasi dokumen berdasarkan kategori (SOP, WI, FRM) dan departemen.'],
                ['icon' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Download Aman', 'desc' => 'Hanya dokumen approved yang bisa didownload. Distribusi dokumen selalu terkontrol.'],
            ];
            @endphp

            @foreach($features as $i => $f)
            <div class="feature-card reveal" style="transition-delay:{{ $i * 0.07 }}s;">
                <div style="width:44px;height:44px;border-radius:12px;background:rgba(14,165,233,0.12);display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                    <svg width="20" height="20" fill="none" stroke="#38bdf8" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                    </svg>
                </div>
                <h3 style="font-size:14px;font-weight:600;color:white;margin:0 0 8px;">{{ $f['title'] }}</h3>
                <p style="font-size:13px;color:rgba(255,255,255,0.45);line-height:1.65;margin:0;">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ WORKFLOW ══════════════════ --}}
<section id="alur" style="padding:96px 24px;background:linear-gradient(180deg,#0f172a,#0c1e3d);">
    <div style="max-width:960px;margin:0 auto;">

        <div class="reveal" style="text-align:center;margin-bottom:56px;">
            <p style="font-size:11px;font-weight:700;color:#38bdf8;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 12px;">Alur Kerja</p>
            <h2 class="font-serif" style="font-size:40px;font-weight:400;color:white;margin:0;">Sederhana &amp; Terstruktur</h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:32px;">
            @php
            $steps = [
                ['num'=>'01','title'=>'Upload Dokumen','desc'=>'Employee upload file PDF ke dalam sistem'],
                ['num'=>'02','title'=>'Submit untuk Review','desc'=>'Dokumen disubmit ke Department Head untuk ditinjau'],
                ['num'=>'03','title'=>'Approval','desc'=>'Department Head approve atau reject dokumen'],
                ['num'=>'04','title'=>'Publikasi','desc'=>'Dokumen approved dapat diakses dan didownload'],
            ];
            @endphp
            @foreach($steps as $i => $s)
            <div class="reveal" style="text-align:center;transition-delay:{{ $i * 0.1 }}s;">
                <div class="step-num">{{ $s['num'] }}</div>
                <h3 style="font-size:14px;font-weight:600;color:white;margin:0 0 6px;">{{ $s['title'] }}</h3>
                <p style="font-size:12px;color:rgba(255,255,255,0.4);line-height:1.65;margin:0;">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ ROLES ══════════════════ --}}
<section id="role" style="padding:96px 24px;background:#0f172a;">
    <div style="max-width:1060px;margin:0 auto;">

        <div class="reveal" style="text-align:center;margin-bottom:56px;">
            <p style="font-size:11px;font-weight:700;color:#38bdf8;letter-spacing:2.5px;text-transform:uppercase;margin:0 0 12px;">Hak Akses</p>
            <h2 class="font-serif" style="font-size:40px;font-weight:400;color:white;margin:0;">4 Role Pengguna</h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:16px;">
            @php
            $roles = [
                ['role'=>'Admin','color'=>'#f87171','bg'=>'rgba(239,68,68,0.1)','border'=>'rgba(239,68,68,0.2)','perms'=>['Kelola semua user & role','Kelola departemen & kategori','Lihat audit log lengkap','Hapus/restore dokumen']],
                ['role'=>'Department Head','color'=>'#38bdf8','bg'=>'rgba(14,165,233,0.1)','border'=>'rgba(14,165,233,0.2)','perms'=>['Approve / Reject dokumen','Lihat semua dokumen departemen','Dashboard statistik','Beri komentar approval']],
                ['role'=>'Employee','color'=>'#4ade80','bg'=>'rgba(34,197,94,0.1)','border'=>'rgba(34,197,94,0.2)','perms'=>['Upload dokumen PDF','Submit untuk approval','Upload revisi jika ditolak','Pantau status dokumen']],
                ['role'=>'Viewer','color'=>'#94a3b8','bg'=>'rgba(148,163,184,0.08)','border'=>'rgba(148,163,184,0.15)','perms'=>['Lihat dokumen approved','Download dokumen','Read-only access','Tidak bisa upload']],
            ];
            @endphp
            @foreach($roles as $i => $r)
            <div class="role-card reveal" style="transition-delay:{{ $i * 0.1 }}s;border-color:{{ $r['border'] }};">
                <span style="font-size:11px;font-weight:700;background:{{ $r['bg'] }};color:{{ $r['color'] }};padding:5px 14px;border-radius:99px;display:inline-block;margin-bottom:18px;border:1px solid {{ $r['border'] }};">{{ $r['role'] }}</span>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:9px;">
                    @foreach($r['perms'] as $p)
                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:rgba(255,255,255,0.5);">
                        <svg width="14" height="14" fill="none" stroke="{{ $r['color'] }}" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $p }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════════════ CTA ══════════════════ --}}
<section class="cta-section" style="padding:80px 24px;position:relative;overflow:hidden;">
    <div class="dots-bg" style="position:absolute;inset:0;"></div>
    <div style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:500px;height:250px;background:rgba(14,165,233,0.12);border-radius:50%;filter:blur(80px);pointer-events:none;"></div>

    <div class="reveal" style="max-width:540px;margin:0 auto;text-align:center;position:relative;z-index:1;">
        <h2 class="font-serif" style="font-size:42px;font-weight:400;color:white;margin:0 0 14px;">Siap Mulai?</h2>
        <p style="font-size:15px;color:rgba(255,255,255,0.45);margin:0 0 32px;">Masuk ke sistem dan mulai kelola dokumen perusahaan Anda hari ini.</p>
        <a href="{{ route('login') }}" class="btn-primary" style="font-size:15px;padding:14px 36px;">
            Masuk ke Sistem
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7l5 5m0 0l-5 5m5-5H4"/></svg>
        </a>
    </div>
</section>

{{-- ══════════════════ FOOTER ══════════════════ --}}
<footer style="background:#080f1e;padding:32px 24px;text-align:center;border-top:1px solid rgba(255,255,255,0.05);">
    <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:8px;">
        <img src="{{ asset('asset/images/logo.png') }}" alt="Logo" style="width:28px;height:28px;border-radius:7px;object-fit:contain;">
        <span style="font-size:13px;font-weight:600;color:rgba(255,255,255,0.7);">Document Control <span style="color:#38bdf8;">System</span></span>
    </div>
    <p style="font-size:11px;color:rgba(255,255,255,0.2);margin:0;">© {{ date('Y') }} Document Control System. All rights reserved.</p>
</footer>

<script>
// Scroll reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('show'); });
}, { threshold: 0.08 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const t = document.querySelector(a.getAttribute('href'));
        if (t) t.scrollIntoView({ behavior: 'smooth' });
    });
});
</script>
</body>
</html>