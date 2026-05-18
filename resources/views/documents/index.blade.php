@extends('layouts.app')
@section('title', 'Dokumen')

@section('content')
<div class="space-y-6" x-data="docIndex()">

    {{-- ── CATEGORY CARDS ──────────────────────────────────── --}}
    @php
        use App\Models\DocumentCategory;
        $allCategories = DocumentCategory::withCount(['documents' => function($q) {
            $user = auth()->user();
            if ($user->hasRole('employee'))        $q->where('department_id', $user->department_id);
            if ($user->hasRole('department_head')) $q->where('department_id', $user->department_id);
        }])->where('is_active', true)->get();

        $categoryIcons = [
            'SOP' => ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'color' => 'blue'],
            'WI'  => ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'color' => 'indigo'],
            'FRM' => ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'purple'],
            'POL' => ['icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3', 'color' => 'cyan'],
        ];
        $colorMap = [
            'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-500',   'ring' => 'hover:ring-blue-200',  'badge' => 'bg-blue-100 text-blue-700'],
            'indigo' => ['bg' => 'bg-indigo-50',  'icon' => 'text-indigo-500', 'ring' => 'hover:ring-indigo-200','badge' => 'bg-indigo-100 text-indigo-700'],
            'purple' => ['bg' => 'bg-purple-50',  'icon' => 'text-purple-500', 'ring' => 'hover:ring-purple-200','badge' => 'bg-purple-100 text-purple-700'],
            'cyan'   => ['bg' => 'bg-cyan-50',    'icon' => 'text-cyan-500',   'ring' => 'hover:ring-cyan-200',  'badge' => 'bg-cyan-100 text-cyan-700'],
            'slate'  => ['bg' => 'bg-slate-50',   'icon' => 'text-slate-500',  'ring' => 'hover:ring-slate-200', 'badge' => 'bg-slate-100 text-slate-700'],
        ];
        $activeCategory = request('category');
    @endphp

    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Kategori Dokumen</h3>
            <a href="{{ route('documents.index') }}" class="text-xs text-gray-400 hover:text-gray-600">
                {{ $activeCategory ? 'Tampilkan semua' : '' }}
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
            <a href="{{ route('documents.index', array_merge(request()->except('category','page'), [])) }}"
               class="group flex flex-col items-center text-center p-4 bg-white border-2 rounded-2xl transition-all shadow-sm hover:shadow-md
                      {{ !$activeCategory ? 'border-blue-500 shadow-blue-100' : 'border-gray-100 hover:border-gray-200' }}">
                <div class="w-12 h-12 rounded-xl {{ !$activeCategory ? 'bg-blue-600' : 'bg-gray-100 group-hover:bg-gray-200' }} flex items-center justify-center mb-3 transition-colors">
                    <svg class="w-6 h-6 {{ !$activeCategory ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-gray-700">Semua</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $documents->total() }} dok</p>
            </a>

            @foreach($allCategories as $cat)
            @php
                $prefix = strtoupper($cat->prefix);
                $ci = $categoryIcons[$prefix] ?? ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'slate'];
                $cm = $colorMap[$ci['color']];
                $isActive = $activeCategory == $cat->id;
            @endphp
            <a href="{{ route('documents.index', array_merge(request()->except('category','page'), ['category' => $cat->id])) }}"
               class="group flex flex-col items-center text-center p-4 bg-white border-2 rounded-2xl transition-all shadow-sm hover:shadow-md
                      {{ $isActive ? 'border-blue-500 shadow-blue-100' : 'border-gray-100 hover:border-gray-200' }}">
                <div class="w-12 h-12 rounded-xl {{ $isActive ? 'bg-blue-600' : $cm['bg'].' group-hover:opacity-80' }} flex items-center justify-center mb-3 transition-all">
                    <svg class="w-6 h-6 {{ $isActive ? 'text-white' : $cm['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ci['icon'] }}"/>
                    </svg>
                </div>
                <p class="text-xs font-semibold text-gray-700 leading-tight">{{ $cat->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $cat->documents_count }} dok</p>
            </a>
            @endforeach
        </div>
    </div>

    {{-- ── HEADER & BUTTONS ─────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <p class="text-sm font-medium text-gray-700">
                @if($activeCategory)
                    Menampilkan dokumen kategori: <span class="text-blue-600">{{ $categories->firstWhere('id', $activeCategory)?->name ?? '' }}</span>
                @else
                    Semua Dokumen
                @endif
            </p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $documents->total() }} dokumen ditemukan</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Hapus Semua (admin only, hanya muncul jika ada dokumen terpilih) --}}
            @role('admin')
            <button x-show="selected.length > 0"
                    x-cloak
                    @click="confirmDeleteSelected()"
                    class="inline-flex items-center gap-2 bg-red-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-red-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Terpilih (<span x-text="selected.length"></span>)
            </button>
            @endrole

            @role('admin|employee')
            <a href="{{ route('documents.create') }}"
               class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-blue-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Dokumen
            </a>
            @endrole
        </div>
    </div>

    {{-- ── FILTER BAR ──────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            @if($activeCategory)
            <input type="hidden" name="category" value="{{ $activeCategory }}">
            @endif

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari judul / nomor dokumen..."
                   class="flex-1 min-w-48 text-sm border border-gray-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">

            <select name="status" class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Semua Status</option>
                <option value="draft"            @selected(request('status') === 'draft')>Draft</option>
                <option value="pending_approval" @selected(request('status') === 'pending_approval')>Pending Approval</option>
                <option value="approved"         @selected(request('status') === 'approved')>Approved</option>
                <option value="rejected"         @selected(request('status') === 'rejected')>Rejected</option>
            </select>

            @role('admin|department_head')
            <select name="department" class="text-sm border border-gray-200 rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <option value="">Semua Departemen</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
            @endrole

            <button type="submit"
                    class="bg-slate-800 text-white text-sm font-medium px-5 py-2.5 rounded-xl hover:bg-slate-700 transition-colors">
                Filter
            </button>
            <a href="{{ route('documents.index') }}"
               class="text-sm text-gray-400 hover:text-gray-600 px-3 py-2.5 transition-colors">Reset</a>
        </form>
    </div>

    {{-- ── DOCUMENT TABLE ───────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-100">
                        {{-- Checkbox select all (admin only) --}}
                        @role('admin')
                        <th class="px-4 py-3.5 w-10">
                            <input type="checkbox"
                                   @change="toggleAll($event)"
                                   :checked="selected.length === pageIds.length && pageIds.length > 0"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        @endrole
                        <th class="text-left px-5 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Dokumen</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Kategori</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Departemen</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Versi</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Status</th>
                        <th class="text-left px-4 py-3.5 font-semibold text-gray-500 text-xs uppercase tracking-wide">Tanggal</th>
                        <th class="px-4 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-blue-50/30 transition-colors" :class="selected.includes({{ $doc->id }}) ? 'bg-red-50/40' : ''">
                        {{-- Checkbox (admin only) --}}
                        @role('admin')
                        <td class="px-4 py-4">
                            <input type="checkbox"
                                   value="{{ $doc->id }}"
                                   @change="toggleOne({{ $doc->id }})"
                                   :checked="selected.includes({{ $doc->id }})"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                        @endrole

                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 leading-snug">{{ $doc->title }}</p>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $doc->document_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <span class="text-xs font-medium bg-slate-100 text-slate-600 px-2 py-0.5 rounded-md font-mono">
                                {{ $doc->category->prefix ?? '-' }}
                            </span>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $doc->category->name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600">{{ $doc->department->name ?? '-' }}</td>
                        <td class="px-4 py-4">
                            <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-lg">v{{ $doc->current_version }}</span>
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full
                                @if($doc->status === 'approved')         bg-emerald-100 text-emerald-700
                                @elseif($doc->status === 'pending_approval') bg-amber-100 text-amber-700
                                @elseif($doc->status === 'rejected')     bg-red-100 text-red-700
                                @elseif($doc->status === 'expired')      bg-orange-100 text-orange-700
                                @elseif($doc->status === 'obsolete')     bg-purple-100 text-purple-700
                                @else bg-gray-100 text-gray-500 @endif">
                                <span class="w-1.5 h-1.5 rounded-full
                                    @if($doc->status === 'approved')         bg-emerald-500
                                    @elseif($doc->status === 'pending_approval') bg-amber-500
                                    @elseif($doc->status === 'rejected')     bg-red-500
                                    @elseif($doc->status === 'expired')      bg-orange-500
                                    @elseif($doc->status === 'obsolete')     bg-purple-500
                                    @else bg-gray-400 @endif"></span>
                                {{ $doc->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-xs text-gray-400">{{ $doc->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1.5 justify-end">
                                <a href="{{ route('documents.show', $doc) }}"
                                   class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 font-medium px-3 py-1.5 rounded-lg transition-colors">
                                    Detail
                                </a>
                                @if($doc->status === 'approved')
                                <a href="{{ route('documents.download', $doc) }}"
                                   class="text-xs bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-medium px-3 py-1.5 rounded-lg transition-colors">
                                    Download
                                </a>
                                @endif
                                @if(in_array($doc->status, ['draft','rejected']) && (auth()->user()->isAdmin() || $doc->created_by === auth()->id()))
                                <a href="{{ route('documents.edit', $doc) }}"
                                   class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium px-3 py-1.5 rounded-lg transition-colors">
                                    Edit
                                </a>
                                @endif

                                {{-- Tombol Hapus (admin only, per baris) --}}
                                @role('admin')
                                <button @click="confirmDelete({{ $doc->id }}, '{{ addslashes($doc->title) }}')"
                                        class="text-xs bg-red-50 hover:bg-red-100 text-red-600 font-medium px-3 py-1.5 rounded-lg transition-colors">
                                    Hapus
                                </button>
                                @endrole
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Tidak ada dokumen ditemukan</p>
                            <p class="text-xs text-gray-400 mt-1">Coba ubah filter atau pilih kategori lain</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $documents->withQueryString()->links() }}
        </div>
        @endif
    </div>

    {{-- ── MODAL KONFIRMASI HAPUS ───────────────────────────── --}}
    @role('admin')
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="showModal = false"></div>

        {{-- Modal box --}}
        <div class="relative bg-white rounded-2xl shadow-xl p-6 w-full max-w-md"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-start gap-4">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-800" x-text="modalTitle"></h3>
                    <p class="text-sm text-gray-500 mt-1" x-html="modalDesc"></p>
                    <p class="text-xs text-red-600 mt-2 font-medium">⚠ Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            <div class="flex gap-3 mt-6 justify-end">
                <button @click="showModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button @click="executeDelete()"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    {{-- Hidden forms untuk delete --}}
    <form id="deleteOneForm" method="POST" style="display:none">
        @csrf
        @method('DELETE')
    </form>

    <form id="deleteSelectedForm" action="{{ route('documents.bulk-delete') }}" method="POST" style="display:none">
        @csrf
        @method('DELETE')
        <div id="bulkDeleteInputs"></div>
    </form>
    @endrole

</div>
@endsection

@push('scripts')
<script>
function docIndex() {
    return {
        selected: [],
        pageIds: @json($documents->pluck('id')->toArray()),
        showModal: false,
        modalTitle: '',
        modalDesc: '',
        deleteAction: null, // 'one' atau 'selected'
        deleteId: null,

        toggleAll(e) {
            this.selected = e.target.checked ? [...this.pageIds] : [];
        },

        toggleOne(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(i => i !== id);
            } else {
                this.selected.push(id);
            }
        },

        confirmDelete(id, title) {
            this.deleteAction = 'one';
            this.deleteId = id;
            this.modalTitle = 'Hapus Dokumen?';
            this.modalDesc = `Dokumen <strong>"${title}"</strong> beserta semua versi dan data AI-nya akan dihapus permanen.`;
            this.showModal = true;
        },

        confirmDeleteSelected() {
            this.deleteAction = 'selected';
            this.modalTitle = `Hapus ${this.selected.length} Dokumen?`;
            this.modalDesc = `<strong>${this.selected.length} dokumen</strong> yang dipilih beserta semua versi dan data AI-nya akan dihapus permanen.`;
            this.showModal = true;
        },

        executeDelete() {
            this.showModal = false;
            if (this.deleteAction === 'one') {
                const form = document.getElementById('deleteOneForm');
                form.action = `/documents/${this.deleteId}/force-delete`;
                form.submit();
            } else {
                const container = document.getElementById('bulkDeleteInputs');
                container.innerHTML = '';
                this.selected.forEach(id => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'ids[]';
                    inp.value = id;
                    container.appendChild(inp);
                });
                document.getElementById('deleteSelectedForm').submit();
            }
        },
    };
}
</script>
@endpush