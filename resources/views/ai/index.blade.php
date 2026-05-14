@extends('layouts.app')
@section('title', 'AI Assistant')

@section('breadcrumb')
    <span class="text-gray-400">AI</span>
    <span class="mx-1">›</span>
    <span>Assistant</span>
@endsection

@section('content')
<div class="flex gap-5 h-[calc(100vh-10rem)]" x-data="aiChat()" x-init="init()">

    {{-- ── SIDEBAR: Riwayat Chat ──────────────────────────────────────────── --}}
    <div class="w-72 flex-shrink-0 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden">

        {{-- Header sidebar --}}
        <div class="px-4 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-gray-800">AI Assistant</span>
            </div>
            <button @click="newChat()"
                    class="w-7 h-7 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center transition-colors"
                    title="Chat Baru">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </button>
        </div>

        {{-- Search --}}
        <div class="px-3 py-2 border-b border-gray-50">
            <input type="text" x-model="searchQuery" placeholder="Cari riwayat..."
                   class="w-full text-xs bg-gray-50 border-0 rounded-lg px-3 py-2 text-gray-600 placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-300">
        </div>

        {{-- Chat list --}}
        <div class="flex-1 overflow-y-auto py-2 px-2 space-y-0.5">
            <template x-if="filteredChats.length === 0">
                <div class="text-center py-8">
                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-xs text-gray-400">Belum ada riwayat chat</p>
                    <button @click="newChat()"
                            class="mt-2 text-xs text-blue-600 hover:underline">Mulai chat baru</button>
                </div>
            </template>

            <template x-for="chat in filteredChats" :key="chat.id">
                <div @click="loadChat(chat.id)"
                     class="group flex items-center gap-2 px-3 py-2.5 rounded-xl cursor-pointer transition-all"
                     :class="activeChatId === chat.id
                         ? 'bg-blue-50 border border-blue-100'
                         : 'hover:bg-gray-50 border border-transparent'">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium truncate"
                           :class="activeChatId === chat.id ? 'text-blue-700' : 'text-gray-700'"
                           x-text="chat.title"></p>
                        <p class="text-xs text-gray-400 truncate" x-text="formatDate(chat.last_activity_at)"></p>
                    </div>
                    <button @click.stop="deleteChat(chat.id)"
                            class="opacity-0 group-hover:opacity-100 w-5 h-5 text-gray-400 hover:text-red-500 transition-all flex-shrink-0">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Info footer --}}
        <div class="border-t border-gray-100 p-3">
            <p class="text-xs text-gray-400 text-center leading-relaxed">
                AI hanya menjawab berdasarkan<br>dokumen yang Anda miliki akses
            </p>
        </div>
    </div>

    {{-- ── AREA CHAT UTAMA ────────────────────────────────────────────────── --}}
    <div class="flex-1 bg-white rounded-2xl border border-gray-100 shadow-sm flex flex-col overflow-hidden">

        {{-- ── STATE: Belum ada chat aktif ── --}}
        <template x-if="!activeChatId && !isLoading">
            <div class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">AI Document Assistant</h3>
                <p class="text-sm text-gray-500 max-w-xs mb-6 leading-relaxed">
                    Tanya apa saja tentang dokumen perusahaan. AI akan menjawab berdasarkan dokumen yang Anda miliki akses.
                </p>

                {{-- Contoh pertanyaan --}}
                <div class="w-full max-w-md space-y-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Contoh pertanyaan</p>
                    @php
                    $examples = [
                        'Apa prosedur pengajuan cuti tahunan?',
                        'Bagaimana alur persetujuan pengadaan barang?',
                        'Apa saja persyaratan dokumen ISO yang berlaku?',
                        'Jelaskan kebijakan keselamatan kerja terbaru',
                    ];
                    @endphp
                    @foreach($examples as $ex)
                    <button @click="startWithQuestion('{{ $ex }}')"
                            class="w-full text-left text-sm text-gray-600 bg-gray-50 hover:bg-blue-50 hover:text-blue-700 border border-gray-100 hover:border-blue-200 rounded-xl px-4 py-2.5 transition-all">
                        {{ $ex }}
                    </button>
                    @endforeach
                </div>
            </div>
        </template>

        {{-- ── STATE: Loading ── --}}
        <template x-if="isLoading && !activeChatId">
            <div class="flex-1 flex items-center justify-center">
                <div class="flex items-center gap-3 text-gray-400">
                    <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <span class="text-sm">Memuat...</span>
                </div>
            </div>
        </template>

        {{-- ── STATE: Chat aktif ── --}}
        <template x-if="activeChatId">
            <div class="flex flex-col h-full">

                {{-- Chat header --}}
                <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse flex-shrink-0"></div>
                        <h4 class="text-sm font-semibold text-gray-800 truncate" x-text="activeChatTitle"></h4>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded-lg">Global</span>
                        <button @click="newChat()"
                                class="text-xs text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition-colors font-medium">
                            + Chat Baru
                        </button>
                    </div>
                </div>

                {{-- Messages area --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-4" x-ref="messagesContainer">

                    <template x-if="messages.length === 0">
                        <div class="text-center py-8 text-sm text-gray-400">
                            Mulai percakapan dengan mengetik pertanyaan di bawah
                        </div>
                    </template>

                    <template x-for="msg in messages" :key="msg.id">
                        <div class="flex gap-3" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

                            {{-- Avatar AI --}}
                            <template x-if="msg.role === 'assistant'">
                                <div class="w-7 h-7 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                            </template>

                            <div class="max-w-[75%]">
                                {{-- Bubble --}}
                                <div class="rounded-2xl px-4 py-3 text-sm leading-relaxed"
                                     :class="msg.role === 'user'
                                         ? 'bg-blue-600 text-white rounded-br-md'
                                         : 'bg-gray-50 text-gray-800 border border-gray-100 rounded-bl-md'">
                                    <div x-html="formatMessage(msg.content)"></div>
                                </div>

                                {{-- Sources / citations --}}
                                <template x-if="msg.role === 'assistant' && msg.sources && msg.sources.length > 0">
                                    <div class="mt-2 space-y-1">
                                        <p class="text-xs text-gray-400 font-medium">Sumber:</p>
                                        <template x-for="src in msg.sources" :key="src.document_id + '-' + src.page_number">
                                            <div class="inline-flex items-center gap-1.5 bg-blue-50 border border-blue-100 text-blue-700 text-xs px-2.5 py-1 rounded-lg mr-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <span x-text="src.document_title"></span>
                                                <template x-if="src.page_number">
                                                    <span class="text-blue-400" x-text="'· Hal. ' + src.page_number"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Timestamp --}}
                                <p class="text-xs text-gray-400 mt-1 px-1"
                                   :class="msg.role === 'user' ? 'text-right' : 'text-left'"
                                   x-text="formatTime(msg.created_at)"></p>
                            </div>

                            {{-- Avatar User --}}
                            <template x-if="msg.role === 'user'">
                                <div class="w-7 h-7 bg-slate-700 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5 text-white text-xs font-bold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Typing indicator --}}
                    <template x-if="isTyping">
                        <div class="flex gap-3 justify-start">
                            <div class="w-7 h-7 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center flex-shrink-0">
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

                {{-- Input area --}}
                <div class="border-t border-gray-100 p-4 flex-shrink-0">
                    <div class="flex gap-3 items-end">
                        <textarea x-model="inputMessage"
                                  @keydown.enter.prevent="!$event.shiftKey && sendMessage()"
                                  @input="autoResize($event)"
                                  rows="1"
                                  placeholder="Ketik pertanyaan Anda... (Enter kirim, Shift+Enter baris baru)"
                                  :disabled="isTyping"
                                  class="flex-1 resize-none bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300 transition-all disabled:opacity-50 max-h-32"></textarea>
                        <button @click="sendMessage()"
                                :disabled="!inputMessage.trim() || isTyping"
                                class="w-10 h-10 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-200 disabled:cursor-not-allowed text-white rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">
                        AI menjawab berdasarkan dokumen yang Anda miliki akses · Selalu verifikasi informasi penting
                    </p>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function aiChat() {
    return {
        // State
        chats: @json($chats),
        messages: [],
        activeChatId: null,
        activeChatTitle: '',
        inputMessage: '',
        isTyping: false,
        isLoading: false,
        searchQuery: '',

        // Computed
        get filteredChats() {
            if (!this.searchQuery) return this.chats;
            const q = this.searchQuery.toLowerCase();
            return this.chats.filter(c => c.title.toLowerCase().includes(q));
        },

        init() {
            // Load chat terakhir jika ada
            if (this.chats.length > 0) {
                this.loadChat(this.chats[0].id);
            }
        },

        async newChat() {
            this.isLoading = true;
            try {
                const res = await fetch('{{ route("ai.chats.create") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ title: 'Chat Baru' }),
                });
                const data = await res.json();
                if (data.success) {
                    this.chats.unshift({
                        id: data.chat.id,
                        title: data.chat.title,
                        mode: data.chat.mode,
                        last_activity_at: new Date().toISOString(),
                        document_id: null,
                    });
                    this.activeChatId = data.chat.id;
                    this.activeChatTitle = data.chat.title;
                    this.messages = [];
                }
            } finally {
                this.isLoading = false;
            }
        },

        async loadChat(chatId) {
            if (this.activeChatId === chatId) return;
            this.isLoading = true;
            try {
                const res = await fetch(`/ai/chats/${chatId}`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                });
                const data = await res.json();
                this.activeChatId = chatId;
                this.activeChatTitle = data.chat.title;
                this.messages = data.messages;
                this.$nextTick(() => this.scrollToBottom());
            } finally {
                this.isLoading = false;
            }
        },

        async sendMessage() {
            const msg = this.inputMessage.trim();
            if (!msg || this.isTyping) return;

            // Buat chat baru jika belum ada
            if (!this.activeChatId) {
                await this.newChat();
            }

            // Optimistic UI
            this.messages.push({
                id: Date.now(),
                role: 'user',
                content: msg,
                sources: [],
                created_at: new Date().toISOString(),
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
                    body: JSON.stringify({ message: msg }),
                });
                const data = await res.json();

                if (data.success) {
                    this.messages.push(data.message);
                    // Update judul jika chat baru
                    const chat = this.chats.find(c => c.id === this.activeChatId);
                    if (chat && chat.title === 'Chat Baru') {
                        chat.title = msg.substring(0, 50) + (msg.length > 50 ? '...' : '');
                        this.activeChatTitle = chat.title;
                    }
                } else {
                    this.messages.push({
                        id: Date.now(),
                        role: 'assistant',
                        content: data.message || 'Terjadi kesalahan. Silakan coba lagi.',
                        sources: [],
                        created_at: new Date().toISOString(),
                    });
                }
            } catch (e) {
                this.messages.push({
                    id: Date.now(),
                    role: 'assistant',
                    content: 'Koneksi gagal. Periksa koneksi internet Anda.',
                    sources: [],
                    created_at: new Date().toISOString(),
                });
            } finally {
                this.isTyping = false;
                this.$nextTick(() => this.scrollToBottom());
            }
        },

        async deleteChat(chatId) {
            if (!confirm('Hapus chat ini?')) return;
            await fetch(`/ai/chats/${chatId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            this.chats = this.chats.filter(c => c.id !== chatId);
            if (this.activeChatId === chatId) {
                this.activeChatId = null;
                this.messages = [];
                if (this.chats.length > 0) this.loadChat(this.chats[0].id);
            }
        },

        async startWithQuestion(question) {
            await this.newChat();
            this.inputMessage = question;
            await this.sendMessage();
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
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/## (.+)/g, '<p class="font-semibold text-gray-800 mt-2 mb-1">$1</p>')
                .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                .replace(/^- (.+)/gm, '<li class="ml-4 list-disc">$1</li>')
                .replace(/\n/g, '<br>');
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            const now = new Date();
            const diff = now - d;
            if (diff < 86400000) return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            if (diff < 604800000) {
                const days = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
                return days[d.getDay()];
            }
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
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