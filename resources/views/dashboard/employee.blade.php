@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Welcome --}}
    <div class="bg-gradient-to-r from-slate-900 to-blue-900 rounded-2xl p-6 flex items-center justify-between">
        <div>
            <p class="text-blue-300 text-sm font-medium mb-1">Selamat datang kembali 👋</p>
            <h2 class="text-xl font-bold text-white">{{ auth()->user()->name }}</h2>
            <p class="text-slate-400 text-xs mt-1">{{ now()->translatedFormat('l, d F Y') }} · {{ auth()->user()->department->name ?? 'Employee' }}</p>
        </div>
        <a href="{{ route('documents.create') }}"
           class="hidden md:inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Upload Dokumen
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
        $stats = [
            ['label' => 'Total',    'value' => $myDocs,       'bg' => 'from-slate-600 to-slate-800', 'sub' => 'Dokumen saya'],
            ['label' => 'Draft',    'value' => $draftDocs,    'bg' => 'from-gray-400 to-gray-600',   'sub' => 'Belum disubmit'],
            ['label' => 'Pending',  'value' => $pendingDocs,  'bg' => 'from-amber-500 to-orange-500','sub' => 'Sedang direview'],
            ['label' => 'Approved', 'value' => $approvedDocs, 'bg' => 'from-emerald-500 to-green-600','sub' => 'Disetujui'],
            ['label' => 'Rejected', 'value' => $rejectedDocs, 'bg' => 'from-red-500 to-rose-600',   'sub' => 'Perlu direvisi'],
        ];
        @endphp

        @foreach($stats as $stat)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="w-2 h-2 rounded-full bg-gradient-to-r {{ $stat['bg'] }} mb-3"></div>
            <p class="text-2xl font-bold text-gray-800 leading-none">{{ $stat['value'] }}</p>
            <p class="text-sm font-medium text-gray-600 mt-0.5">{{ $stat['label'] }}</p>
            <p class="text-xs text-gray-400">{{ $stat['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Rejected alert --}}
    @if($rejectedDocs > 0)
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-center gap-3">
        <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-medium text-red-800">{{ $rejectedDocs }} dokumen ditolak dan perlu direvisi</p>
            <p class="text-xs text-red-600 mt-0.5">Buka detail dokumen untuk melihat alasan penolakan dan upload revisi</p>
        </div>
        <a href="{{ route('documents.index', ['status' => 'rejected']) }}"
           class="text-xs bg-red-600 text-white font-medium px-3 py-1.5 rounded-lg hover:bg-red-700 transition-colors flex-shrink-0">
            Lihat
        </a>
    </div>
    @endif

    {{-- Recent documents --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800">Dokumen Terbaru Saya</h3>
                <p class="text-xs text-gray-400 mt-0.5">Aktivitas dokumen Anda</p>
            </div>
            <a href="{{ route('documents.index') }}"
               class="text-xs text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                Lihat semua
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        @forelse($recentDocs as $doc)
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50 hover:bg-gray-50 transition-colors last:border-0">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                    @if($doc->status === 'approved') bg-emerald-50
                    @elseif($doc->status === 'pending_approval') bg-amber-50
                    @elseif($doc->status === 'rejected') bg-red-50
                    @else bg-gray-100 @endif">
                    <svg class="w-4.5 h-4.5 w-5 h-5
                        @if($doc->status === 'approved') text-emerald-500
                        @elseif($doc->status === 'pending_approval') text-amber-500
                        @elseif($doc->status === 'rejected') text-red-500
                        @else text-gray-400 @endif"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $doc->title }}</p>
                    <p class="text-xs text-gray-400">
                        <span class="font-mono">{{ $doc->document_number }}</span>
                        · {{ $doc->category->name ?? '-' }}
                        · {{ $doc->created_at->format('d M Y') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 ml-3 flex-shrink-0">
                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                    @if($doc->status === 'approved') bg-emerald-100 text-emerald-700
                    @elseif($doc->status === 'pending_approval') bg-amber-100 text-amber-700
                    @elseif($doc->status === 'rejected') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-600 @endif">
                    {{ $doc->status_label }}
                </span>
                <a href="{{ route('documents.show', $doc) }}"
                   class="text-gray-300 hover:text-gray-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="py-14 text-center">
            <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-600">Belum ada dokumen</p>
            <p class="text-xs text-gray-400 mt-1 mb-4">Upload dokumen pertama Anda sekarang</p>
            <a href="{{ route('documents.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Dokumen
            </a>
        </div>
        @endforelse
    </div>

</div>
@endsection
