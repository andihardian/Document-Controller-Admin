@extends('layouts.app')
@section('title', 'Tambah Departemen')
@section('breadcrumb') <a href="{{ route('departments.index') }}">Departemen</a> / Tambah @endsection

@section('content')
<div class="max-w-lg">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
        <form action="{{ route('departments.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Departemen <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 dark:border-red-500 @enderror"
                       placeholder="Contoh: Human Resources">
                @error('name') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Departemen <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code') }}" maxlength="10"
                       class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono uppercase @error('code') border-red-400 dark:border-red-500 @enderror"
                       placeholder="Contoh: HR"
                       oninput="this.value = this.value.toUpperCase()">
                @error('code') <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full text-sm border border-gray-300 dark:border-gray-700 rounded-lg px-3 py-2.5 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Deskripsi singkat departemen...">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                    Tambah Departemen
                </button>
                <a href="{{ route('departments.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
