@extends('layouts.app')
@section('title', 'Tambah Kategori')
@section('breadcrumb') <a href="{{ route('categories.index') }}">Kategori</a> / Tambah @endsection

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form action="{{ route('categories.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror"
                       placeholder="Contoh: Standard Operating Procedure">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prefix <span class="text-red-500">*</span></label>
                <input type="text" name="prefix" value="{{ old('prefix') }}" maxlength="10"
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase @error('prefix') border-red-400 @enderror"
                       placeholder="Contoh: SOP"
                       oninput="this.value = this.value.toUpperCase()">
                <p class="mt-1 text-xs text-gray-400">Digunakan sebagai awalan nomor dokumen, contoh: SOP-HR-001</p>
                @error('prefix') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Deskripsi kategori dokumen...">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                    Tambah Kategori
                </button>
                <a href="{{ route('categories.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
