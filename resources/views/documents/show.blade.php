@extends('layouts.app')
@section('title', $document->title)
@section('breadcrumb') <a href="{{ route('documents.index') }}">Dokumen</a> / {{ $document->document_number }} @endsection

@section('content')
@php
    $currentVersion = $document->currentVersion;
    $pdfUrl = $currentVersion ? asset('storage/' . $currentVersion->file_path) : null;
    $embStatus = $currentVersion?->embedding_status ?? 'pending';
    $canAI = $document->status === 'approved' && $document->allow_ai_access;
@endphp

<div class="flex gap-4 items-start" x-data="docPage()" x-init="init()">

    {{-- ── KOLOM KIRI: Info + PDF Viewer ─────────────────────────────────── --}}
    <div class="flex-1 min-w-0 space-y-4">

        {{-- Header card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded">{{ $document->document_number }}</span>
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full
                            @if($document->status === 'approved') bg-green-100 text-green-700
                            @elseif($document->status === 'pending_approval') bg-yellow-100 text-yellow-700
                            @elseif($document->status === 'rejected') bg-red-100 text-red-700
                            @elseif($document->status === 'expired') bg-orange-100 text-orange-700
                            @elseif($document->status === 'obsolete') bg-purple-100 text-purple-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ $document->status_label }}
                        </span>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-800 truncate">{{ $document->title }}</h2>
                    @if($document->description)
                    <p class="text-sm text-gray-400 mt-0.5">{{ $document->description }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Tombol AI --}}
                    @if($canAI)
                    <button @click="toggleAI()"
                            class="inline-flex items-center gap-1.5 text-sm font-medium px-3.5 py-2 rounded-lg transition-all"
                            :class="showAI ? 'bg-indigo-100 text-indigo-700' : 'bg-indigo-600 text-white hover:bg-indigo-700'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span x-text="showAI ? 'Tutup AI' : 'Tanya AI'"></span>
                    </button>
                    @endif

                    @if($document->status === 'approved')
                    <a href="{{ route('documents.download', $document) }}"
                       class="inline-flex items-center gap-1.5 bg-green-600 text-white text-sm font-medium px-3.5 py-2 rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download
                    </a>
                    @endif

                    @if(in_array($document->status, ['draft','rejected']) && (auth()->user()->isAdmin() || $document->created_by === auth()->id()))
                    <a href="{{ route('documents.edit', $document) }}"
                       class="inline-flex items-center gap-1.5 border border-gray-300 text-gray-700 text-sm font-medium px-3.5 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                        Edit
                    </a>
                    <form action="{{ route('documents.submit', $document) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-sm font-medium px-3.5 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Submit Approval
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Meta info --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-100">
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

        {{-- PDF Viewer --}}
        @if($pdfUrl)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z"/>
                        <path d="M14 2v6h6M9 15h6M9 11h6M9 19h4"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-700">{{ $document->document_number }}_v{{ $document->current_version }}.pdf</span>
                </div>
                <a href="{{ $pdfUrl }}" target="_blank"
                   class="text-xs text-blue-600 hover:underline flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Buka di tab baru
                </a>
            </div>
            <iframe src="{{ $pdfUrl }}"
                    class="w-full"
                    style="height: 70vh;"
                    title="{{ $document->title }}">
                <p class="p-4 text-sm text-gray-500">
                    Browser Anda tidak mendukung tampilan PDF.
                    <a href="{{ $pdfUrl }}" class="text-blue-600 hover:underline">Klik di sini untuk membuka PDF.</a>
                </p>
            </iframe>
        </div>
        @endif

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
                            @else bg-gray-100 text-gray-600 @endif">
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
                        @else bg-yellow-100 text-yellow-700 @endif">
                        {{ ucfirst($approval->status) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>{{-- end kolom kiri --}}

    {{-- ── SIDEBAR AI (kanan) ─────────────────────────────────────────────── --}}
    @if($canAI)
    <div x-show="showAI"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-4"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4"
         class="w-80 flex-shrink-0 bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col sticky top-4"
         style="height: calc(100vh - 7rem);">

        {{-- Header --}}
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-800">AI Assistant</p>
                    <p class="text-xs text-gray-400 truncate max-w-[170px]">{{ Str::limit($document->title, 28) }}</p>
                </div>
            </div>
            <button @click="showAI = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- AI not ready warning --}}
        @if($embStatus !== 'done')
        <div class="px-4 py-2 bg-amber-50 border-b border-amber-100 flex items-center gap-2 flex-shrink-0">
            <svg class="w-3.5 h-3.5 text-amber-500 animate-spin flex-shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <p class="text-xs text-amber-700">Dokumen sedang diproses untuk AI...</p>
        </div>
        @endif

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-3 space-y-3" x-ref="msgs">
            <template x-if="messages.length === 0 && !typing">
                <div class="space-y-1.5 pt-1">
                    <p class="text-xs text-gray-400 text-center mb-2">Saran pertanyaan:</p>
                    @foreach(['Apa isi utama dokumen ini?', 'Jelaskan prosedur yang ada', 'Poin penting yang perlu diperhatikan?'] as $s)
                    <button @click="input = '{{ $s }}'; send()"
                            class="w-full text-left text-xs text-gray-600 bg-gray-50 hover:bg-indigo-50 hover:text-indigo-700 border border-gray-100 hover:border-indigo-200 rounded-lg px-3 py-2 transition-all">
                        {{ $s }}
                    </button>
                    @endforeach
                </div>
            </template>

            <template x-for="m in messages" :key="m.id">
                <div class="flex gap-2" :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
                    <template x-if="m.role === 'assistant'">
                        <div class="w-5 h-5 bg-indigo-600 rounded-md flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                        </div>
                    </template>

                    <div class="max-w-[85%]">
                        <div class="rounded-xl px-3 py-2 text-xs leading-relaxed"
                             :class="m.role === 'user'
                                 ? 'bg-indigo-600 text-white rounded-br-sm'
                                 : 'bg-gray-50 text-gray-800 border border-gray-100 rounded-bl-sm'">
                            <div x-html="fmt(m.content)"></div>
                        </div>

                        <template x-if="m.role === 'assistant' && m.sources && m.sources.length > 0">
                            <div class="mt-1 flex flex-wrap gap-1">
                                <template x-for="s in m.sources" :key="s.document_id + '-' + s.page_number">
                                    <span class="inline-flex items-center gap-1 bg-indigo-50 border border-indigo-100 text-indigo-600 text-xs px-1.5 py-0.5 rounded">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="'Hal. ' + (s.page_number || '?')"></span>
                                    </span>
                                </template>
                            </div>
                        </template>
                        <p class="text-xs text-gray-300 mt-0.5 px-1"
                           :class="m.role === 'user' ? 'text-right' : ''"
                           x-text="fmtTime(m.created_at)"></p>
                    </div>

                    <template x-if="m.role === 'user'">
                        <div class="w-5 h-5 bg-slate-600 rounded-md flex items-center justify-center flex-shrink-0 mt-0.5 text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="typing">
                <div class="flex gap-2">
                    <div class="w-5 h-5 bg-indigo-600 rounded-md flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <div class="bg-gray-50 border border-gray-100 rounded-xl rounded-bl-sm px-3 py-2">
                        <div class="flex gap-1 items-center">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Input --}}
        <div class="border-t border-gray-100 p-3 flex-shrink-0">
            <div class="flex gap-2 items-end">
                <textarea x-model="input"
                          @keydown.enter.prevent="!$event.shiftKey && send()"
                          @input="autoResize($event)"
                          rows="1"
                          placeholder="Tanya tentang dokumen ini..."
                          :disabled="typing || '{{ $embStatus }}' !== 'done'"
                          class="flex-1 resize-none bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-300 transition-all disabled:opacity-50 max-h-20"></textarea>
                <button @click="send()"
                        :disabled="!input.trim() || typing || '{{ $embStatus }}' !== 'done'"
                        class="w-8 h-8 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-200 disabled:cursor-not-allowed text-white rounded-lg flex items-center justify-center transition-colors flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-1.5 text-center">Verifikasi jawaban dengan dokumen di kiri</p>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
