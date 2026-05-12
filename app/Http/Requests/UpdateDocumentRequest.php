<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
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
            'effective_date' => 'nullable|date',
            'expiry_date'    => 'nullable|date|after_or_equal:effective_date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul dokumen wajib diisi.',
            'expiry_date.after_or_equal' => 'Tanggal kadaluarsa harus setelah tanggal efektif.',
        ];
    }
}
