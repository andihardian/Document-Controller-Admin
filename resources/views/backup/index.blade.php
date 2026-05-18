@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Backup & Restore</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola backup data dokumen dan audit log sistem</p>
        </div>
        <button onclick="openBackupModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 active:scale-95 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Backup Baru
        </button>
    </div>

    {{-- Stats Cards --}}
    @php
        $totalSize    = $backups ? array_sum(array_column($backups, 'size')) : 0;
        $latestBackup = $backups[0] ?? null;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex items-center gap-4">
            <div class="p-3 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 3 8 3s8-.79 8-3V7M4 7c0 2.21 3.582 3 8 3s8-2.79 8-3M4 7c0-2.21 3.582-3 8-3s8 .79 8 3"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Backup</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ count($backups) }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">backup tersedia</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex items-center gap-4">
            <div class="p-3 bg-emerald-100 dark:bg-emerald-900/40 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Ukuran</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    {{ \App\Http\Controllers\BackupController::formatBytesStatic($totalSize) }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500">total seluruh backup</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex items-center gap-4">
            <div class="p-3 bg-purple-100 dark:bg-purple-900/40 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Backup Terakhir</p>
                <p class="text-sm font-bold text-gray-800 dark:text-gray-100">
                    {{ $latestBackup ? $latestBackup['date']->format('d M Y H:i') : 'Belum ada' }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $latestBackup ? $latestBackup['date_human'] : '-' }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 flex items-center gap-4">
            <div class="p-3 bg-amber-100 dark:bg-amber-900/40 rounded-xl flex-shrink-0">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">Retention</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-100">30 Hari</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">otomatis hapus backup lama</p>
            </div>
        </div>
    </div>

    {{-- Main Card with Tabs --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

        {{-- Tab Header --}}
        <div class="border-b border-gray-200 dark:border-gray-700 px-6">
            <div class="flex gap-0">
                <button onclick="switchTab('backup')" id="tab-backup"
                    class="px-5 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 transition-all">
                    Backup
                </button>
                <button onclick="switchTab('restore')" id="tab-restore"
                    class="px-5 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all">
                    Restore
                </button>
            </div>
        </div>

        {{-- ─── TAB: BACKUP ─── --}}
        <div id="panel-backup">

            <div class="mx-6 mt-5 p-4 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl text-sm text-blue-700 dark:text-blue-300">
                <p class="font-semibold mb-1.5"> Jadwal Backup Otomatis</p>
                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs">
                    <span> Backup DB — Setiap hari 02:00</span>
                    <span> Backup Full — Setiap Minggu 01:00</span>
                    <span> Cleanup — Setiap hari 03:00</span>
                </div>
            </div>

            @if(count($backups) === 0)
                <div class="py-20 text-center text-gray-400">
                    <svg class="w-14 h-14 mx-auto mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Belum ada file backup</p>
                    <p class="text-xs mt-1 text-gray-400 dark:text-gray-500">Klik "Buat Backup Baru" untuk memulai</p>
                </div>
            @else
                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">Nama Backup</th>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3">Komponen</th>
                                <th class="px-6 py-3">Ukuran</th>
                                <th class="px-6 py-3">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($backups as $backup)
                                @php
                                    $isFull = $backup['type'] === 'full';
                                    $nameWithoutExt = pathinfo($backup['file'], PATHINFO_FILENAME);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0
                                                {{ $isFull ? 'bg-blue-100 dark:bg-blue-900/40' : 'bg-gray-100 dark:bg-gray-800' }}">
                                                <svg class="w-4 h-4 {{ $isFull ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 7v10c0 2.21 3.582 3 8 3s8-.79 8-3V7M4 7c0 2.21 3.582 3 8 3s8-2.79 8-3M4 7c0-2.21 3.582-3 8-3s8 .79 8 3"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-mono text-xs font-medium text-gray-800 dark:text-gray-200 truncate max-w-[200px]">
                                                    {{ $nameWithoutExt }}
                                                </p>
                                                <p class="text-xs text-gray-400 dark:text-gray-500">.zip</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($isFull)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Full</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">DB Only</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span class="text-xs">{{ $isFull ? 'Dokumen + DB' : 'Database' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $backup['size_human'] }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $backup['date']->format('d M Y, H:i') }}</p>
                                        <p class="text-xs text-gray-400">{{ $backup['date_human'] }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            {{-- Download --}}
                                            <form method="POST" action="{{ route('backup.download') }}">
                                                @csrf
                                                <input type="hidden" name="file" value="{{ $backup['file'] }}">
                                                <button type="submit"
                                                    class="p-1.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition" title="Download">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </button>
                                            </form>
                                            {{-- Restore --}}
                                            <button
                                                onclick="openRestoreModal('{{ $backup['file'] }}', '{{ $backup['date']->format('d M Y H:i') }}', '{{ $backup['size_human'] }}', '{{ $isFull ? 'Full Backup' : 'DB Only' }}')"
                                                class="p-1.5 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition" title="Restore">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                            {{-- Delete --}}
                                            <form method="POST" action="{{ route('backup.delete') }}"
                                                onsubmit="return confirm('Hapus backup ini? Tindakan tidak dapat dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="file" value="{{ $backup['file'] }}">
                                                <button type="submit"
                                                    class="p-1.5 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan {{ count($backups) }} backup
                </div>
            @endif
        </div>

        {{-- ─── TAB: RESTORE ─── --}}
        <div id="panel-restore" class="hidden">

            <div class="mx-6 mt-5 p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-xl text-sm text-amber-800 dark:text-amber-300">
                <p class="font-semibold mb-1">⚠️ Perhatian</p>
                <p class="text-xs">Proses restore akan menimpa data yang ada saat ini. Pastikan kamu memilih file backup yang benar sebelum melanjutkan.</p>
            </div>

            @if(count($backups) === 0)
                <div class="py-20 text-center text-gray-400">
                    <p class="text-sm">Tidak ada file backup tersedia untuk di-restore.</p>
                </div>
            @else
                <div class="overflow-x-auto mt-4">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr class="text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <th class="px-6 py-3">Nama Backup</th>
                                <th class="px-6 py-3">Jenis</th>
                                <th class="px-6 py-3">Ukuran</th>
                                <th class="px-6 py-3">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($backups as $backup)
                                @php
                                    $isFull = $backup['type'] === 'full';
                                    $nameWithoutExt = pathinfo($backup['file'], PATHINFO_FILENAME);
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 {{ $isFull ? 'bg-blue-100 dark:bg-blue-900/40' : 'bg-gray-100 dark:bg-gray-800' }} rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 {{ $isFull ? 'text-blue-600 dark:text-blue-400' : 'text-gray-500 dark:text-gray-400' }}"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 7v10c0 2.21 3.582 3 8 3s8-.79 8-3V7M4 7c0 2.21 3.582 3 8 3s8-2.79 8-3M4 7c0-2.21 3.582-3 8-3s8 .79 8 3"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="font-mono text-xs font-medium text-gray-800 dark:text-gray-200 truncate max-w-[220px]">{{ $nameWithoutExt }}</p>
                                                <p class="text-xs text-gray-400">.zip</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($isFull)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">Full</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">DB Only</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $backup['size_human'] }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $backup['date']->format('d M Y, H:i') }}</p>
                                        <p class="text-xs text-gray-400">{{ $backup['date_human'] }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            onclick="openRestoreModal('{{ $backup['file'] }}', '{{ $backup['date']->format('d M Y H:i') }}', '{{ $backup['size_human'] }}', '{{ $isFull ? 'Full Backup' : 'DB Only' }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Restore
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 text-xs text-gray-500 dark:text-gray-400">
                    {{ count($backups) }} backup tersedia
                </div>
            @endif
        </div>

    </div>{{-- end main card --}}

</div>
@endsection

{{-- ═══════════════════════════════════════
     MODAL: Buat Backup Baru
═══════════════════════════════════════ --}}
@push('modals')
<div id="modal-backup" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeBackupModal()"></div>
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Buat Backup Baru</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih data yang ingin dibackup</p>
            </div>
            <button onclick="closeBackupModal()" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Jenis Backup</p>
                <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="backup_type_modal" value="all" class="sr-only peer" checked>
                        <div class="peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 peer-checked:text-blue-700 dark:peer-checked:text-blue-300 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-3 text-center transition hover:border-blue-300 dark:hover:border-blue-600 text-gray-500 dark:text-gray-400">
                            <svg class="w-6 h-6 mx-auto mb-1.5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                            </svg>
                            <p class="text-xs font-semibold">Full Backup</p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">DB + Dokumen</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="backup_type_modal" value="db" class="sr-only peer">
                        <div class="peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 peer-checked:text-blue-700 dark:peer-checked:text-blue-300 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-3 text-center transition hover:border-blue-300 dark:hover:border-blue-600 text-gray-500 dark:text-gray-400">
                            <svg class="w-6 h-6 mx-auto mb-1.5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 3 8 3s8-.79 8-3V7M4 7c0 2.21 3.582 3 8 3s8-2.79 8-3M4 7c0-2.21 3.582-3 8-3s8 .79 8 3"/>
                            </svg>
                            <p class="text-xs font-semibold">Database</p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">Hanya DB</p>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="backup_type_modal" value="files" class="sr-only peer">
                        <div class="peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/30 peer-checked:text-blue-700 dark:peer-checked:text-blue-300 border-2 border-gray-200 dark:border-gray-700 rounded-xl p-3 text-center transition hover:border-blue-300 dark:hover:border-blue-600 text-gray-500 dark:text-gray-400">
                            <svg class="w-6 h-6 mx-auto mb-1.5 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                            <p class="text-xs font-semibold">File Saja</p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">Hanya dokumen</p>
                        </div>
                    </label>
                </div>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg text-xs text-gray-500 dark:text-gray-400">
                💡 Backup disimpan di local storage server. Proses mungkin membutuhkan beberapa menit tergantung ukuran data.
            </div>
        </div>
        <div class="p-6 pt-0 flex gap-3">
            <button onclick="closeBackupModal()"
                class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition">
                Batal
            </button>
            <button onclick="submitBackup()"
                class="flex-1 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition">
                Mulai Backup
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════
     MODAL: Restore Backup
═══════════════════════════════════════ --}}
<div id="modal-restore" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeRestoreModal()"></div>
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-gray-100">Restore Backup</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pilih backup yang akan di-restore</p>
            </div>
            <button onclick="closeRestoreModal()" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('backup.restore') }}" onsubmit="return confirmRestore()">
            @csrf
            <input type="hidden" name="file" id="restore-file">
            <div class="p-6 space-y-5">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Informasi Backup</p>
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 3 8 3s8-.79 8-3V7M4 7c0 2.21 3.582 3 8 3s8-2.79 8-3M4 7c0-2.21 3.582-3 8-3s8 .79 8 3"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-mono text-xs font-semibold text-gray-800 dark:text-gray-200 truncate" id="restore-filename">—</p>
                                    <span id="restore-type-badge" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"></span>
                                </div>
                                <div class="flex gap-4 mt-1.5 text-xs text-gray-500 dark:text-gray-400">
                                    <span id="restore-date">—</span>
                                    <span id="restore-size">—</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Komponen</p>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <input type="checkbox" name="restore_db" value="1" checked class="w-4 h-4 text-blue-600 rounded">
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Database</p>
                                    <p class="text-[10px] text-gray-400">Semua data & audit log</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <input type="checkbox" name="restore_files" value="1" class="w-4 h-4 text-blue-600 rounded">
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">File Dokumen</p>
                                    <p class="text-[10px] text-gray-400">Semua file PDF</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Mode Restore</p>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2.5 p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <input type="radio" name="mode" value="replace" checked class="w-4 h-4 text-blue-600">
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Replace (Timpa)</p>
                                    <p class="text-[10px] text-gray-400">Timpa data yang ada</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                <input type="radio" name="mode" value="merge" class="w-4 h-4 text-blue-600">
                                <div>
                                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Merge (Gabung)</p>
                                    <p class="text-[10px] text-gray-400">Gabung dengan data ada</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800 rounded-lg text-xs text-red-700 dark:text-red-300">
                    ⚠️ Proses restore tidak dapat dibatalkan. Pastikan kamu sudah membuat backup terbaru sebelum melanjutkan.
                </div>
            </div>
            <div class="p-6 pt-0 flex gap-3">
                <button type="button" onclick="closeRestoreModal()"
                    class="flex-1 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                    Mulai Restore
                </button>
            </div>
        </form>
    </div>
