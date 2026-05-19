@extends('layouts.app')
@section('title', 'Edit Departemen')
@section('breadcrumb') <a href="{{ route('departments.index') }}">Departemen</a> / Edit @endsection

@section('content')
<div class="max-w-lg">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <form action="{{ route('departments.update', $department) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Departemen <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $department->name) }}"
                       class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 dark:border-red-500 @enderror">
                @error('name') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Departemen <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $department->code) }}" maxlength="10"
                       class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase @error('code') border-red-400 dark:border-red-500 @enderror"
                       oninput="this.value = this.value.toUpperCase()">
                @error('code') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $department->description) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $department->is_active ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:ring-blue-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Departemen aktif</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('departments.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
