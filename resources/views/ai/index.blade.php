@extends('layouts.app')
@section('title', 'AI Assistant')

@section('breadcrumb')
    <span class="text-gray-400 dark:text-gray-500">AI</span>
    <span class="mx-1">›</span>
    <span>Assistant</span>
@endsection

@section('content')
<div class="max-w-4xl space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold">AI Document Assistant</h2>
                <p class="text-indigo-200 text-sm">Pilih dokumen untuk mulai bertanya</p>
            </div>
        </div>
        <div class="mt-3 bg-white/10 rounded-xl px-4 py-3">
            <p class="text-sm text-indigo-100">
                <span class="font-semibold">👋 Halo, {{ auth()->user()->name }}!</span>
                Anda dapat bertanya tentang dokumen yang ada di departemen Anda.
                AI akan menjawab berdasarkan isi dokumen yang dipilih.
            </p>
        </div>
    </div>

    {{-- Daftar dokumen --}}
    @if($documents->count() > 0)
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                Dokumen Tersedia
                <span class="ml-2 text-xs font-normal text-gray-400 dark:text-gray-500">({{ $documents->count() }} dokumen)</span>
            </h3>
            @if(auth()->user()->hasRole('admin'))
            <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full">Semua departemen</span>
            @else
            <span class="text-xs text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-800 px-2.5 py-1 rounded-full">
                {{ auth()->user()->department->name ?? 'Departemen Anda' }}
            </span>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($documents as $doc)
            <a href="{{ route('ai.document-context', $doc) }}"
               class="group bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:border-indigo-300 dark:hover:border-indigo-700 hover:shadow-md transition-all cursor-pointer">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 bg-indigo-50 dark:bg-indigo-950/40 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/50 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-xs text-gray-400 dark:text-gray-500">{{ $doc->document_number }}</span>
                            @php $embStatus = $doc->currentVersion?->embedding_status ?? 'pending'; @endphp
                            @if($embStatus === 'done')
                            <span class="text-xs bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-full">AI Siap</span>
                            @else
                            <span class="text-xs bg-amber-100 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 px-1.5 py-0.5 rounded-full">Memproses...</span>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 group-hover:text-indigo-700 dark:group-hover:text-indigo-400 truncate transition-colors">
                            {{ $doc->title }}
                        </p>
                        <div class="flex items-center gap-2 mt-1 flex-wrap">
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $doc->department->name ?? '-' }}</span>
                            <span class="text-gray-300 dark:text-gray-600">·</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">v{{ $doc->current_version }}</span>
                            <span class="text-gray-300 dark:text-gray-600">·</span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $doc->category->name ?? '-' }}</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-indigo-400 flex-shrink-0 mt-1 transition-colors"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
        <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum Ada Dokumen</h3>
        <p class="text-sm text-gray-400 dark:text-gray-500">Belum ada dokumen approved yang bisa diakses AI di departemen Anda.</p>
    </div>
    @endif

    {{-- Cara pakai --}}
    <div class="bg-gray-50 dark:bg-gray-900/60 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">Cara menggunakan AI Assistant:</p>
        <div class="space-y-1.5">
            @foreach([
                'Pilih dokumen yang ingin ditanyakan dari daftar di atas',
                'PDF dokumen akan tampil di sebelah kiri untuk referensi langsung',
                'Ketik pertanyaan di panel AI sebelah kanan — jawaban berdasarkan isi dokumen',
                'Verifikasi jawaban AI dengan membaca dokumen asli di sebelah kiri',
            ] as $i => $step)
            <div class="flex items-start gap-2">
                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex-shrink-0">{{ $i + 1 }}.</span>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $step }}</p>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection