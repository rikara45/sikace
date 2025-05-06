<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hanya admin yang boleh
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama_guru' => ['required', 'string', 'max:100'],
            'nip' => ['nullable', 'string', 'max:20', 'unique:gurus,nip'], // NIP unik jika diisi
            // Validasi untuk membuat akun user (opsional)
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'required_with:email', 'string', 'min:8', 'confirmed'], // Password wajib jika email diisi
        ];
    }

    public function messages(): array // Pesan error kustom (opsional)
    {
        return [
            'password.required_with' => 'Password wajib diisi jika Email diisi.',
        ];
    }
}