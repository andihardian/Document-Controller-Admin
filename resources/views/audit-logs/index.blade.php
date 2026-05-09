@extends('layouts.app')
@section('title', 'Audit Log')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="user_id" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua User</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>

            <select name="module" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Modul</option>
                @foreach($modules as $mod)
                <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ ucfirst($mod) }}</option>
                @endforeach
            </select>

            <select name="action" class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Aksi</option>
                @foreach($actions as $act)
                <option value="{{ $act }}" @selected(request('action') === $act)>{{ ucfirst($act) }}</option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit" class="bg-gray-800 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">Filter</button>
            <a href="{{ route('audit-logs.index') }}" class="text-sm text-gray-500 px-3 py-2 hover:text-gray-700">Reset</a>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <p class="text-sm text-gray-500">{{ $logs->total() }} log ditemukan</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Waktu</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">User</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Aksi</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Modul</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Deskripsi</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                            {{ $log->created_at->format('d M Y') }}<br>
                            <span class="font-mono">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-gray-700 text-xs font-medium">{{ $log->user->name ?? 'System' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $actionColors = [
                                    'create'  => 'bg-green-100 text-green-700',
                                    'update'  => 'bg-blue-100 text-blue-700',
                                    'delete'  => 'bg-red-100 text-red-700',
                                    'approve' => 'bg-emerald-100 text-emerald-700',
                                    'reject'  => 'bg-red-100 text-red-700',
                                    'upload'  => 'bg-indigo-100 text-indigo-700',
                                    'download'=> 'bg-gray-100 text-gray-700',
                                    'submit'  => 'bg-yellow-100 text-yellow-700',
                                    'login'   => 'bg-slate-100 text-slate-700',
                                ];
                                $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600';
                            @endphp
                            <span class="text-xs font-medium px-2 py-0.5 rounded {{ $colorClass }}">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $log->module }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs font-mono">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">Tidak ada log</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $logs->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
