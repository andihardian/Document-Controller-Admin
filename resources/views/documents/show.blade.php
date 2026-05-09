@extends('layouts.app')
@section('title', $document->title)
@section('breadcrumb') <a href="{{ route('documents.index') }}">Dokumen</a> / {{ $document->document_number }} @endsection

@section('content')
<div class="space-y-5 max-w-4xl">

    {{-- Header card --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded">{{ $document->document_number }}</span>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full
                        @if($document->status === 'approved') bg-green-100 text-green-700
                        @elseif($document->status === 'pending_approval') bg-yellow-100 text-yellow-700
                        @elseif($document->status === 'rejected') bg-red-100 text-red-700
                        @elseif($document->status === 'expired') bg-orange-100 text-orange-700
                        @elseif($document->status === 'obsolete') bg-purple-100 text-purple-700
                        @else bg-gray-100 text-gray-600
                        @endif">
                        {{ $document->status_label }}
                    </span>
                </div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $document->title }}</h2>
                @if($document->description)
                <p class="text-sm text-gray-500 mt-1">{{ $document->description }}</p>
                @endif
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                @if($document->status === 'approved')
                <a href="{{ route('documents.download', $document) }}"
                   class="inline-flex items-center gap-1.5 bg-green-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download
                </a>
                @endif

                @if(in_array($document->status, ['draft','rejected']) && (auth()->user()->isAdmin() || $document->created_by === auth()->id()))
                <a href="{{ route('documents.edit', $document) }}"
                   class="inline-flex items-center gap-1.5 border border-gray-300 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    Edit
                </a>

                <form action="{{ route('documents.submit', $document) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Submit untuk Approval
                    </button>
                </form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">
            <div>
                <p class="text-xs text-gray-400">Kategori</p>
                <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $document->category->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Departemen</p>
                <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $document->department->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Dibuat oleh</p>
                <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $document->creator->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Versi aktif</p>
                <p class="text-sm font-medium font-mono text-gray-700 mt-0.5">v{{ $document->current_version }}</p>
            </div>
            @if($document->effective_date)
            <div>
                <p class="text-xs text-gray-400">Tanggal Efektif</p>
                <p class="text-sm font-medium text-gray-700 mt-0.5">{{ $document->effective_date->format('d M Y') }}</p>
            </div>
            @endif
            @if($document->expiry_date)
            <div>
                <p class="text-xs text-gray-400">Kadaluarsa</p>
                <p class="text-sm font-medium mt-0.5 {{ $document->isExpired() ? 'text-red-600' : 'text-gray-700' }}">
                    {{ $document->expiry_date->format('d M Y') }}
                    @if($document->isExpired()) <span class="text-xs">(Expired)</span> @endif
                </p>
            </div>
            @endif
        </div>
    </div>

    {{-- Rejected: form revisi --}}
    @if($document->status === 'rejected' && (auth()->user()->isAdmin() || $document->created_by === auth()->id()))
    <div class="bg-red-50 border border-red-200 rounded-xl p-5">
        <h3 class="font-semibold text-red-800 mb-3">Upload Revisi</h3>
        <form action="{{ route('documents.revise', $document) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-red-700 mb-1">File PDF Revisi <span class="text-red-500">*</span></label>
                <input type="file" name="document_file" accept=".pdf"
                       class="w-full text-sm border border-red-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-red-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-red-700 mb-1">Catatan Revisi <span class="text-red-500">*</span></label>
                <input type="text" name="revision_note"
                       class="w-full text-sm border border-red-300 rounded-lg px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-red-500"
                       placeholder="Jelaskan perubahan yang dilakukan...">
            </div>
            <button type="submit" class="bg-red-600 text-white text-sm font-medium px-5 py-2 rounded-lg hover:bg-red-700 transition-colors">
                Upload Revisi
            </button>
        </form>
    </div>
    @endif

    {{-- Version history --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Riwayat Versi</h3>
        <div class="space-y-3">
            @foreach($document->versions as $version)
            <div class="flex items-start justify-between p-3 rounded-lg {{ $version->is_current ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50' }}">
                <div class="flex items-start gap-3">
                    <span class="font-mono text-xs bg-white border border-gray-200 px-2 py-1 rounded font-medium">v{{ $version->version_number }}</span>
                    <div>
                        <p class="text-sm text-gray-700">{{ $version->revision_note ?? '-' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Diupload oleh {{ $version->uploader->name ?? '-' }} ·
                            {{ $version->created_at->format('d M Y H:i') }} ·
                            {{ $version->file_size_human }}
                        </p>
                        @if($version->approved_by && $version->approver)
                        <p class="text-xs text-green-600 mt-0.5">
                            ✓ Disetujui oleh {{ $version->approver->name }} {{ $version->approved_at?->format('d M Y') }}
                        </p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs px-2 py-0.5 rounded-full
                        @if($version->status === 'approved') bg-green-100 text-green-700
                        @elseif($version->status === 'pending_approval') bg-yellow-100 text-yellow-700
                        @elseif($version->status === 'rejected') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-600
                        @endif">
                        {{ $version->status }}
                    </span>
                    @if($version->status === 'approved' || $version->is_current)
                    <a href="{{ route('documents.download', [$document, $version]) }}"
                       class="text-xs text-blue-600 hover:underline">Download</a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Approval history --}}
    @php $allApprovals = $document->versions->flatMap->approvals; @endphp
    @if($allApprovals->count() > 0)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Riwayat Approval</h3>
        <div class="space-y-3">
            @foreach($allApprovals as $approval)
            <div class="flex items-start justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-700">{{ $approval->approver->name ?? '-' }}</p>
                    <p class="text-xs text-gray-400">{{ $approval->action_at?->format('d M Y H:i') ?? 'Belum diproses' }}</p>
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

</div>
@endsection
