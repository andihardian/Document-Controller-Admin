<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Control System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,400&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; margin: 0; }
        .font-display { font-family: 'Playfair Display', serif; }

        .hero-bg {
            background:
                radial-gradient(ellipse 80% 50% at 50% -5%, rgba(14,165,233,0.18) 0%, transparent 65%),
                radial-gradient(ellipse 40% 40% at 85% 40%, rgba(14,165,233,0.08) 0%, transparent 60%),
                #f8fafc;
        }

        .dots-grid {
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .badge-pill {
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #0ea5e9, #38bdf8) border-box;
            border: 1.5px solid transparent;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            box-shadow: 0 4px 20px rgba(14,165,233,0.4);
            transition: all 0.25s ease;
            color: white;
            font-weight: 600;
            padding: 14px 32px;
            border-radius: 9999px;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary:hover {
            box-shadow: 0 8px 30px rgba(14,165,233,0.55);
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 1.5px solid #e2e8f0;
            background: white;
            color: #475569;
            font-weight: 500;
            padding: 12px 24px;
            border-radius: 9999px;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }
        .btn-outline:hover { background: #f8fafc; border-color: #0ea5e9; color: #0ea5e9; }

        .nav-blur {
            backdrop-filter: blur(16px);
            background: rgba(248,250,252,0.88);
            border-bottom: 1px solid rgba(226,232,240,0.8);
        }

        .feature-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(14,165,233,0.12);
        }

        .icon-box {
            width: 48px; height: 48px;
            background: #f0f9ff;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }

        .step-num {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 13px;
            margin: 0 auto 16px;
            box-shadow: 0 6px 20px rgba(14,165,233,0.35);
        }

        .role-card {
            background: white;
            border: 1px solid #f1f5f9;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .role-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        }

        .dark-section {
            background: linear-gradient(145deg, #0f172a 0%, #0c2244 50%, #0f172a 100%);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-10px); }
        }
        .float-1 { animation: float 6s ease-in-out infinite; }
        .float-2 { animation: float 8s ease-in-out infinite 1.5s; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-1 { animation: fadeUp 0.6s ease 0.1s both; }
        .fade-2 { animation: fadeUp 0.6s ease 0.25s both; }
        .fade-3 { animation: fadeUp 0.6s ease 0.4s both; }
        .fade-4 { animation: fadeUp 0.6s ease 0.55s both; }

        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.show { opacity: 1; transform: translateY(0); }

        /* Override any global Laravel styles */
        a { text-decoration: none; }
    </style>
</head>
<body style="background:#f8fafc;">

{{-- ═══ NAVBAR ═══════════════════════════════════════════ --}}
<nav class="nav-blur" style="position:fixed;top:0;left:0;right:0;z-index:100;">
    <div style="max-width:1100px;margin:0 auto;padding:0 24px;height:64px;display:flex;align-items:center;justify-content:space-between;">

        {{-- Logo --}}
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#38bdf8,#0284c7);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(14,165,233,0.3);">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span style="font-weight:600;font-size:15px;color:#0f172a;">Document Control <span style="color:#0ea5e9;">System</span></span>
        </div>

        {{-- Links --}}
        <div style="display:flex;align-items:center;gap:32px;">
            <a href="#fitur" style="font-size:13px;color:#64748b;font-weight:500;transition:color 0.2s;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">Fitur</a>
            <a href="#alur" style="font-size:13px;color:#64748b;font-weight:500;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">Alur Kerja</a>
            <a href="#role" style="font-size:13px;color:#64748b;font-weight:500;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#64748b'">Role</a>
            @auth
            <a href="{{ route('dashboard') }}" style="font-size:13px;color:#64748b;font-weight:500;">Dashboard</a>
            @else
            <a href="{{ route('login') }}" style="font-size:13px;color:#64748b;font-weight:500;">Masuk</a>
            <a href="{{ route('login') }}" class="btn-primary" style="padding:9px 22px;font-size:13px;">Mulai Sekarang</a>
            @endauth
        </div>
    </div>
</nav>

{{-- ═══ HERO ══════════════════════════════════════════════ --}}
<section class="hero-bg" style="padding:120px 24px 80px;position:relative;overflow:hidden;min-height:90vh;display:flex;align-items:center;">
    <div class="dots-grid" style="position:absolute;inset:0;opacity:0.45;"></div>

    {{-- Floating cards --}}
    <div class="float-1" style="position:absolute;top:120px;right:80px;display:none;" id="fc1">
        <div style="background:white;border:1px solid #f1f5f9;border-radius:18px;padding:16px;width:210px;box-shadow:0 12px 40px rgba(0,0,0,0.08);">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;">
                <div style="width:8px;height:8px;background:#22c55e;border-radius:50%;"></div>
                <span style="font-size:11px;color:#64748b;font-weight:500;">SOP-HR-001 Approved</span>
            </div>
            <p style="font-size:11px;color:#94a3b8;margin:0;">Disetujui Dept. Head · 2 menit lalu</p>
            <div style="margin-top:8px;height:4px;background:#dcfce7;border-radius:99px;"><div style="width:100%;height:100%;background:#22c55e;border-radius:99px;"></div></div>
        </div>
    </div>

    <div class="float-2" style="position:absolute;bottom:100px;right:60px;display:none;" id="fc2">
        <div style="background:white;border:1px solid #f1f5f9;border-radius:18px;padding:16px;width:180px;box-shadow:0 12px 40px rgba(0,0,0,0.08);">
            <p style="font-size:11px;color:#94a3b8;margin:0 0 4px;">Total Dokumen</p>
            <p style="font-size:28px;font-weight:700;color:#0f172a;margin:0;">248</p>
            <p style="font-size:11px;color:#0ea5e9;font-weight:500;margin:4px 0 0;">↑ 12 dokumen baru</p>
        </div>
    </div>

    {{-- Content --}}
    <div style="max-width:700px;margin:0 auto;text-align:center;position:relative;z-index:2;">

        <div class="badge-pill fade-1" style="margin-bottom:28px;">
            <div style="width:6px;height:6px;background:#0ea5e9;border-radius:50%;animation:float 2s ease-in-out infinite;"></div>
            <span style="font-size:11px;font-weight:600;color:#0369a1;letter-spacing:1.5px;text-transform:uppercase;">Sistem Kontrol Dokumen ISO 9001:2015</span>
        </div>

        <h1 class="font-display fade-2" style="font-size:clamp(40px,6vw,64px);font-weight:800;color:#0f172a;line-height:1.1;margin:0 0 24px;">
            Kelola Dokumen<br>
            <span style="background:linear-gradient(135deg,#0ea5e9,#0369a1);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Lebih Terstruktur</span>
        </h1>

        <p class="fade-3" style="font-size:17px;color:#64748b;line-height:1.7;max-width:520px;margin:0 auto 40px;">
            Platform manajemen dokumen perusahaan dengan workflow approval, version control, dan audit trail lengkap sesuai standar ISO.
        </p>

        <div class="fade-4" style="display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;">
            <a href="{{ route('login') }}" class="btn-primary">Mulai Sekarang →</a>
            <a href="#fitur" class="btn-outline">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                Lihat Fitur
            </a>
        </div>

        {{-- Stats bar --}}
        <div style="display:flex;align-items:center;justify-content:center;gap:32px;margin-top:56px;padding-top:32px;border-top:1px solid rgba(226,232,240,0.8);flex-wrap:wrap;">
            @foreach(['✓ ISO 9001:2015', '✓ Version Control', '✓ Audit Trail', '✓ Role-Based Access'] as $s)
            <span style="font-size:13px;color:#64748b;font-weight:500;">{{ $s }}</span>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ FEATURES ══════════════════════════════════════════ --}}
<section id="fitur" style="padding:96px 24px;background:white;">
    <div style="max-width:1100px;margin:0 auto;">

        <div class="reveal" style="text-align:center;margin-bottom:56px;">
            <p style="font-size:11px;font-weight:700;color:#0ea5e9;letter-spacing:2px;text-transform:uppercase;margin:0 0 12px;">Fitur Unggulan</p>
            <h2 class="font-display" style="font-size:36px;font-weight:700;color:#0f172a;margin:0 0 12px;">Semua yang Anda Butuhkan</h2>
            <p style="font-size:15px;color:#64748b;max-width:480px;margin:0 auto;">Dirancang khusus untuk kebutuhan document control perusahaan modern</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;">
            @php
            $features = [
                ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Upload & Version Control', 'desc' => 'Upload PDF dan kelola versi dokumen secara otomatis. Setiap revisi tercatat dengan rapi dan terstruktur.'],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'title' => 'Approval Workflow', 'desc' => 'Alur persetujuan dokumen yang jelas. Department Head dapat approve atau reject disertai komentar.'],
                ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'title' => 'Role-Based Access', 'desc' => 'Admin, Department Head, Employee, dan Viewer — masing-masing dengan hak akses yang sesuai.'],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2', 'title' => 'Audit Trail Lengkap', 'desc' => 'Setiap aktivitas tercatat: siapa melakukan apa dan kapan. Rekam jejak lengkap untuk keperluan audit.'],
                ['icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'title' => 'Kategorisasi Dokumen', 'desc' => 'Organisasi dokumen berdasarkan kategori (SOP, WI, FRM) dan departemen secara otomatis.'],
                ['icon' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Download Aman', 'desc' => 'Hanya dokumen approved yang bisa didownload. Pastikan distribusi dokumen selalu terkontrol.'],
            ];
            @endphp

            @foreach($features as $i => $f)
            <div class="feature-card reveal" style="transition-delay:{{ $i * 0.08 }}s;">
                <div class="icon-box">
                    <svg width="22" height="22" fill="none" stroke="#0ea5e9" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                    </svg>
                </div>
                <h3 style="font-size:15px;font-weight:600;color:#0f172a;margin:0 0 8px;">{{ $f['title'] }}</h3>
                <p style="font-size:13px;color:#64748b;line-height:1.65;margin:0;">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ WORKFLOW ═══════════════════════════════════════════ --}}
<section id="alur" style="padding:96px 24px;background:#f8fafc;">
    <div style="max-width:900px;margin:0 auto;">

        <div class="reveal" style="text-align:center;margin-bottom:56px;">
            <p style="font-size:11px;font-weight:700;color:#0ea5e9;letter-spacing:2px;text-transform:uppercase;margin:0 0 12px;">Alur Kerja</p>
            <h2 class="font-display" style="font-size:36px;font-weight:700;color:#0f172a;margin:0;">Sederhana & Terstruktur</h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:32px;">
            @php
            $steps = [
                ['num'=>'01','title'=>'Upload Dokumen','desc'=>'Employee upload file PDF ke dalam sistem'],
                ['num'=>'02','title'=>'Submit untuk Review','desc'=>'Dokumen disubmit ke Department Head untuk ditinjau'],
                ['num'=>'03','title'=>'Approval','desc'=>'Department Head approve atau reject dokumen'],
                ['num'=>'04','title'=>'Publikasi','desc'=>'Dokumen approved dapat diakses dan didownload semua user'],
            ];
            @endphp
            @foreach($steps as $i => $s)
            <div class="reveal" style="text-align:center;transition-delay:{{ $i * 0.1 }}s;">
                <div class="step-num">{{ $s['num'] }}</div>
                <h3 style="font-size:14px;font-weight:600;color:#0f172a;margin:0 0 6px;">{{ $s['title'] }}</h3>
                <p style="font-size:12px;color:#64748b;line-height:1.6;margin:0;">{{ $s['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══ ROLES ══════════════════════════════════════════════ --}}
<section id="role" style="padding:96px 24px;background:white;">
    <div style="max-width:1000px;margin:0 auto;">

        <div class="reveal" style="text-align:center;margin-bottom:56px;">
            <p style="font-size:11px;font-weight:700;color:#0ea5e9;letter-spacing:2px;text-transform:uppercase;margin:0 0 12px;">Hak Akses</p>
            <h2 class="font-display" style="font-size:36px;font-weight:700;color:#0f172a;margin:0;">4 Role Pengguna</h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">
            @php
            $roles = [
                ['role'=>'Admin','color'=>'#ef4444','bg'=>'#fef2f2','perms'=>['Kelola semua user & role','Kelola departemen & kategori','Lihat audit log lengkap','Hapus/restore dokumen']],
                ['role'=>'Department Head','color'=>'#0ea5e9','bg'=>'#f0f9ff','perms'=>['Approve / Reject dokumen','Lihat semua dokumen departemen','Dashboard statistik','Beri komentar approval']],
                ['role'=>'Employee','color'=>'#22c55e','bg'=>'#f0fdf4','perms'=>['Upload dokumen PDF','Submit untuk approval','Upload revisi jika ditolak','Pantau status dokumen']],
                ['role'=>'Viewer','color'=>'#64748b','bg'=>'#f8fafc','perms'=>['Lihat dokumen approved','Download dokumen','Read-only access','Tidak bisa upload']],
            ];
            @endphp
            @foreach($roles as $i => $r)
            <div class="role-card reveal" style="transition-delay:{{ $i * 0.1 }}s;">
                <span style="font-size:11px;font-weight:700;background:{{ $r['bg'] }};color:{{ $r['color'] }};padding:4px 12px;border-radius:99px;display:inline-block;margin-bottom:16px;">{{ $r['role'] }}</span>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                    @foreach($r['perms'] as $p)
                    <li style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:#475569;">
                        <svg width="15" height="15" fill="none" stroke="#0ea5e9" stroke-width="2.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
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

{{-- ═══ CTA ════════════════════════════════════════════════ --}}
<section class="dark-section" style="padding:80px 24px;position:relative;overflow:hidden;">
    <div class="dots-grid" style="position:absolute;inset:0;opacity:0.08;"></div>
    <div style="position:absolute;top:0;left:50%;transform:translateX(-50%);width:400px;height:200px;background:rgba(14,165,233,0.15);border-radius:50%;filter:blur(60px);pointer-events:none;"></div>

    <div class="reveal" style="max-width:560px;margin:0 auto;text-align:center;position:relative;z-index:1;">
        <h2 class="font-display" style="font-size:36px;font-weight:700;color:white;margin:0 0 12px;">Siap Mulai?</h2>
        <p style="font-size:15px;color:#94a3b8;margin:0 0 32px;">Masuk ke sistem dan mulai kelola dokumen perusahaan Anda hari ini.</p>
        <a href="{{ route('login') }}" class="btn-primary">Masuk ke Sistem →</a>
    </div>
</section>

{{-- ═══ FOOTER ════════════════════════════════════════════ --}}
<footer style="background:#0f172a;padding:32px 24px;text-align:center;">
    <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:8px;">
        <div style="width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#38bdf8,#0284c7);display:flex;align-items:center;justify-content:center;">
            <svg width="14" height="14" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <span style="font-size:14px;font-weight:600;color:white;">Document Control <span style="color:#38bdf8;">System</span></span>
    </div>
    <p style="font-size:12px;color:#475569;margin:0;">© {{ date('Y') }} Document Control System. All rights reserved.</p>
</footer>

<script>
// Show floating cards on large screens
if (window.innerWidth >= 1024) {
    document.getElementById('fc1').style.display = 'block';
    document.getElementById('fc2').style.display = 'block';
}

// Scroll reveal
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) e.target.classList.add('show');
    });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        e.preventDefault();
        const target = document.querySelector(a.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth' });
    });
});
</script>
</body>
</html>