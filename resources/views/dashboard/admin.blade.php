@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome bar --}}
    <div class="bg-gradient-to-r from-slate-900 to-blue-900 rounded-2xl p-6 flex items-center justify-between">
        <div>
            <p class="text-blue-300 text-sm font-medium mb-1">Selamat datang kembali 👋</p>
            <h2 class="text-xl font-bold text-white">{{ auth()->user()->name }}</h2>
            <p class="text-slate-400 text-xs mt-1">{{ now()->translatedFormat('l, d F Y') }} · Administrator</p>
        </div>
        <div class="hidden md:flex items-center gap-3">
            <a href="{{ route('documents.index') }}"
               class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors border border-white/10">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Lihat Dokumen
            </a>
            <a href="{{ route('users.index') }}"
               class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah User
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
        $stats = [
            [
                'label'   => 'Total Users',
                'value'   => $totalUsers,
                'sub'     => 'Pengguna terdaftar',
                'color'   => 'blue',
                'bg'      => 'from-blue-500 to-blue-700',
                'icon'    => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
            ],
            [
                'label'   => 'Total Dokumen',
                'value'   => $totalDocuments,
                'sub'     => 'Semua departemen',
                'color'   => 'indigo',
                'bg'      => 'from-indigo-500 to-indigo-700',
                'icon'    => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
            ],
            [
                'label'   => 'Pending Approval',
                'value'   => $pendingApprovals,
                'sub'     => 'Menunggu persetujuan',
                'color'   => 'amber',
                'bg'      => 'from-amber-500 to-orange-500',
                'icon'    => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label'   => 'Approved',
                'value'   => $approvedDocs,
                'sub'     => 'Dokumen aktif',
                'color'   => 'emerald',
                'bg'      => 'from-emerald-500 to-green-600',
                'icon'    => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
        ];
        @endphp

        @foreach($stats as $stat)
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $stat['bg'] }} flex items-center justify-center shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-800 leading-none mb-1">{{ $stat['value'] }}</p>
            <p class="text-sm font-medium text-gray-600">{{ $stat['label'] }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $stat['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Status Pills --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @php
        $statuses = [
            ['label' => 'Approved',  'value' => $approvedDocs,     'dot' => 'bg-emerald-400', 'text' => 'text-emerald-700', 'bg' => 'bg-emerald-50 border-emerald-100'],
            ['label' => 'Pending',   'value' => $pendingApprovals, 'dot' => 'bg-amber-400',   'text' => 'text-amber-700',   'bg' => 'bg-amber-50 border-amber-100'],
            ['label' => 'Rejected',  'value' => $rejectedDocs,     'dot' => 'bg-red-400',     'text' => 'text-red-700',     'bg' => 'bg-red-50 border-red-100'],
            ['label' => 'Expired',   'value' => $expiredDocs,      'dot' => 'bg-orange-400',  'text' => 'text-orange-700',  'bg' => 'bg-orange-50 border-orange-100'],
        ];
        @endphp
        @foreach($statuses as $s)
        <div class="flex items-center justify-between {{ $s['bg'] }} border rounded-xl px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full {{ $s['dot'] }}"></div>
                <span class="text-sm font-medium {{ $s['text'] }}">{{ $s['label'] }}</span>
            </div>
            <span class="text-lg font-bold {{ $s['text'] }}">{{ $s['value'] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Two column --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Dokumen per Departemen --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-800">Dokumen per Departemen</h3>
                <a href="{{ route('departments.index') }}" class="text-xs text-blue-600 hover:underline">Kelola →</a>
            </div>
            <div class="space-y-3">
                @forelse($docsByDepartment as $dept)
                @php $max = $docsByDepartment->max('documents_count') ?: 1; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-mono font-semibold bg-slate-100 text-slate-600 px-2 py-0.5 rounded">{{ $dept->code }}</span>
                            <span class="text-sm text-gray-700">{{ $dept->name }}</span>
                        </div>
                        <span class="text-sm font-bold text-gray-800">{{ $dept->documents_count }}</span>
                    </div>
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full transition-all"
                             style="width: {{ $dept->documents_count / $max * 100 }}%"></div>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-6">Belum ada data departemen</p>
                @endforelse
            </div>
        </div>

        {{-- Aktivitas Terbaru --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-gray-800">Aktivitas Terbaru</h3>
                <a href="{{ route('audit-logs.index') }}" class="text-xs text-blue-600 hover:underline">Lihat semua →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentLogs as $log)
                @php
                $actionColors = [
                    'upload'   => 'bg-blue-100 text-blue-600',
                    'approve'  => 'bg-emerald-100 text-emerald-600',
                    'reject'   => 'bg-red-100 text-red-600',
                    'submit'   => 'bg-amber-100 text-amber-600',
                    'login'    => 'bg-slate-100 text-slate-500',
                    'create'   => 'bg-indigo-100 text-indigo-600',
                    'delete'   => 'bg-red-100 text-red-600',
                    'download' => 'bg-cyan-100 text-cyan-600',
                ];
                $ac = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-500';
                @endphp
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg {{ $ac }} flex items-center justify-center flex-shrink-0 text-xs font-bold">
                        {{ strtoupper(substr($log->action, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 truncate">{{ $log->description }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->diffForHumans() }} · {{ $log->user->name ?? 'System' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-6">Belum ada aktivitas</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick links --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        @php
        $links = [
            ['label' => 'Kelola Users',      'route' => 'users.index',       'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'color' => 'blue'],
            ['label' => 'Departemen',         'route' => 'departments.index', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'color' => 'indigo'],
            ['label' => 'Kategori Dokumen',   'route' => 'categories.index',  'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'color' => 'purple'],
            ['label' => 'Audit Log',          'route' => 'audit-logs.index',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'slate'],
        ];
        @endphp
        @foreach($links as $link)
        <a href="{{ route($link['route']) }}"
           class="bg-white border border-gray-100 rounded-2xl p-4 flex items-center gap-3 shadow-sm hover:shadow-md hover:border-{{ $link['color'] }}-200 transition-all group">
            <div class="w-9 h-9 bg-{{ $link['color'] }}-50 rounded-xl flex items-center justify-center group-hover:bg-{{ $link['color'] }}-100 transition-colors">
                <svg class="w-4.5 h-4.5 w-5 h-5 text-{{ $link['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                </svg>
            </div>
            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">{{ $link['label'] }}</span>
        </a>
        @endforeach
    </div>

</div>
@endsection
