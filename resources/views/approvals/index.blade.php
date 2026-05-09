@extends('layouts.app')
@section('title', 'Daftar Approval')

@section('content')
<div class="space-y-5">
    <p class="text-sm text-gray-500">{{ $pendingApprovals->total() }} dokumen menunggu persetujuan Anda</p>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Dokumen</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Departemen</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Versi</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Diupload oleh</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tanggal Submit</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($pendingApprovals as $approval)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $approval->documentVersion->document->title }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $approval->documentVersion->document->document_number }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $approval->documentVersion->document->department->name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs bg-gray-100 px-2 py-0.5 rounded">v{{ $approval->documentVersion->version_number }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $approval->documentVersion->uploader->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $approval->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('approvals.show', $approval->documentVersion) }}"
                               class="inline-flex items-center gap-1.5 bg-blue-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-blue-700 transition-colors">
                                Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            Tidak ada dokumen yang menunggu approval
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendingApprovals->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $pendingApprovals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
