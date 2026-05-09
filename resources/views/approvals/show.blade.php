@extends('layouts.app')
@section('title', 'Review Dokumen')
@section('breadcrumb') <a href="{{ route('approvals.index') }}">Approval</a> / Review @endsection

@section('content')
<div class="max-w-3xl space-y-5">

    {{-- Document info --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-xs font-mono text-gray-400 mb-1">{{ $documentVersion->document->document_number }}</p>
                <h2 class="text-lg font-semibold text-gray-800">{{ $documentVersion->document->title }}</h2>
                @if($documentVersion->document->description)
                <p class="text-sm text-gray-500 mt-1">{{ $documentVersion->document->description }}</p>
                @endif
            </div>
            <span class="font-mono text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1.5 rounded-lg">
                v{{ $documentVersion->version_number }}
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-400">Kategori</p>
                <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $documentVersion->document->category->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Departemen</p>
                <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $documentVersion->document->department->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Diupload oleh</p>
                <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $documentVersion->uploader->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Ukuran File</p>
                <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $documentVersion->file_size_human }}</p>
            </div>
        </div>

        @if($documentVersion->revision_note)
        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
            <p class="text-xs text-gray-400 mb-1">Catatan Revisi</p>
            <p class="text-sm text-gray-700">{{ $documentVersion->revision_note }}</p>
        </div>
        @endif

        {{-- Download for review --}}
        <div class="mt-4 pt-4 border-t border-gray-100">
            <a href="{{ route('documents.download', [$documentVersion->document, $documentVersion]) }}"
               class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF untuk Review
            </a>
        </div>
    </div>

    {{-- Approval action --}}
    @if($documentVersion->status === 'pending_approval')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Approve --}}
        <div class="bg-green-50 border border-green-200 rounded-xl p-5">
            <h3 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Setujui Dokumen
            </h3>
            <form action="{{ route('approvals.approve', $documentVersion) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-green-700 mb-1">Komentar (opsional)</label>
                    <textarea name="comments" rows="3"
                              class="w-full text-sm border border-green-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-green-500"
                              placeholder="Dokumen sudah sesuai standar..."></textarea>
                </div>
                <button type="submit"
                        onclick="return confirm('Yakin ingin menyetujui dokumen ini?')"
                        class="w-full bg-green-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-green-700 transition-colors">
                    Setujui Dokumen
                </button>
            </form>
        </div>

        {{-- Reject --}}
        <div class="bg-red-50 border border-red-200 rounded-xl p-5">
            <h3 class="font-semibold text-red-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tolak Dokumen
            </h3>
            <form action="{{ route('approvals.reject', $documentVersion) }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-red-700 mb-1">Alasan Penolakan <span class="text-red-500">*</span></label>
                    <textarea name="comments" rows="3" required
                              class="w-full text-sm border border-red-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-red-500"
                              placeholder="Jelaskan alasan penolakan dokumen..."></textarea>
                </div>
                <button type="submit"
                        onclick="return confirm('Yakin ingin menolak dokumen ini?')"
                        class="w-full bg-red-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-red-700 transition-colors">
                    Tolak Dokumen
                </button>
            </form>
        </div>
    </div>
    @else
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-center text-gray-500 text-sm">
        Dokumen ini sudah diproses dengan status: <strong>{{ $documentVersion->status }}</strong>
    </div>
    @endif

    {{-- Approval history --}}
    @if($documentVersion->approvals->count() > 0)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Riwayat Approval</h3>
        <div class="space-y-2">
            @foreach($documentVersion->approvals as $approval)
            <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $approval->approver->name ?? '-' }}</p>
                    <p class="text-xs text-gray-400">{{ $approval->action_at?->format('d M Y H:i') ?? 'Menunggu' }}</p>
                    @if($approval->comments)
                    <p class="text-sm text-gray-600 mt-1 italic">"{{ $approval->comments }}"</p>
                    @endif
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                    @if($approval->status === 'approved') bg-green-100 text-green-700
                    @elseif($approval->status === 'rejected') bg-red-100 text-red-700
                    @else bg-yellow-100 text-yellow-700
                    @endif">
                    {{ ucfirst($approval->status) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div>
        <a href="{{ route('approvals.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Kembali ke Daftar Approval</a>
    </div>
</div>
@endsection
