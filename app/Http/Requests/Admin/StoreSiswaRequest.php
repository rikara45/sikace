<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Jangan lupa import Rule

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pastikan hanya admin yang bisa melakukan request ini
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama_siswa' => ['required', 'string', 'max:100'],
            'nis' => ['required', 'string', 'max:20', 'unique:siswas,nis'], // Pastikan NIS unik di tabel siswas
            'nisn' => ['nullable', 'string', 'max:20', 'unique:siswas,nisn'],// Pastikan NISN unik jika diisi
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'], // Pastikan kelas_id ada di tabel kelas
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            // Tambahkan aturan validasi untuk field user jika siswa bisa login
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'], // Jika ingin set password saat buat siswa
        ];
    }
}