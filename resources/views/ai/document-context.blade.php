@extends('layouts.app')
@section('title', 'AI — ' . $document->title)

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="hover:text-gray-600">Dokumen</a>
    <span class="mx-1">›</span>
    <a href="{{ route('documents.show', $document) }}" class="hover:text-gray-600 truncate max-w-xs">{{ $document->title }}</a>
    <span class="mx-1">›</span>
    <span>AI Assistant</span>
@endsection

@section('content')
<div class="flex gap-5 h-[calc(100vh-10rem)]" x-data="documentAiChat()" x-init="init()">

    {{-- ── PANEL KIRI: Info Dokumen + Fitur ──────────────────────────────── --}}
    <div class="w-80 flex-shrink-0 flex flex-col gap-4">

        {{-- Info Dokumen --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <div class="flex items-start gap-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-800 leading-tight">{{ $document->title }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $document->document_number }}</p>
                </div>
            </div>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                    <span class="text-gray-500">Departemen</span>
                    <span class="font-medium text-gray-700">{{ $document->department->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                    <span class="text-gray-500">Versi</span>
                    <span class="font-medium text-gray-700">{{ $document->currentVersion?->version_number ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center py-1.5 border-b border-gray-50">
                    <span class="text-gray-500">Status</span>
                    @php
                    $statusMap = [
                        'approved' => ['bg-emerald-100 text-emerald-700', 'Approved'],
                        'pending'  => ['bg-amber-100 text-amber-700', 'Pending'],
                        'rejected' => ['bg-red-100 text-red-700', 'Rejected'],
                        'draft'    => ['bg-gray-100 text-gray-600', 'Draft'],
                    ];
                    [$sCls, $sLabel] = $statusMap[$document->status] ?? ['bg-gray-100 text-gray-600', $document->status];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $sCls }}">{{ $sLabel }}</span>
                </div>
                <div class="flex justify-between items-center py-1.5">
                    <span class="text-gray-500">AI Status</span>
                    @php
                    $embStatus = $document->currentVersion?->embedding_status ?? 'pending';
                    $embMap = [
                        'done'       => ['bg-emerald-100 text-emerald-700', 'Siap'],
                        'pending'    => ['bg-amber-100 text-amber-700', 'Proses...'],
                        'processing' => ['bg-blue-100 text-blue-700', 'Memproses'],
                        'failed'     => ['bg-red-100 text-red-700', 'Gagal'],
                    ];
                    [$eCls, $eLabel] = $embMap[$embStatus] ?? ['bg-gray-100 text-gray-600', $embStatus];
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $eCls }}">{{ $eLabel }}</span>
                </div>
            </div>
        </div>

        {{-- Fitur AI --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Fitur AI</p>

            {{-- Summarize --}}
            <button @click="summarize()"
                    :disabled="isSummarizing"
                    class="w-full flex items-center gap-3 p-3 bg-indigo-50 hover:bg-indigo-100 disabled:opacity-50 disabled:cursor-not-allowed border border-indigo-100 rounded-xl transition-all mb-2">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12h6m-6 4h3"/>
                    </svg>
                </div>
                <div class="text-left flex-1">
                    <p class="text-xs font-semibold text-indigo-700">Ringkas Dokumen</p>
                    <p class="text-xs text-indigo-500 mt-0.5">Buat ringkasan otomatis</p>
                </div>
                <template x-if="isSummarizing">
                    <svg class="w-4 h-4 text-indigo-400 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                </template>
            </button>

            {{-- Compare versions --}}
            @if($document->versions->count() >= 2)
            <button @click="showCompareModal = true"
                    class="w-full flex items-center gap-3 p-3 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-xl transition-all">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="text-left">
                    <p class="text-xs font-semibold text-blue-700">Bandingkan Versi</p>
                    <p class="text-xs text-blue-500 mt-0.5">Lihat perbedaan antar versi</p>
                </div>
            </button>
            @endif
        </div>

        {{-- Riwayat chat dokumen ini --}}
        @if($chats->count() > 0)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex-1 overflow-hidden flex flex-col">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Riwayat Chat</p>
            <div class="space-y-1 overflow-y-auto flex-1">
                @foreach($chats as $ch)
                <button onclick="window.documentAiChatInstance && window.documentAiChatInstance.loadChat({{ $ch->id }})"
                        class="w-full text-left px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    <p class="text-xs font-medium text-gray-700 truncate">{{ $ch->title }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $ch->last_activity_at?->diffForHumans() }}</p>
                </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── AREA CHAT ───────────────────────────────────────────────────────── --}}
    <div class="flex-1 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden">

        {{-- Summary panel --}}
        <template x-if="summaryResult">
            <div class="border-b border-gray-100 bg-indigo-50 p-4 flex-shrink-0 max-h-48 overflow-y-auto">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-5 h-5 bg-indigo-200 rounded flex items-center justify-center">
                            <svg class="w-3 h-3 text-indigo-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold text-indigo-700">Ringkasan Dokumen</span>
                    </div>
                    <button @click="summaryResult = null" class="text-indigo-400 hover:text-indigo-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-indigo-800 leading-relaxed" x-html="formatMessage(summaryResult)"></p>
            </div>
        </template>

        {{-- Chat header --}}
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 bg-indigo-400 rounded-full animate-pulse"></div>
                <span class="text-sm font-semibold text-gray-800" x-text="activeChatTitle || 'Tanya tentang dokumen ini'"></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-indigo-600 bg-indigo-50 border border-indigo-100 px-2 py-1 rounded-lg font-medium">
                    Konteks: {{ Str::limit($document->title, 30) }}
                </span>
                <button @click="newChat()"
                        class="text-xs text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors font-medium">
                    + Chat Baru
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-5 space-y-4" x-ref="messagesContainer">

            <template x-if="messages.length === 0 && !isTyping">
                <div class="text-center py-8">
                    <p class="text-sm text-gray-500 mb-4">Tanya apa saja tentang dokumen ini</p>
                    <div class="space-y-2 max-w-sm mx-auto">
                        @php
                        $suggestions = [
                            'Apa isi utama dokumen ini?',
                            'Jelaskan prosedur yang ada di dokumen ini',
                            'Apa saja poin penting yang perlu diperhatikan?',
                        ];
                        @endphp
                        @foreach($suggestions as $s)
                        <button @click="inputMessage = '{{ $s }}'; sendMessage()"
                                class="w-full text-left text-xs text-gray-600 bg-gray-50 hover:bg-indigo-50 hover:text-indigo-700 border border-gray-100 hover:border-indigo-200 rounded-xl px-4 py-2.5 transition-all">
                            {{ $s }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </template>

            <template x-for="msg in messages" :key="msg.id">
                <div class="flex gap-3" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                    <template x-if="msg.role === 'assistant'">
                        <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                    </template>

                    <div class="max-w-[75%]">
                        <div class="rounded-2xl px-4 py-3 text-sm leading-relaxed"
                             :class="msg.role === 'user'
                                 ? 'bg-indigo-600 text-white rounded-br-md'
                                 : 'bg-gray-50 text-gray-800 border border-gray-100 rounded-bl-md'">
                            <div x-html="formatMessage(msg.content)"></div>
                        </div>

                        <template x-if="msg.role === 'assistant' && msg.sources && msg.sources.length > 0">
                            <div class="mt-2 flex flex-wrap gap-1">
                                <template x-for="src in msg.sources" :key="src.document_id + '-' + src.page_number">
                                    <div class="inline-flex items-center gap-1 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs px-2.5 py-1 rounded-lg">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="'Hal. ' + (src.page_number || '?')"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <p class="text-xs text-gray-400 mt-1 px-1"
                           :class="msg.role === 'user' ? 'text-right' : 'text-left'"
                           x-text="formatTime(msg.created_at)"></p>
                    </div>

                    <template x-if="msg.role === 'user'">
                        <div class="w-7 h-7 bg-slate-700 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="isTyping">
                <div class="flex gap-3">
                    <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl rounded-bl-md px-4 py-3">
                        <div class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-100 p-4 flex-shrink-0">
            <div class="flex gap-3 items-end">
                <textarea x-model="inputMessage"
                          @keydown.enter.prevent="!$event.shiftKey && sendMessage()"
                          @input="autoResize($event)"
                          rows="1"
                          placeholder="Tanya tentang dokumen ini..."
                          :disabled="isTyping"
                          class="flex-1 resize-none bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-300 transition-all disabled:opacity-50 max-h-32"></textarea>
                <button @click="sendMessage()"
                        :disabled="!inputMessage.trim() || isTyping"
                        class="w-10 h-10 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-200 disabled:cursor-not-allowed text-white rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Modal Compare Versions ─────────────────────────────────────────── --}}
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
                        <select x-model="compareOldId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <option value="">Pilih versi...</option>
                            @foreach($document->versions as $v)
                            <option value="{{ $v->id }}">{{ $v->version_number }} — {{ $v->created_at->format('d M Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">Versi Baru</label>
                        <select x-model="compareNewId" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <option value="">Pilih versi...</option>
                            @foreach($document->versions as $v)
                            <option value="{{ $v->id }}">{{ $v->version_number }} — {{ $v->created_at->format('d M Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-5 border-t border-gray-100 flex justify-end gap-3">
                    <button @click="showCompareModal = false"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 transition-colors">Batal</button>
                    <button @click="compareVersions()"
                            :disabled="!compareOldId || !compareNewId || isComparing"
                            class="px-4 py-2 text-sm font-medium bg-blue-600 hover:bg-blue-700 disabled:bg-gray-200 text-white rounded-lg transition-colors">
                        <span x-text="isComparing ? 'Membandingkan...' : 'Bandingkan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ── Modal Hasil Compare ─────────────────────────────────────────────── --}}
    <template x-if="compareResult">
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="font-semibold text-gray-800">Hasil Perbandingan</h3>
                        <p class="text-xs text-gray-500 mt-0.5"
                           x-text="compareResult.old_version + ' → ' + compareResult.new_version"></p>
                    </div>
                    <button @click="compareResult = null" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto p-5">
                    <div class="prose prose-sm max-w-none text-gray-700 text-sm leading-relaxed"
                         x-html="formatMessage(compareResult.changes)"></div>
                </div>
            </div>
        </div>
    </template>

</div>

@push('scripts')
<script>
function documentAiChat() {
    const instance = {
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
            window.documentAiChatInstance = this;
            @if($chats->count() > 0)
            this.loadChat({{ $chats->first()->id }});
            @endif
        },

        async newChat() {
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
            }
        },

        async loadChat(chatId) {
            const res = await fetch(`/ai/chats/${chatId}`);
            const data = await res.json();
            this.activeChatId = chatId;
            this.activeChatTitle = data.chat.title;
            this.messages = data.messages;
            this.$nextTick(() => this.scrollToBottom());
        },

        async sendMessage() {
            const msg = this.inputMessage.trim();
            if (!msg || this.isTyping) return;

            if (!this.activeChatId) await this.newChat();

            this.messages.push({
                id: Date.now(), role: 'user', content: msg,
                sources: [], created_at: new Date().toISOString(),
            });
            this.inputMessage = '';
            this.isTyping = true;
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
                    content: 'Koneksi gagal.', sources: [],
                    created_at: new Date().toISOString(),
                });
            } finally {
                this.isTyping = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async summarize() {
            this.isSummarizing = true;
            try {
                const res = await fetch(`/ai/documents/${this.documentId}/summarize`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const data = await res.json();
                if (data.success) this.summaryResult = data.data.summary;
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
            el.style.height = Math.min(el.scrollHeight, 128) + 'px';
        },

        formatMessage(content) {
            if (!content) return '';
            return content
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/## (.+)/g, '<p class="font-semibold text-gray-800 mt-2 mb-1">$1</p>')
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/^- (.+)/gm, '<li class="ml-4 list-disc">$1</li>')
                .replace(/\n/g, '<br>');
        },

        formatTime(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        },
    };
    return instance;
}
</script>
@endpush
@endsection