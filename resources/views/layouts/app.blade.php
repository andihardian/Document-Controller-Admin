<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'DCS') }} — @yield('title', 'Dashboard')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">

    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        <aside class="w-64 bg-slate-900 text-white flex flex-col fixed inset-y-0 left-0 z-40">
            {{-- Logo --}}
            <div class="h-16 flex items-center px-6 border-b border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-sm tracking-wide">Document Control</span>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('documents.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('documents.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Dokumen
                </a>

                @role('admin|department_head')
                <a href="{{ route('approvals.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('approvals.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Approval
                    @php
                        $pendingCount = \App\Models\Approval::where('status','pending')
                            ->when(auth()->user()->hasRole('department_head'), fn($q) => $q->where('approver_id', auth()->id()))
                            ->count();
                    @endphp
                    @if($pendingCount > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingCount }}</span>
                    @endif
                </a>
                @endrole

                @role('admin')
                <div class="pt-4 pb-1">
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Manajemen</p>
                </div>

                <a href="{{ route('users.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Users
                </a>

                <a href="{{ route('departments.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('departments.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Departemen
                </a>

                <a href="{{ route('categories.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('categories.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Kategori
                </a>

                <a href="{{ route('audit-logs.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                          {{ request()->routeIs('audit-logs.*') ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Audit Log
                </a>
                @endrole

            </nav>

            {{-- User info --}}
            <div class="border-t border-slate-700 p-4">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                         class="w-8 h-8 rounded-full object-cover">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->getRoleNames()->first() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-white transition-colors" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 ml-64 flex flex-col min-h-screen">

            {{-- Top bar --}}
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-30">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
                    @hasSection('breadcrumb')
                    <div class="text-xs text-gray-500 mt-0.5">@yield('breadcrumb')</div>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-gray-900">
                        {{ auth()->user()->name }}
                    </a>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-6">

                {{-- Flash messages --}}
                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        <p class="font-medium mb-1">Terdapat kesalahan:</p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
