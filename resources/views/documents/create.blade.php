@extends('layouts.app')
@section('title', 'Upload Dokumen Baru')
@section('breadcrumb') <a href="{{ route('documents.index') }}">Dokumen</a> / Upload @endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-400 @enderror"
                       placeholder="Contoh: Prosedur Rekrutmen Karyawan">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="category_id"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('category_id') border-red-400 @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>
                            {{ $cat->name }} ({{ $cat->prefix }})
                        </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departemen <span class="text-red-500">*</span></label>
                    <select name="department_id"
                            class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('department_id') border-red-400 @enderror">
                        <option value="">Pilih Departemen</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected(old('department_id') == $dept->id)>
                            {{ $dept->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Deskripsi singkat mengenai dokumen ini...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Efektif</label>
                    <input type="date" name="effective_date" value="{{ old('effective_date') }}"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kadaluarsa</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- File upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">File PDF <span class="text-red-500">*</span></label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                    <input type="file" name="document_file" id="document_file" accept=".pdf" class="hidden"
                           onchange="updateFileLabel(this)">
                    <label for="document_file" class="cursor-pointer">
                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span id="file-label" class="text-sm text-gray-500">Klik untuk pilih file PDF (maks. 10MB)</span>
                    </label>
                </div>
                @error('document_file') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Revisi</label>
                <input type="text" name="revision_note" value="{{ old('revision_note', 'Versi awal') }}"
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-blue-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                    Upload Dokumen
                </button>
                <a href="{{ route('documents.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function updateFileLabel(input) {
    const label = document.getElementById('file-label');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const size = (file.size / 1024 / 1024).toFixed(2);
        label.textContent = `${file.name} (${size} MB)`;
        label.classList.add('text-blue-600', 'font-medium');
    }
}
</script>
@endsection
