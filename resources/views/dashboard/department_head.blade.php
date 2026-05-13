@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome --}}
    <div class="bg-gradient-to-r from-slate-900 to-blue-900 rounded-2xl p-6 flex items-center justify-between">
        <div>
            <p class="text-blue-300 text-sm font-medium mb-1">Selamat datang kembali 👋</p>
            <h2 class="text-xl font-bold text-white">{{ auth()->user()->name }}</h2>
            <p class="text-slate-400 text-xs mt-1">{{ now()->translatedFormat('l, d F Y') }} · Department Head · {{ auth()->user()->department->name ?? '-' }}</p>
        </div>
        <a href="{{ route('approvals.index') }}"
           class="hidden md:inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
            Lihat Approval
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
        $stats = [
            ['label' => 'Total',    'value' => $totalDocs,    'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z', 'bg' => 'from-slate-600 to-slate-800', 'sub' => 'Semua dokumen'],
            ['label' => 'Draft',    'value' => $draftDocs,    'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'bg' => 'from-gray-400 to-gray-600', 'sub' => 'Belum disubmit'],
            ['label' => 'Pending',  'value' => $pendingDocs,  'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'from-amber-500 to-orange-500', 'sub' => 'Menunggu review'],
            ['label' => 'Approved', 'value' => $approvedDocs, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'from-emerald-500 to-green-600', 'sub' => 'Telah disetujui'],
            ['label' => 'Rejected', 'value' => $rejectedDocs, 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'from-red-500 to-rose-600', 'sub' => 'Ditolak'],
        ];
        @endphp

        @foreach($stats as $stat)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $stat['bg'] }} flex items-center justify-center mb-3 shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-gray-800 leading-none">{{ $stat['value'] }}</p>
            <p class="text-sm font-medium text-gray-600 mt-0.5">{{ $stat['label'] }}</p>
            <p class="text-xs text-gray-400">{{ $stat['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Pending Approval List --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">Dokumen Menunggu Persetujuan</h3>
                <p class="text-xs text-gray-400 mt-0.5">Perlu ditinjau segera</p>
            </div>
            <a href="{{ route('approvals.index') }}"
               class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                Lihat semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @forelse($pendingList as $doc)
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50 hover:bg-gray-50 transition-colors last:border-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-4.5 h-4.5 w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $doc->title }}</p>
                    <p class="text-xs text-gray-400">
                        <span class="font-mono">{{ $doc->document_number }}</span>
                        · {{ $doc->category->name ?? '-' }}
                        · Oleh <span class="font-medium text-gray-600">{{ $doc->creator->name ?? '-' }}</span>
                    </p>
                </div>
            </div>
            <a href="{{ route('approvals.show', $doc->currentVersion) }}"
               class="ml-3 flex-shrink-0 inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                Review
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        @empty
        <div class="py-12 text-center">
            <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-600">Semua dokumen sudah diproses</p>
            <p class="text-xs text-gray-400 mt-1">Tidak ada dokumen yang menunggu approval</p>
        </div>
        @endforelse
    </div>

</div>
@endsection
