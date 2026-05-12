<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Control System — NexaStudio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }

        :root {
            --blue-primary: #0ea5e9;
            --blue-dark: #0284c7;
            --blue-deeper: #0369a1;
            --navy: #0f172a;
        }

        /* Gradient mesh background */
        .hero-bg {
            background: radial-gradient(ellipse 80% 60% at 50% -10%, rgba(14,165,233,0.15) 0%, transparent 70%),
                        radial-gradient(ellipse 50% 40% at 80% 50%, rgba(14,165,233,0.08) 0%, transparent 60%),
                        radial-gradient(ellipse 40% 60% at 10% 60%, rgba(56,189,248,0.06) 0%, transparent 60%),
                        #f8fafc;
        }

        /* Animated gradient border */
        .badge-pill {
            background: linear-gradient(white, white) padding-box,
                        linear-gradient(135deg, #0ea5e9, #38bdf8, #0ea5e9) border-box;
            border: 1.5px solid transparent;
        }

        /* Floating animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        .float-1 { animation: float 6s ease-in-out infinite; }
        .float-2 { animation: float 8s ease-in-out infinite 1s; }
        .float-3 { animation: float 7s ease-in-out infinite 2s; }

        /* Fade in up */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeInUp 0.7s ease forwards; }
        .delay-1 { animation-delay: 0.1s; opacity: 0; }
        .delay-2 { animation-delay: 0.25s; opacity: 0; }
        .delay-3 { animation-delay: 0.4s; opacity: 0; }
        .delay-4 { animation-delay: 0.55s; opacity: 0; }
        .delay-5 { animation-delay: 0.7s; opacity: 0; }

        /* Card hover */
        .feature-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(14,165,233,0.12);
        }

        /* Glow button */
        .btn-primary {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            box-shadow: 0 4px 20px rgba(14,165,233,0.35);
            transition: all 0.25s ease;
        }
        .btn-primary:hover {
            box-shadow: 0 8px 30px rgba(14,165,233,0.5);
            transform: translateY(-2px);
        }

        /* Nav blur */
        .nav-blur {
            backdrop-filter: blur(16px);
            background: rgba(248,250,252,0.85);
        }

        /* Decorative dots grid */
        .dots-grid {
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 28px 28px;
        }

        /* Step connector line */
        .step-line::after {
            content: '';
            position: absolute;
            top: 24px;
            left: calc(50% + 24px);
            width: calc(100% - 48px);
            height: 1px;
            background: linear-gradient(to right, #0ea5e9, #bae6fd);
        }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body class="antialiased text-slate-800">

    {{-- ─── NAVBAR ─────────────────────────────────────── --}}
    <nav class="nav-blur fixed top-0 left-0 right-0 z-50 border-b border-slate-200/60">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            {{-- Logo --}}
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center shadow-md shadow-sky-200">
                    <svg class="w-4.5 h-4.5 text-white w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="font-semibold text-slate-800 tracking-tight">Document Control <span class="text-sky-500">DCS</span></span>
            </div>

            {{-- Nav links --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#fitur" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">Fitur</a>
                <a href="#alur" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">Alur Kerja</a>
                <a href="#role" class="text-sm text-slate-500 hover:text-slate-800 transition-colors">Role</a>
            </div>

            {{-- CTA --}}
            <div class="flex items-center gap-3">
                @auth
                <a href="{{ route('dashboard') }}"
                   class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Dashboard</a>
                @else
                <a href="{{ route('login') }}"
                   class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">Masuk</a>
                <a href="{{ route('login') }}"
                   class="btn-primary text-white text-sm font-medium px-5 py-2.5 rounded-full">
                    Mulai Sekarang
                </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ─── HERO ───────────────────────────────────────── --}}
    <section class="hero-bg pt-32 pb-24 px-6 relative overflow-hidden">

        {{-- Decorative dots --}}
        <div class="dots-grid absolute inset-0 opacity-40"></div>

        {{-- Floating decorative cards --}}
        <div class="float-1 absolute top-28 right-12 hidden lg:block">
            <div class="bg-white rounded-2xl shadow-xl shadow-slate-200 border border-slate-100 p-4 w-52">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-2 h-2 rounded-full bg-green-400"></div>
                    <span class="text-xs text-slate-500 font-medium">SOP-HR-001 Approved</span>
                </div>
                <p class="text-xs text-slate-400">Disetujui oleh Dept. Head</p>
                <div class="mt-2 h-1.5 bg-green-100 rounded-full"><div class="h-full w-full bg-green-400 rounded-full"></div></div>
            </div>
        </div>

        <div class="float-2 absolute top-48 left-10 hidden lg:block">
            <div class="bg-white rounded-2xl shadow-xl shadow-slate-200 border border-slate-100 p-4 w-44">
                <div class="text-xs text-slate-400 mb-1">Total Dokumen</div>
                <div class="text-2xl font-bold text-slate-800">248</div>
                <div class="text-xs text-sky-500 font-medium mt-0.5">↑ 12 bulan ini</div>
            </div>
        </div>

        <div class="float-3 absolute bottom-16 right-24 hidden lg:block">
            <div class="bg-white rounded-2xl shadow-xl shadow-slate-200 border border-slate-100 p-4 w-48">
                <div class="flex items-center gap-1.5 mb-1">
                    <div class="w-2 h-2 rounded-full bg-yellow-400"></div>
                    <span class="text-xs text-slate-500 font-medium">Pending Approval</span>
                </div>
                <div class="text-xl font-bold text-slate-800">3</div>
                <p class="text-xs text-slate-400 mt-0.5">menunggu review</p>
            </div>
        </div>

        {{-- Hero content --}}
        <div class="max-w-3xl mx-auto text-center relative z-10">

            <div class="badge-pill inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-8 animate-fade-up delay-1">
                <div class="w-1.5 h-1.5 rounded-full bg-sky-400 animate-pulse"></div>
                <span class="text-xs font-medium text-sky-600 tracking-widest uppercase">Sistem Kontrol Dokumen ISO 9001:2015</span>
            </div>

            <h1 class="font-display text-5xl md:text-6xl font-800 text-slate-900 leading-tight mb-6 animate-fade-up delay-2">
                Kelola Dokumen<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-500 to-blue-600">Lebih Terstruktur</span>
            </h1>

            <p class="text-lg text-slate-500 leading-relaxed mb-10 max-w-xl mx-auto animate-fade-up delay-3">
                Platform manajemen dokumen perusahaan dengan workflow approval, version control, dan audit trail lengkap sesuai standar ISO.
            </p>

            <div class="flex items-center justify-center gap-4 animate-fade-up delay-4">
                <a href="{{ route('login') }}"
                   class="btn-primary text-white font-medium px-8 py-3.5 rounded-full text-sm">
                    Mulai Sekarang →
                </a>
                <a href="#fitur"
                   class="flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors">
                    <div class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center bg-white shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                    Lihat Fitur
                </a>
            </div>

            {{-- Stats --}}
            <div class="flex items-center justify-center gap-8 mt-14 pt-8 border-t border-slate-200/60 animate-fade-up delay-5">
                @foreach([['✓ ISO 9001:2015', ''], ['✓ Version Control', ''], ['✓ Audit Trail', ''], ['✓ Role-Based Access', '']] as $stat)
                <span class="text-sm text-slate-500 font-medium">{{ $stat[0] }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── FEATURES ───────────────────────────────────── --}}
    <section id="fitur" class="py-24 px-6 bg-white">
        <div class="max-w-6xl mx-auto">

            <div class="text-center mb-16 reveal">
                <p class="text-xs font-semibold text-sky-500 tracking-widest uppercase mb-3">Fitur Unggulan</p>
                <h2 class="font-display text-4xl font-bold text-slate-900">Semua yang Anda Butuhkan</h2>
                <p class="text-slate-500 mt-3 max-w-lg mx-auto">Dirancang khusus untuk kebutuhan document control perusahaan modern</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                $features = [
                    ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Upload & Version Control', 'desc' => 'Upload PDF, kelola versi dokumen secara otomatis. Setiap revisi tercatat dengan rapi.', 'color' => 'sky'],
                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'title' => 'Approval Workflow', 'desc' => 'Alur persetujuan dokumen multi-level. Department Head approve/reject dengan komentar.', 'color' => 'blue'],
                    ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'title' => 'Role-Based Access', 'desc' => 'Admin, Department Head, Employee, dan Viewer — masing-masing dengan hak akses yang tepat.', 'color' => 'indigo'],
                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2', 'title' => 'Audit Log Lengkap', 'desc' => 'Setiap aktivitas tercatat: siapa, kapan, apa. Rekam jejak lengkap untuk keperluan audit.', 'color' => 'cyan'],
                    ['icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'title' => 'Kategorisasi Dokumen', 'desc' => 'Organisasi dokumen berdasarkan kategori (SOP, WI, FRM) dan departemen secara otomatis.', 'color' => 'sky'],
                    ['icon' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Download Aman', 'desc' => 'Hanya dokumen approved yang bisa didownload. Kontrol distribusi dokumen perusahaan.', 'color' => 'blue'],
                ];
                @endphp

                @foreach($features as $i => $f)
                <div class="feature-card reveal bg-white border border-slate-100 rounded-2xl p-6 shadow-sm" style="transition-delay: {{ $i * 0.1 }}s">
                    <div class="w-11 h-11 bg-{{ $f['color'] }}-50 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-{{ $f['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $f['icon'] }}"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-slate-800 mb-2">{{ $f['title'] }}</h3>
                    <p class="text-sm text-slate-500 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── WORKFLOW ────────────────────────────────────── --}}
    <section id="alur" class="py-24 px-6 bg-slate-50">
        <div class="max-w-5xl mx-auto">

            <div class="text-center mb-16 reveal">
                <p class="text-xs font-semibold text-sky-500 tracking-widest uppercase mb-3">Alur Kerja</p>
                <h2 class="font-display text-4xl font-bold text-slate-900">Sederhana & Terstruktur</h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 relative">
                @php
                $steps = [
                    ['num' => '01', 'title' => 'Upload', 'desc' => 'Employee upload dokumen PDF ke sistem', 'color' => 'sky'],
                    ['num' => '02', 'title' => 'Review', 'desc' => 'Department Head mereview dokumen yang disubmit', 'color' => 'blue'],
                    ['num' => '03', 'title' => 'Approve', 'desc' => 'Dokumen disetujui dan dipublikasikan', 'color' => 'indigo'],
                    ['num' => '04', 'title' => 'Akses', 'desc' => 'Seluruh tim bisa download dokumen approved', 'color' => 'cyan'],
                ];
                @endphp
                @foreach($steps as $i => $step)
                <div class="reveal text-center" style="transition-delay: {{ $i * 0.15 }}s">
                    <div class="w-12 h-12 bg-gradient-to-br from-sky-400 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-sky-200 text-white font-bold text-sm">
                        {{ $step['num'] }}
                    </div>
                    <h3 class="font-semibold text-slate-800 mb-1">{{ $step['title'] }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ─── ROLES ───────────────────────────────────────── --}}
    <section id="role" class="py-24 px-6 bg-white">
        <div class="max-w-5xl mx-auto">

            <div class="text-center mb-16 reveal">
                <p class="text-xs font-semibold text-sky-500 tracking-widest uppercase mb-3">Hak Akses</p>
                <h2 class="font-display text-4xl font-bold text-slate-900">4 Role Pengguna</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @php
                $roles = [
                    ['role' => 'Admin', 'badge' => 'bg-red-100 text-red-600', 'desc' => 'Kelola seluruh sistem, user, departemen, kategori, dan audit log.', 'perms' => ['Kelola semua user', 'Kelola departemen & kategori', 'Lihat audit log', 'Restore / hapus permanen dokumen']],
                    ['role' => 'Department Head', 'badge' => 'bg-blue-100 text-blue-600', 'desc' => 'Mengelola dan menyetujui dokumen dalam departemennya.', 'perms' => ['Approve / Reject dokumen', 'Lihat semua dokumen departemen', 'Dashboard statistik departemen', 'Beri komentar pada approval']],
                    ['role' => 'Employee', 'badge' => 'bg-green-100 text-green-600', 'desc' => 'Upload dan kelola dokumen yang dibuat sendiri.', 'perms' => ['Upload dokumen PDF', 'Submit untuk approval', 'Upload revisi jika ditolak', 'Lihat status dokumen miliknya']],
                    ['role' => 'Viewer', 'badge' => 'bg-slate-100 text-slate-600', 'desc' => 'Hanya bisa melihat dan download dokumen yang sudah approved.', 'perms' => ['Lihat dokumen approved', 'Download dokumen', 'Tidak bisa upload', 'Tidak bisa approve']],
                ];
                @endphp
                @foreach($roles as $i => $r)
                <div class="reveal feature-card border border-slate-100 rounded-2xl p-6 shadow-sm" style="transition-delay: {{ $i * 0.1 }}s">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $r['badge'] }}">{{ $r['role'] }}</span>
                    </div>
                    <p class="text-sm text-slate-500 mb-4">{{ $r['desc'] }}</p>
                    <ul class="space-y-1.5">
                        @foreach($r['perms'] as $p)
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-sky-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
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

    {{-- ─── CTA ─────────────────────────────────────────── --}}
    <section class="py-20 px-6 bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 relative overflow-hidden">
        <div class="absolute inset-0 dots-grid opacity-10"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-40 bg-sky-500/20 rounded-full blur-3xl"></div>

        <div class="max-w-2xl mx-auto text-center relative z-10 reveal">
            <h2 class="font-display text-4xl font-bold text-white mb-4">Siap Mulai?</h2>
            <p class="text-slate-400 mb-8">Masuk ke sistem dan mulai kelola dokumen perusahaan Anda hari ini.</p>
            <a href="{{ route('login') }}"
               class="btn-primary inline-block text-white font-semibold px-10 py-4 rounded-full text-sm">
                Masuk ke Sistem →
            </a>
        </div>
    </section>

    {{-- ─── FOOTER ─────────────────────────────────────── --}}
    <footer class="bg-slate-900 border-t border-slate-800 py-8 px-6 text-center">
        <div class="flex items-center justify-center gap-2 mb-2">
            <div class="w-6 h-6 rounded-md bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-white">NexaStudio <span class="text-sky-400">DCS</span></span>
        </div>
        <p class="text-xs text-slate-500">© {{ date('Y') }} NexaStudio. Document Control System.</p>
    </footer>

    <script>
        // Scroll reveal
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                e.preventDefault();
                document.querySelector(a.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>
