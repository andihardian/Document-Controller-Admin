@extends('layouts.app')
@section('title', 'Dashboard Admin')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $stats = [
                ['label' => 'Total Users',      'value' => $totalUsers,       'color' => 'blue',   'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['label' => 'Total Dokumen',    'value' => $totalDocuments,   'color' => 'indigo', 'icon' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ['label' => 'Pending Approval', 'value' => $pendingApprovals, 'color' => 'yellow', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => 'Approved',         'value' => $approvedDocs,     'color' => 'green',  'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">{{ $stat['label'] }}</span>
                <div class="w-9 h-9 bg-{{ $stat['color'] }}-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-{{ $stat['color'] }}-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $stat['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Dokumen per Departemen --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Dokumen per Departemen</h3>
            <div class="space-y-3">
                @forelse($docsByDepartment as $dept)
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $dept->code }}</span>
                        <span class="text-sm text-gray-700">{{ $dept->name }}</span>
                    </div>
                    <span class="text-sm font-semibold text-gray-800">{{ $dept->documents_count }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-400">Belum ada data</p>
                @endforelse
            </div>
        </div>

        {{-- Aktivitas Terbaru --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Aktivitas Terbaru</h3>
            <div class="space-y-3">
                @forelse($recentLogs as $log)
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 bg-slate-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="text-xs font-bold text-slate-500">{{ strtoupper(substr($log->user->name ?? 'S', 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-700 truncate">{{ $log->description }}</p>
                        <p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }} · {{ $log->user->name ?? 'System' }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400">Belum ada aktivitas</p>
                @endforelse
            </div>
            @if($recentLogs->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <a href="{{ route('audit-logs.index') }}" class="text-sm text-blue-600 hover:underline">Lihat semua log →</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Status summary --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Ringkasan Status Dokumen</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $statuses = [
                    ['label' => 'Approved',  'value' => $approvedDocs,    'color' => 'green'],
                    ['label' => 'Pending',   'value' => $pendingApprovals,'color' => 'yellow'],
                    ['label' => 'Rejected',  'value' => $rejectedDocs,    'color' => 'red'],
                    ['label' => 'Expired',   'value' => $expiredDocs,     'color' => 'orange'],
                ];
            @endphp
            @foreach($statuses as $s)
            <div class="text-center p-4 bg-{{ $s['color'] }}-50 rounded-lg">
                <p class="text-2xl font-bold text-{{ $s['color'] }}-600">{{ $s['value'] }}</p>
                <p class="text-sm text-{{ $s['color'] }}-700 mt-1">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
