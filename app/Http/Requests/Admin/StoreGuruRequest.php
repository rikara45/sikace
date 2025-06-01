<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama_guru' => ['required', 'string', 'max:100'],
            'nip' => ['required', 'string', 'max:20', 'unique:gurus,nip'], // NIP sekarang wajib dan unik
            // Email dan password sudah dihapus dari form create sebelumnya
        ];
    }

    public function messages(): array
    {
        return [
            'nama_guru.required' => 'Nama guru wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
        ];
    }
}