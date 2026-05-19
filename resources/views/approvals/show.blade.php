@extends('layouts.app')
@section('title', 'Review Dokumen')

@section('breadcrumb')
    <a href="{{ route('approvals.index') }}" class="hover:text-gray-600 dark:hover:text-gray-300">Approval</a>
    <span class="mx-1">›</span>
    <span>Review</span>
@endsection

@push('styles')
<style>
    body, html { overflow: hidden; }
    .panel-scroll::-webkit-scrollbar { width: 4px; }
    .panel-scroll::-webkit-scrollbar-track { background: transparent; }
    .panel-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
    .dark .panel-scroll::-webkit-scrollbar-thumb { background: #374151; }
</style>
@endpush

@section('content')

<div class="flex gap-0 -mx-6 -mt-4" style="height: calc(100vh - 4rem);">

    {{-- ══════════════════════════════════════════════
         PANEL KIRI — PDF Viewer
    ══════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 border-r border-gray-200 dark:border-gray-700">

        {{-- Toolbar --}}
        <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-5 flex-shrink-0">

            {{-- Baris 1: Kembali + Judul --}}
            <div class="flex items-center gap-3 py-3 border-b border-gray-100 dark:border-gray-800">
                <a href="{{ route('approvals.index') }}"
                   class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-100 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">{{ $documentVersion->document->title }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 font-mono flex-shrink-0">{{ $documentVersion->document->document_number }}</span>
                </div>

                {{-- Download --}}
                <a href="{{ route('documents.download', [$documentVersion->document, $documentVersion]) }}"
                   class="ml-auto inline-flex items-center gap-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download
                </a>
            </div>

            {{-- Baris 2: Badges --}}
            <div class="flex items-center gap-2 py-2 flex-wrap">
                <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full">
                    📁 {{ $documentVersion->document->category->name ?? '-' }}
                </span>
                <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full">
                    🏢 {{ $documentVersion->document->department->name ?? '-' }}
                </span>
                <span class="text-xs bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 px-2.5 py-1 rounded-full font-mono font-medium">
                    v{{ $documentVersion->version_number }}
                </span>
                <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full">
                    👤 {{ $documentVersion->uploader->name ?? '-' }}
                </span>
                <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2.5 py-1 rounded-full">
                    📦 {{ $documentVersion->file_size_human }}
                </span>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                    @if($documentVersion->status === 'pending_approval') bg-yellow-100 dark:bg-yellow-950/60 text-yellow-700 dark:text-yellow-400
                    @elseif($documentVersion->status === 'approved') bg-green-100 dark:bg-green-950/60 text-green-700 dark:text-green-400
                    @elseif($documentVersion->status === 'rejected') bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400
                    @else bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                    @endif">
                    @if($documentVersion->status === 'pending_approval') ⏳ Pending Review
                    @elseif($documentVersion->status === 'approved') ✅ Approved
                    @elseif($documentVersion->status === 'rejected') ❌ Rejected
                    @else {{ ucfirst($documentVersion->status) }}
                    @endif
                </span>
            </div>
        </div>

        {{-- PDF iframe --}}
        <div class="flex-1 bg-gray-800 dark:bg-gray-950 overflow-hidden">
            <iframe src="{{ asset('storage/' . $documentVersion->file_path) }}#toolbar=1&navpanes=1&scrollbar=1"
                    class="w-full h-full border-0">
            </iframe>
        </div>

        {{-- Bottom bar --}}
        <div class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-5 py-2 flex items-center gap-4 flex-shrink-0">
            @foreach(['ISO 9001:2015 Compliant', 'Version Control', 'Audit Trail', 'Role-Based Access'] as $badge)
            <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <svg class="w-3.5 h-3.5 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $badge }}
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         PANEL KANAN — Review Panel
    ══════════════════════════════════════════════ --}}
    <div class="w-[380px] flex-shrink-0 flex flex-col bg-white dark:bg-gray-900 overflow-y-auto panel-scroll">

        {{-- Panel Header --}}
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Panel Review</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Tinjau dokumen dan berikan keputusan</p>
                </div>
            </div>
        </div>

        <div class="px-5 py-4 space-y-5">

            {{-- Info Dokumen --}}
            <div class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-4 space-y-3">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Informasi Dokumen</p>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Nomor Dokumen</p>
                        <p class="text-sm font-mono font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $documentVersion->document->document_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Versi</p>
                        <p class="text-sm font-mono font-medium text-gray-700 dark:text-gray-300 mt-0.5">v{{ $documentVersion->version_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Kategori</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $documentVersion->document->category->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Departemen</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $documentVersion->document->department->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Diupload oleh</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $documentVersion->uploader->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Tanggal Submit</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $documentVersion->created_at->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Ukuran File</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $documentVersion->file_size_human }}</p>
                    </div>
                    @if($documentVersion->document->effective_date)
                    <div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">Tanggal Efektif</p>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-0.5">{{ $documentVersion->document->effective_date->format('d M Y') }}</p>
                    </div>
                    @endif
                </div>

                @if($documentVersion->revision_note)
                <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Catatan Revisi</p>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-0.5">{{ $documentVersion->revision_note }}</p>
                </div>
                @endif

                @if($documentVersion->document->description)
                <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-400 dark:text-gray-500">Deskripsi</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">{{ $documentVersion->document->description }}</p>
                </div>
                @endif
            </div>

            {{-- Checklist Review --}}
            @if($documentVersion->status === 'pending_approval')
            <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900 rounded-xl p-4"
                 x-data="{ checks: { format: false, content: false, dept: false, comply: false } }">
                <p class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wide mb-3">Checklist Review</p>
                <div class="space-y-2.5">
                    @foreach([
                        ['key' => 'format',  'label' => 'Format & struktur dokumen sesuai standar'],
                        ['key' => 'content', 'label' => 'Isi dokumen lengkap dan akurat'],
                        ['key' => 'dept',    'label' => 'Sesuai kebutuhan departemen'],
                        ['key' => 'comply',  'label' => 'Tidak melanggar kebijakan perusahaan'],
                    ] as $item)
                    <label class="flex items-start gap-2.5 cursor-pointer group">
                        <input type="checkbox" x-model="checks.{{ $item['key'] }}"
                               class="w-4 h-4 mt-0.5 rounded border-blue-300 dark:border-blue-700 text-blue-600 focus:ring-blue-500 flex-shrink-0 bg-white dark:bg-gray-800">
                        <span class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed group-hover:text-blue-900 dark:group-hover:text-blue-200">
                            {{ $item['label'] }}
                        </span>
                    </label>
                    @endforeach
                </div>
                <div class="mt-3 pt-3 border-t border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs text-blue-600 dark:text-blue-400">Progress review</span>
                        <span class="text-xs font-semibold text-blue-700 dark:text-blue-300"
                              x-text="Object.values(checks).filter(Boolean).length + ' / 4'"></span>
                    </div>
                    <div class="w-full bg-blue-200 dark:bg-blue-900 rounded-full h-1.5">
                        <div class="bg-blue-600 dark:bg-blue-500 h-1.5 rounded-full transition-all duration-300"
                             :style="'width: ' + (Object.values(checks).filter(Boolean).length / 4 * 100) + '%'">
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tombol Aksi --}}
            @if($documentVersion->status === 'pending_approval')

            {{-- Setujui --}}
            <div class="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-900 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-green-800 dark:text-green-400 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Setujui Dokumen
                </h3>
                <form action="{{ route('approvals.approve', $documentVersion) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-green-700 dark:text-green-400 mb-1">Komentar (opsional)</label>
                        <textarea name="comments" rows="2"
                                  class="w-full text-sm border border-green-300 dark:border-green-800 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 resize-none"
                                  placeholder="Dokumen sudah sesuai standar..."></textarea>
                    </div>
                    <button type="submit"
                            onclick="return confirm('Yakin ingin menyetujui dokumen ini?')"
                            class="w-full bg-green-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-green-700 active:scale-95 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Setujui Dokumen
                    </button>
                </form>
            </div>

            {{-- Tolak --}}
            <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900 rounded-xl p-4">
                <h3 class="text-sm font-semibold text-red-800 dark:text-red-400 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Tolak Dokumen
                </h3>
                <form action="{{ route('approvals.reject', $documentVersion) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-red-700 dark:text-red-400 mb-1">
                            Alasan Penolakan <span class="text-red-500">*</span>
                        </label>
                        <textarea name="comments" rows="2" required
                                  class="w-full text-sm border border-red-300 dark:border-red-800 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-red-500 resize-none"
                                  placeholder="Jelaskan alasan penolakan dokumen..."></textarea>
                    </div>
                    <button type="submit"
                            onclick="return confirm('Yakin ingin menolak dokumen ini?')"
                            class="w-full bg-red-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-red-700 active:scale-95 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tolak Dokumen
                    </button>
                </form>
            </div>

            @else
            {{-- Sudah diproses --}}
            <div class="rounded-xl border p-5 text-center
                @if($documentVersion->status === 'approved') bg-green-50 dark:bg-green-950/30 border-green-200 dark:border-green-900
                @elseif($documentVersion->status === 'rejected') bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-900
                @else bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 @endif">
                <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3
                    @if($documentVersion->status === 'approved') bg-green-100 dark:bg-green-900/60
                    @elseif($documentVersion->status === 'rejected') bg-red-100 dark:bg-red-900/60
                    @else bg-gray-100 dark:bg-gray-700 @endif">
                    @if($documentVersion->status === 'approved')
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    @endif
                </div>
                <p class="text-sm font-semibold
                    @if($documentVersion->status === 'approved') text-green-700 dark:text-green-400
                    @elseif($documentVersion->status === 'rejected') text-red-700 dark:text-red-400
                    @else text-gray-700 dark:text-gray-300 @endif">
                    Dokumen telah {{ $documentVersion->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                </p>
                @if($documentVersion->approved_at)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $documentVersion->approved_at->format('d M Y H:i') }}</p>
                @endif
            </div>
            @endif

            {{-- Riwayat Approval --}}
            @if($documentVersion->approvals->count() > 0)
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Riwayat Approval</p>
                <div class="space-y-2">
                    @foreach($documentVersion->approvals as $approval)
                    <div class="flex items-start justify-between p-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl border border-gray-100 dark:border-gray-700">
                        <div class="flex items-start gap-2.5">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold
                                @if($approval->status === 'approved') bg-green-100 dark:bg-green-950/60 text-green-700 dark:text-green-400
                                @elseif($approval->status === 'rejected') bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400
                                @else bg-yellow-100 dark:bg-yellow-950/60 text-yellow-700 dark:text-yellow-400 @endif">
                                {{ strtoupper(substr($approval->approver->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $approval->approver->name ?? '-' }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $approval->action_at?->format('d M Y H:i') ?? 'Menunggu tindakan' }}
                                </p>
                                @if($approval->comments)
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 italic">"{{ $approval->comments }}"</p>
                                @endif
                            </div>
                        </div>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0 ml-2
                            @if($approval->status === 'approved') bg-green-100 dark:bg-green-950/60 text-green-700 dark:text-green-400
                            @elseif($approval->status === 'rejected') bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400
                            @else bg-yellow-100 dark:bg-yellow-950/60 text-yellow-700 dark:text-yellow-400 @endif">
                            {{ ucfirst($approval->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>{{-- END content --}}
    </div>{{-- END panel kanan --}}

</div>
@endsection
