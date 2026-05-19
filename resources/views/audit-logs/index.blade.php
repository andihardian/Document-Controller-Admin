@extends('layouts.app')
@section('title', 'Audit Log')

@section('content')
<div class="space-y-5">

    {{-- Filter --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="user_id" class="text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua User</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" @selected(request('user_id') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>

            <select name="module" class="text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Modul</option>
                @foreach($modules as $mod)
                <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ ucfirst($mod) }}</option>
                @endforeach
            </select>

            <select name="action" class="text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Aksi</option>
                @foreach($actions as $act)
                <option value="{{ $act }}" @selected(request('action') === $act)>{{ ucfirst($act) }}</option>
                @endforeach
            </select>

            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500">

            <button type="submit" class="bg-gray-800 dark:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 dark:hover:bg-gray-600 transition-colors">Filter</button>
            <a href="{{ route('audit-logs.index') }}" class="text-sm text-gray-500 dark:text-gray-400 px-3 py-2 hover:text-gray-700 dark:hover:text-gray-200">Reset</a>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $logs->total() }} log ditemukan</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Waktu</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">User</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Aksi</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Modul</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Deskripsi</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs whitespace-nowrap">
                            {{ $log->created_at->format('d M Y') }}<br>
                            <span class="font-mono">{{ $log->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-gray-700 dark:text-gray-300 text-xs font-medium">{{ $log->user->name ?? 'System' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $actionColors = [
                                    'create'   => 'bg-green-100 dark:bg-green-950/60 text-green-700 dark:text-green-400',
                                    'update'   => 'bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400',
                                    'delete'   => 'bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400',
                                    'approve'  => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400',
                                    'reject'   => 'bg-red-100 dark:bg-red-950/60 text-red-700 dark:text-red-400',
                                    'upload'   => 'bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400',
                                    'download' => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300',
                                    'submit'   => 'bg-yellow-100 dark:bg-yellow-950/60 text-yellow-700 dark:text-yellow-400',
                                    'login'    => 'bg-slate-100 dark:bg-gray-800 text-slate-700 dark:text-slate-300',
                                ];
                                $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400';
                            @endphp
                            <span class="text-xs font-medium px-2 py-0.5 rounded {{ $colorClass }}">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $log->module }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-gray-400 dark:text-gray-500 text-xs font-mono">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-gray-500">Tidak ada log</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
