{{-- resources/views/categories/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Kategori Dokumen')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $categories->total() }} kategori</p>
        <a href="{{ route('categories.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kategori
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700">
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Prefix</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Nama</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Deskripsi</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Dokumen</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($categories as $cat)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 font-semibold px-2.5 py-1 rounded border border-blue-200 dark:border-blue-900">{{ $cat->prefix }}</span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-100">{{ $cat->name }}</td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ Str::limit($cat->description, 60) ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $cat->documents_count }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $cat->is_active ? 'bg-green-100 dark:bg-green-950/60 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                            {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="{{ route('categories.edit', $cat) }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                            @if($cat->documents_count === 0)
                            <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus kategori ini?')" class="text-xs text-red-500 dark:text-red-400 hover:underline">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">Belum ada kategori</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($categories->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $categories->links() }}</div>
        @endif
    </div>
</div>
@endsection
