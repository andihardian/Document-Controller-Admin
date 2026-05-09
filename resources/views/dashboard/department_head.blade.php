@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $stats = [
                ['label' => 'Total',    'value' => $totalDocs,   'color' => 'slate'],
                ['label' => 'Draft',    'value' => $draftDocs,   'color' => 'gray'],
                ['label' => 'Pending',  'value' => $pendingDocs, 'color' => 'yellow'],
                ['label' => 'Approved', 'value' => $approvedDocs,'color' => 'green'],
                ['label' => 'Rejected', 'value' => $rejectedDocs,'color' => 'red'],
            ];
        @endphp
        @foreach($stats as $stat)
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $stat['value'] }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ $stat['label'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Dokumen Menunggu Approval</h3>
            <a href="{{ route('approvals.index') }}" class="text-sm text-blue-600 hover:underline">Lihat semua →</a>
        </div>

        @forelse($pendingList as $doc)
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
                <p class="text-sm font-medium text-gray-800">{{ $doc->title }}</p>
                <p class="text-xs text-gray-400">{{ $doc->document_number }} · {{ $doc->category->name ?? '-' }} · Oleh {{ $doc->creator->name ?? '-' }}</p>
            </div>
            <a href="{{ route('approvals.show', $doc->currentVersion) }}"
               class="text-xs bg-yellow-100 text-yellow-800 font-medium px-3 py-1 rounded-full hover:bg-yellow-200 transition-colors">
                Review
            </a>
        </div>
        @empty
        <p class="text-sm text-gray-400 py-4 text-center">Tidak ada dokumen yang menunggu approval</p>
        @endforelse
    </div>
</div>
@endsection