</div>
@endpush

{{-- Hidden form untuk submit backup --}}
@push('scripts')
<form id="form-backup" method="POST" action="{{ route('backup.run') }}" class="hidden">
    @csrf
    <input type="hidden" name="type" id="backup-type-input">
</form>

<script>
    function switchTab(tab) {
        const isBackup = tab === 'backup';
        document.getElementById('panel-backup').classList.toggle('hidden', !isBackup);
        document.getElementById('panel-restore').classList.toggle('hidden', isBackup);

        const active   = 'px-5 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600 dark:text-blue-400 transition-all';
        const inactive = 'px-5 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all';

        document.getElementById('tab-backup').className  = isBackup  ? active : inactive;
        document.getElementById('tab-restore').className = !isBackup ? active : inactive;
    }

    function openBackupModal()  { document.getElementById('modal-backup').classList.remove('hidden'); }
    function closeBackupModal() { document.getElementById('modal-backup').classList.add('hidden'); }

    function submitBackup() {
        const type = document.querySelector('input[name="backup_type_modal"]:checked')?.value || 'all';
        document.getElementById('backup-type-input').value = type;
        closeBackupModal();
        document.getElementById('form-backup').submit();
    }

    function openRestoreModal(file, date, size, type) {
        document.getElementById('restore-file').value            = file;
        document.getElementById('restore-filename').textContent  = file;
        document.getElementById('restore-date').textContent      = ' ' + date;
        document.getElementById('restore-size').textContent      = ' ' + size;
        document.getElementById('restore-type-badge').textContent = type;
        document.getElementById('modal-restore').classList.remove('hidden');
    }

    function closeRestoreModal() { document.getElementById('modal-restore').classList.add('hidden'); }

    function confirmRestore() {
        return confirm('Apakah kamu yakin ingin melakukan restore? Proses ini tidak dapat dibatalkan dan akan menimpa data yang ada.');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { closeBackupModal(); closeRestoreModal(); }
    });
</script>
@endpush