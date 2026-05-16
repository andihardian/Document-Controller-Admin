@extends('layouts.app')
@section('title', 'AI — ' . $document->title)

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="hover:text-gray-600">Dokumen</a>
    <span class="mx-1">›</span>
    <a href="{{ route('ai.index') }}" class="hover:text-gray-600">AI Assistant</a>
    <span class="mx-1">›</span>
    <span class="truncate max-w-xs">{{ $document->title }}</span>
@endsection

@push('styles')
<style>
    /* Full height layout tanpa overflow parent */
    body, html { overflow: hidden; }

    .typing-dot {
        animation: typingBounce 1.2s infinite ease-in-out;
    }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typingBounce {
        0%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-6px); }
    }

    /* PDF iframe */
    #pdf-viewer {
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Scrollbar tipis untuk chat */
    .chat-scroll::-webkit-scrollbar { width: 4px; }
    .chat-scroll::-webkit-scrollbar-track { background: transparent; }
    .chat-scroll::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
    .chat-scroll::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
@endpush

@section('content')
{{-- Override content area ke full-height flex --}}
<div class="flex gap-0 -mx-6 -mt-4" style="height: calc(100vh - 4rem);"
     x-data="documentAiChat()" x-init="init()">

    {{-- ══════════════════════════════════════════════
         PANEL KIRI — PDF Viewer
    ══════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col min-w-0 border-r border-gray-200">

        {{-- Toolbar PDF --}}
        <div class="bg-white border-b border-gray-200 px-5 py-3 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('ai.index') }}"
                   class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
                <span class="text-gray-300">|</span>
                <div>
                    <span class="text-sm font-semibold text-gray-800">{{ $document->title }}</span>
                    <span class="ml-2 text-xs text-gray-400 font-mono">{{ $document->document_number }}</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Info badges --}}
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">
                    📁 {{ $document->category->name ?? '-' }}
                </span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">
                    🏢 {{ $document->department->name ?? '-' }}
                </span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">
                    📅 v{{ $document->currentVersion?->version_number ?? $document->current_version }}
                </span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">
                    📤 {{ $document->currentVersion?->created_at?->format('d M Y') ?? '-' }}
                </span>
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">
                    👤 {{ $document->creator->name ?? '-' }}
                </span>
                <a href="{{ route('documents.download', $document) }}"
                   class="inline-flex items-center gap-1.5 bg-white border border-gray-300 text-gray-700 text-sm font-medium px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download
                </a>
            </div>
        </div>

        {{-- PDF iframe --}}
        <div class="flex-1 bg-gray-800 overflow-hidden">
            @php
                $fileUrl = $document->currentVersion
                    ? asset('storage/' . $document->currentVersion->file_path)
                    : null;
            @endphp

            @if($fileUrl)
            <iframe id="pdf-viewer"
                    src="{{ $fileUrl }}#toolbar=1&navpanes=1&scrollbar=1"
                    class="w-full h-full border-0">
            </iframe>
            @else
            <div class="flex items-center justify-center h-full text-gray-400">
                <div class="text-center">
                    <svg class="w-16 h-16 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm">File dokumen tidak tersedia</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Bottom badge bar --}}
        <div class="bg-white border-t border-gray-200 px-5 py-2 flex items-center gap-4 flex-shrink-0">
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                ISO 9001:2015 Compliant
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Version Control
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Audit Trail
            </div>
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Role-Based Access
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         PANEL KANAN — AI Chat
    ══════════════════════════════════════════════ --}}
    <div class="w-[380px] flex-shrink-0 flex flex-col bg-white">

        {{-- Chat Header --}}
        <div class="px-5 py-4 border-b border-gray-100 flex-shrink-0">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-semibold text-gray-800">AI Assistant</p>
                            <span class="text-xs bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-medium">BETA</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    {{-- Riwayat chat --}}
                    @if($chats->count() > 0)
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" title="Riwayat Chat"
                                class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open = false"
                             class="absolute right-0 top-10 w-64 bg-white rounded-xl border border-gray-200 shadow-lg z-10 overflow-hidden">
                            <p class="text-xs font-semibold text-gray-500 px-3 py-2 border-b border-gray-100">Riwayat Chat</p>
                            @foreach($chats as $ch)
                            <button @click="loadChat({{ $ch->id }}); open = false"
                                    class="w-full text-left px-3 py-2.5 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0">
                                <p class="text-xs font-medium text-gray-700 truncate">{{ $ch->title }}</p>
                                <p class="text-xs text-gray-400">{{ $ch->last_activity_at?->diffForHumans() }}</p>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Chat baru --}}
                    <button @click="newChat()" title="Chat Baru"
                            class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </button>

                    {{-- Bandingkan versi --}}
                    @if($document->versions->count() >= 2)
                    <button @click="showCompareModal = true" title="Bandingkan Versi"
                            class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                    </button>
                    @endif

                    {{-- Tutup --}}
                    <a href="{{ route('ai.index') }}"
                       class="w-8 h-8 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- AI embedding status --}}
            @php $embStatus = $document->currentVersion?->embedding_status ?? 'pending'; @endphp
            @if($embStatus !== 'done')
            <div class="mt-3 flex items-center gap-2 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                <svg class="w-3.5 h-3.5 text-amber-500 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <p class="text-xs text-amber-700">Dokumen sedang diproses untuk AI...</p>
            </div>
            @endif
        </div>

        {{-- Summary result --}}
        <template x-if="summaryResult">
            <div class="border-b border-gray-100 bg-indigo-50 px-5 py-3 flex-shrink-0 max-h-40 overflow-y-auto">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-xs font-semibold text-indigo-700">📋 Ringkasan Dokumen</span>
                    <button @click="summaryResult = null" class="text-indigo-400 hover:text-indigo-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-indigo-800 leading-relaxed" x-html="formatMessage(summaryResult)"></p>
            </div>
        </template>

        {{-- Messages --}}
        <div x-ref="messagesContainer"
             class="flex-1 overflow-y-auto chat-scroll px-5 py-4 space-y-4">

            {{-- Empty / Welcome state --}}
            <template x-if="messages.length === 0 && !isTyping">
                <div class="py-6">
                    <div class="text-center mb-5">
                        <p class="text-base font-semibold text-gray-800">
                            Halo, {{ auth()->user()->name }} 👋
                        </p>
                        <p class="text-sm text-gray-500 mt-1 leading-relaxed">
                            Saya siap membantu Anda mencari informasi dari dokumen ini.
                        </p>
                    </div>

                    <p class="text-xs font-semibold text-gray-500 mb-2.5">Saran pertanyaan</p>
                    <div class="space-y-2">
                        @foreach([
                            ['icon' => '📄', 'text' => 'Apa isi utama dokumen ini?'],
                            ['icon' => '📅', 'text' => 'Apa prosedur yang dijelaskan?'],
                            ['icon' => '👤', 'text' => 'Siapa yang bertanggung jawab?'],
                            ['icon' => '✨', 'text' => 'Tampilkan ringkasan dokumen ini'],
                        ] as $sug)
                        <button @click="quickAsk('{{ $sug['text'] }}')"
                                class="w-full flex items-center gap-3 text-left text-sm bg-gray-50 hover:bg-indigo-50 hover:text-indigo-700 border border-gray-200 hover:border-indigo-200 px-3.5 py-2.5 rounded-xl transition-all group">
                            <span class="text-base flex-shrink-0">{{ $sug['icon'] }}</span>
                            <span class="text-sm text-gray-700 group-hover:text-indigo-700">{{ $sug['text'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
            </template>

            {{-- Messages --}}
            <template x-for="msg in messages" :key="msg.id">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start gap-2.5'">

                    {{-- AI avatar --}}
                    <template x-if="msg.role === 'assistant'">
                        <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                    </template>

                    <div class="max-w-[85%]">
                        {{-- Bubble --}}
                        <div class="rounded-2xl px-4 py-3 text-sm leading-relaxed"
                             :class="msg.role === 'user'
                                 ? 'bg-indigo-600 text-white rounded-tr-sm'
                                 : 'bg-gray-50 border border-gray-200 text-gray-800 rounded-tl-sm'">
                            <div x-html="formatMessage(msg.content)"></div>
                        </div>

                        {{-- Sources --}}
                        <template x-if="msg.role === 'assistant' && msg.sources && msg.sources.length > 0">
                            <div class="mt-2 space-y-1">
                                <p class="text-xs text-gray-400 font-medium">Sumber:</p>
                                <template x-for="src in msg.sources" :key="src.chunk_id ?? src.document">
                                    <div class="inline-flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs px-2.5 py-1 rounded-lg mr-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="src.document ?? 'Dokumen'"></span>
                                        <template x-if="src.page">
                                            <span x-text="'(Hal. ' + src.page + ')'"></span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Time + thumbs for assistant --}}
                        <div class="flex items-center gap-2 mt-1"
                             :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                            <p class="text-xs text-gray-400" x-text="formatTime(msg.created_at)"></p>
                            <template x-if="msg.role === 'assistant'">
                                <div class="flex items-center gap-1">
                                    <button class="text-gray-300 hover:text-green-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                        </svg>
                                    </button>
                                    <button class="text-gray-300 hover:text-red-500 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <template x-if="isTyping">
                <div class="flex gap-2.5">
                    <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-2xl rounded-tl-sm px-4 py-3">
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full typing-dot"></div>
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full typing-dot"></div>
                            <div class="w-1.5 h-1.5 bg-gray-400 rounded-full typing-dot"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Quick action: Ringkas --}}
        <div class="px-5 pb-2 flex-shrink-0">
            <button @click="summarize()"
                    :disabled="isSummarizing"
                    class="w-full flex items-center justify-center gap-1.5 text-xs text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed border border-indigo-100 rounded-lg px-3 py-1.5 transition-colors">
                <template x-if="!isSummarizing">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6m-6 4h3"/>
                    </svg>
                </template>
                <template x-if="isSummarizing">
                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </template>
                <span x-text="isSummarizing ? 'Meringkas...' : 'Tampilkan ringkasan dokumen ini'"></span>
            </button>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-100 px-5 py-4 flex-shrink-0">
            <div class="flex items-end gap-2">
                <textarea x-model="inputMessage"
                          x-ref="chatInput"
                          @keydown.enter.prevent="!$event.shiftKey && sendMessage()"
                          @input="autoResize($event)"
                          :disabled="isTyping"
                          placeholder="Tanyakan sesuatu tentang dokumen..."
                          rows="1"
                          class="flex-1 text-sm border border-gray-200 rounded-xl px-3.5 py-2.5 resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
                          style="min-height: 42px; max-height: 120px;"></textarea>
                <button @click="sendMessage()"
                        :disabled="!inputMessage.trim() || isTyping"
                        class="w-10 h-10 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed text-white rounded-xl flex items-center justify-center flex-shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2 text-center">✓ AI dapat membuat kesalahan. Verifikasi informasi penting.</p>
        </div>
    </div>

