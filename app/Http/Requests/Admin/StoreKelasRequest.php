<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama_kelas' => ['required', 'string', 'max:50'],
            'tahun_ajaran' => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'], // Format YYYY/YYYY
            'wali_kelas_id' => ['nullable', 'integer', 'exists:gurus,id'], // Pastikan ID guru ada
            'mata_pelajaran_ids' => ['nullable', 'array'],
            'mata_pelajaran_ids.*' => ['integer', 'exists:mata_pelajarans,id'],
            'mata_pelajaran_guru' => ['nullable', 'array'],
            'mata_pelajaran_guru.*' => ['nullable', 'integer', 'exists:gurus,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.regex' => 'Format Tahun Ajaran harus YYYY/YYYY (contoh: 2024/2025).',
            'wali_kelas_id.exists' => 'Guru yang dipilih sebagai Wali Kelas tidak valid.',
            'mata_pelajaran_ids.*.exists' => 'Mata pelajaran yang dipilih tidak valid.',
            'mata_pelajaran_guru.*.exists' => 'Guru pengampu yang dipilih untuk mata pelajaran tidak valid.',
        ];
    }
}