function docPage() {
    return {
        showAI: {{ request('ai') === '1' ? 'true' : 'false' }},
        chatId: null,
        messages: [],
        input: '',
        typing: false,
        docId: {{ $document->id }},

        init() {
            if (this.showAI) this.$nextTick(() => this.startChat());
        },

        toggleAI() {
            this.showAI = !this.showAI;
            if (this.showAI && !this.chatId) this.$nextTick(() => this.startChat());
        },

        async startChat() {
            const res = await fetch('{{ route("ai.chats.create") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ document_id: this.docId }),
            });
            const data = await res.json();
            if (data.success) { this.chatId = data.chat.id; this.messages = []; }
        },

        async send() {
            const msg = this.input.trim();
            if (!msg || this.typing) return;
            if (!this.chatId) await this.startChat();

            this.messages.push({ id: Date.now(), role: 'user', content: msg, sources: [], created_at: new Date().toISOString() });
            this.input = '';
            this.typing = true;
            this.$nextTick(() => this.scroll());

            try {
                const res = await fetch(`/ai/chats/${this.chatId}/messages`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ message: msg, document_id: this.docId }),
                });
                const data = await res.json();
                this.messages.push(data.success ? data.message : {
                    id: Date.now(), role: 'assistant',
                    content: data.message || 'Terjadi kesalahan.',
                    sources: [], created_at: new Date().toISOString(),
                });
            } catch {
                this.messages.push({ id: Date.now(), role: 'assistant', content: 'Koneksi gagal.', sources: [], created_at: new Date().toISOString() });
            } finally {
                this.typing = false;
                this.$nextTick(() => this.scroll());
            }
        },

        scroll() {
            const el = this.$refs.msgs;
            if (el) el.scrollTop = el.scrollHeight;
        },

        autoResize(e) {
            e.target.style.height = 'auto';
            e.target.style.height = Math.min(e.target.scrollHeight, 80) + 'px';
        },

        fmt(t) {
            if (!t) return '';
            return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                    .replace(/## (.+)/g,'<p class="font-semibold mt-2 mb-1">$1</p>')
                    .replace(/\*\*(.+?)\*\*/g,'<strong>$1</strong>')
                    .replace(/^- (.+)/gm,'<li class="ml-3 list-disc">$1</li>')
                    .replace(/\n/g,'<br>');
        },

        fmtTime(d) {
            if (!d) return '';
            return new Date(d).toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
        },
    };
}
</script>
@endpush