</div>

{{-- Modal Bandingkan Versi --}}
<template x-if="showCompareModal">
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg" @click.outside="showCompareModal = false">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Bandingkan Versi</h3>
                <button @click="showCompareModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Versi Lama</label>
                    <select x-model="compareOldId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Pilih versi...</option>
                        @foreach($document->versions as $v)
                        <option value="{{ $v->id }}">v{{ $v->version_number }} — {{ $v->created_at->format('d M Y') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Versi Baru</label>
                    <select x-model="compareNewId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Pilih versi...</option>
                        @foreach($document->versions as $v)
                        <option value="{{ $v->id }}">v{{ $v->version_number }} — {{ $v->created_at->format('d M Y') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="p-5 border-t border-gray-100 flex justify-end gap-3">
                <button @click="showCompareModal = false" class="px-4 py-2 text-sm text-gray-600">Batal</button>
                <button @click="compareVersions()"
                        :disabled="!compareOldId || !compareNewId || isComparing"
                        class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 disabled:bg-gray-200 text-white rounded-lg transition-colors">
                    <span x-text="isComparing ? 'Membandingkan...' : 'Bandingkan'"></span>
                </button>
            </div>
        </div>
    </div>
</template>

{{-- Modal Hasil Compare --}}
<template x-if="compareResult">
    <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                <div>
                    <h3 class="font-semibold text-gray-800">Hasil Perbandingan</h3>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="compareResult.old_version + ' → ' + compareResult.new_version"></p>
                </div>
                <button @click="compareResult = null" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                <div class="text-sm text-gray-700 leading-relaxed" x-html="formatMessage(compareResult.changes)"></div>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
function documentAiChat() {
    return {
        documentId: {{ $document->id }},
        messages: [],
        activeChatId: null,
        activeChatTitle: '',
        inputMessage: '',
        isTyping: false,
        isSummarizing: false,
        isComparing: false,
        summaryResult: null,
        compareResult: null,
        showCompareModal: false,
        compareOldId: '',
        compareNewId: '',

        init() {
            @if($chats->count() > 0)
            this.loadChat({{ $chats->first()->id }});
            @else
            this.newChat();
            @endif
        },

        async newChat() {
            try {
                const res = await fetch('{{ route("ai.chats.create") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ document_id: this.documentId }),
                });
                const data = await res.json();
                if (data.success) {
                    this.activeChatId = data.chat.id;
                    this.activeChatTitle = data.chat.title;
                    this.messages = [];
                    this.summaryResult = null;
                }
            } catch (e) {
                console.error('Gagal membuat chat:', e);
            }
        },

        async loadChat(chatId) {
            try {
                const res = await fetch(`/ai/chats/${chatId}`);
                const data = await res.json();
                this.activeChatId = chatId;
                this.activeChatTitle = data.chat.title;
                this.messages = data.messages;
                this.$nextTick(() => this.scrollToBottom());
            } catch (e) {
                console.error('Gagal load chat:', e);
            }
        },

        async sendMessage() {
            const msg = this.inputMessage.trim();
            if (!msg || this.isTyping) return;

            if (!this.activeChatId) await this.newChat();
            if (!this.activeChatId) return;

            this.messages.push({
                id: Date.now(), role: 'user', content: msg,
                sources: [], created_at: new Date().toISOString(),
            });
            this.inputMessage = '';
            this.isTyping = true;

            if (this.$refs.chatInput) this.$refs.chatInput.style.height = 'auto';
            this.$nextTick(() => this.scrollToBottom());

            try {
                const res = await fetch(`/ai/chats/${this.activeChatId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ message: msg, document_id: this.documentId }),
                });
                const data = await res.json();
                this.messages.push(data.success ? data.message : {
                    id: Date.now(), role: 'assistant',
                    content: data.message || 'Terjadi kesalahan.',
                    sources: [], created_at: new Date().toISOString(),
                });
            } catch {
                this.messages.push({
                    id: Date.now(), role: 'assistant',
                    content: 'Koneksi gagal. Silakan coba lagi.',
                    sources: [], created_at: new Date().toISOString(),
                });
            } finally {
                this.isTyping = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        quickAsk(question) {
            this.inputMessage = question;
            this.sendMessage();
        },

        async summarize() {
            if (this.isSummarizing) return;
            this.isSummarizing = true;
            try {
                const res = await fetch(`/ai/documents/${this.documentId}/summarize`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const data = await res.json();
                if (data.success) this.summaryResult = data.data.summary;
            } catch (e) {
                console.error('Gagal meringkas:', e);
            } finally {
                this.isSummarizing = false;
            }
        },

        async compareVersions() {
            if (!this.compareOldId || !this.compareNewId) return;
            this.isComparing = true;
            try {
                const res = await fetch(`/ai/documents/${this.documentId}/compare-versions`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        old_version_id: this.compareOldId,
                        new_version_id: this.compareNewId,
                    }),
                });
                const data = await res.json();
                if (data.success) {
                    this.compareResult = data.data;
                    this.showCompareModal = false;
                }
            } finally {
                this.isComparing = false;
            }
        },

        scrollToBottom() {
            const el = this.$refs.messagesContainer;
            if (el) el.scrollTop = el.scrollHeight;
        },

        autoResize(event) {
            const el = event.target;
            el.style.height = 'auto';
            el.style.height = Math.min(el.scrollHeight, 120) + 'px';
        },

        formatMessage(content) {
            if (!content) return '';
            return content
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/## (.+)/g, '<p class="font-semibold mt-2 mb-1">$1</p>')
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/^- (.+)/gm, '<li class="ml-4 list-disc text-sm">$1</li>')
                .replace(/\n/g, '<br>');
        },

        formatTime(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
    };
}
</script>
@endpush
@endsection