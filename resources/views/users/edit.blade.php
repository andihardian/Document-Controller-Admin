@extends('layouts.app')
@section('title', 'Edit User')
@section('breadcrumb') <a href="{{ route('users.index') }}">Users</a> / Edit @endsection

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form action="{{ route('users.update', $user) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected($user->hasRole($role->name))>
                            {{ ucfirst(str_replace('_',' ',$role->name)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                    <select name="department_id" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Departemen</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" @selected($user->department_id == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">
                    Simpan Perubahan
                </button>
                <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
