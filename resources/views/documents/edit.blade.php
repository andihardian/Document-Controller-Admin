@extends('layouts.app')
@section('title', 'Edit Dokumen')
@section('breadcrumb') <a href="{{ route('documents.index') }}">Dokumen</a> / <a href="{{ route('documents.show', $document) }}">{{ $document->document_number }}</a> / Edit @endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form action="{{ route('documents.update', $document) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Dokumen</label>
                <input type="text" value="{{ $document->document_number }}" disabled
                       class="w-full text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2.5 text-gray-400 font-mono cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title', $document->title) }}"
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-400 @enderror">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $document->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Efektif</label>
                    <input type="date" name="effective_date" value="{{ old('effective_date', $document->effective_date?->format('Y-m-d')) }}"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kadaluarsa</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', $document->expiry_date?->format('Y-m-d')) }}"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('documents.show', $document) }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
