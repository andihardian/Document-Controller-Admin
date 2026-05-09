{{-- resources/views/departments/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Manajemen Departemen')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $departments->total() }} departemen</p>
        <a href="{{ route('departments.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Departemen
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Kode</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Nama</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Deskripsi</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Users</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Dokumen</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($departments as $dept)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs bg-slate-100 text-slate-700 font-semibold px-2.5 py-1 rounded">{{ $dept->code }}</span>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $dept->name }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ Str::limit($dept->description, 50) ?? '-' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $dept->users_count }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $dept->documents_count }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $dept->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $dept->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="{{ route('departments.edit', $dept) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                            @if($dept->users_count === 0 && $dept->documents_count === 0)
                            <form action="{{ route('departments.destroy', $dept) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus departemen ini?')" class="text-xs text-red-500 hover:underline">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-400">Belum ada departemen</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($departments->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">{{ $departments->links() }}</div>
        @endif
    </div>
</div>
@endsection
