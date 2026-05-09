@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        @php
            $stats = [
                ['label' => 'Total',    'value' => $myDocs,      'color' => 'slate'],
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
            <h3 class="font-semibold text-gray-800">Dokumen Terbaru Saya</h3>
            <a href="{{ route('documents.create') }}"
               class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Dokumen
            </a>
        </div>

        @forelse($recentDocs as $doc)
        <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
            <div>
                <p class="text-sm font-medium text-gray-800">{{ $doc->title }}</p>
                <p class="text-xs text-gray-400">{{ $doc->document_number }} · {{ $doc->category->name ?? '-' }} · {{ $doc->department->name ?? '-' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                    @if($doc->status === 'approved') bg-green-100 text-green-700
                    @elseif($doc->status === 'pending_approval') bg-yellow-100 text-yellow-700
                    @elseif($doc->status === 'rejected') bg-red-100 text-red-700
                    @else bg-gray-100 text-gray-600
                    @endif">
                    {{ $doc->status_label }}
                </span>
                <a href="{{ route('documents.show', $doc) }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-8">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada dokumen. Upload dokumen pertama Anda!</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
