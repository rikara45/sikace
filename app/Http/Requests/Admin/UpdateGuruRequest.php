<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuruRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        // Dapatkan objek Guru dari route model binding
        $guru = $this->route('guru');

        return [
            'nama_guru' => ['required', 'string', 'max:100'],
            'nip' => ['nullable', 'string', 'max:20', Rule::unique('gurus')->ignore($guru->id)], // Abaikan NIP guru ini saat cek unique
            // Validasi untuk update/membuat akun user
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users')->ignore($guru->user_id)], // Abaikan email user guru ini saat cek unique
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], // Password hanya diupdate jika diisi
        ];
    }
}