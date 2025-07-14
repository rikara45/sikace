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
            'nip' => ['required', 'string', 'max:20', 'unique:gurus,nip'],
            'username' => ['nullable', 'string', 'alpha_dash', 'max:50', 'unique:users,username'], // <-- Tambahkan ini
        ];
    }

    public function messages(): array
    {
        return [
            'nama_guru.required' => 'Nama guru wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar.',
            'username.unique' => 'Username ini sudah digunakan oleh pengguna lain.', // <-- Tambahkan ini
            'username.alpha_dash' => 'Username hanya boleh berisi huruf, angka, tanda hubung (-), dan garis bawah (_).', // <-- Tambahkan ini
        ];
    }
}