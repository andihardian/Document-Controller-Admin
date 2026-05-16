<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'category_id'    => 'required|exists:document_categories,id',
            'department_id'  => ['required', 'exists:departments,id', function ($attr, $value, $fail) {
                $user = auth()->user();
                // Employee dan department_head hanya boleh upload ke departemen sendiri
                if ($user->hasRole('employee') || $user->hasRole('department_head')) {
                    if ((int) $value !== (int) $user->department_id) {
                        $fail('Anda hanya dapat mengupload dokumen ke departemen Anda sendiri.');
                    }
                }
            }],
            'document_file'  => 'required|file|mimes:pdf|max:10240',
            'effective_date' => 'nullable|date',
            'expiry_date'    => 'nullable|date|after_or_equal:effective_date',
            'revision_note'  => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'document_file.required' => 'File PDF wajib diunggah.',
            'document_file.mimes'    => 'File harus berformat PDF.',
            'document_file.max'      => 'Ukuran file maksimal 10MB.',
            'expiry_date.after_or_equal' => 'Tanggal kadaluarsa harus setelah tanggal efektif.',
        ];
    }
}