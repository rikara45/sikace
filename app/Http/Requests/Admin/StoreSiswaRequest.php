<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama_siswa' => ['required', 'string', 'max:100'],
            'nis' => ['required', 'string', 'max:20', 'unique:siswas,nis'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:siswas,nisn'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            // Hapus validasi untuk email dan password dari form ini
            // 'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            // 'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }
}