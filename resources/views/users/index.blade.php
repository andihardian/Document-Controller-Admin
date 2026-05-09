@extends('layouts.app')
@section('title', 'Manajemen User')

@section('content')
<div class="space-y-5">

    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $users->total() }} user terdaftar</p>
        <a href="{{ route('users.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah User
        </a>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama / email..."
                   class="flex-1 min-w-48 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="role" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ ucfirst(str_replace('_',' ',$role->name)) }}</option>
                @endforeach
            </select>

            <select name="department" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Departemen</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->id }}" @selected(request('department') == $dept->id)>{{ $dept->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">Filter</button>
            <a href="{{ route('users.index') }}" class="text-sm text-gray-500 px-3 py-2 hover:text-gray-700">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="text-left px-4 py-3 font-medium text-gray-600">User</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Role</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Departemen</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                            <div>
                                <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-xs bg-slate-100 text-slate-700 font-medium px-2.5 py-1 rounded-full">
                            {{ ucfirst(str_replace('_',' ', $user->getRoleNames()->first() ?? '-')) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->department->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="{{ route('users.edit', $user) }}" class="text-xs text-blue-600 hover:underline">Edit</a>

                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs text-gray-500 hover:underline">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>

                            <form action="{{ route('users.reset-password', $user) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" onclick="return confirm('Reset password user ini?')" class="text-xs text-orange-500 hover:underline">Reset PW</button>
                            </form>

                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus user ini?')" class="text-xs text-red-500 hover:underline">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">Tidak ada user</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
