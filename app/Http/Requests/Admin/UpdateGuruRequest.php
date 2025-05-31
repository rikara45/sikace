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
        $guru = $this->route('guru');

        return [
            'nama_guru' => ['required', 'string', 'max:100'],
            'nip' => ['required', 'string', 'max:20', Rule::unique('gurus')->ignore($guru->id)],
            // Validasi email dihapus karena field dihilangkan/dinonaktifkan dari form
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'mapel_diampu' => ['nullable', 'array'],
            'mapel_diampu.*' => ['exists:mata_pelajarans,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_guru.required' => 'Nama guru wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah terdaftar untuk guru lain.',
        ];
    }
}