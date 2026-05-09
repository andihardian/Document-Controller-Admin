@extends('layouts.app')
@section('title', 'Daftar Dokumen')

@section('content')
<div class="space-y-5">

    {{-- Header & actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <p class="text-sm text-gray-500">{{ $documents->total() }} dokumen ditemukan</p>
        @role('admin|employee')
        <a href="{{ route('documents.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Upload Dokumen
        </a>
        @endrole
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari judul / nomor dokumen..."
                   class="flex-1 min-w-48 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="status" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                <option value="pending_approval" @selected(request('status') === 'pending_approval')>Pending Approval</option>
                <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
            </select>

            @role('admin|department_head')
            <select name="department" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Departemen</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>
            @endrole

            <select name="category" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                Filter
            </button>
            <a href="{{ route('documents.index') }}" class="text-sm text-gray-500 px-3 py-2 hover:text-gray-700">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Nomor / Judul</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Kategori</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Departemen</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Versi</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $doc->title }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $doc->document_number }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $doc->category->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $doc->department->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">v{{ $doc->current_version }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center text-xs font-medium px-2.5 py-1 rounded-full
                                @if($doc->status === 'approved') bg-green-100 text-green-700
                                @elseif($doc->status === 'pending_approval') bg-yellow-100 text-yellow-700
                                @elseif($doc->status === 'rejected') bg-red-100 text-red-700
                                @elseif($doc->status === 'expired') bg-orange-100 text-orange-700
                                @elseif($doc->status === 'obsolete') bg-purple-100 text-purple-700
                                @else bg-gray-100 text-gray-600
                                @endif">
                                {{ $doc->status_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $doc->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('documents.show', $doc) }}"
                                   class="text-xs text-blue-600 hover:underline">Detail</a>

                                @if($doc->status === 'approved')
                                <a href="{{ route('documents.download', $doc) }}"
                                   class="text-xs text-green-600 hover:underline">Download</a>
                                @endif

                                @if(in_array($doc->status, ['draft','rejected']) && (auth()->user()->isAdmin() || $doc->created_by === auth()->id()))
                                <a href="{{ route('documents.edit', $doc) }}"
                                   class="text-xs text-gray-500 hover:underline">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Tidak ada dokumen
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $documents->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
