<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKelasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'nama_kelas' => 'required|string|max:255',
            'tahun_ajaran' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'wali_kelas_id' => 'nullable|integer|exists:gurus,id',
            'mata_pelajaran_ids' => ['nullable', 'array'],
            'mata_pelajaran_ids.*' => ['integer', 'exists:mata_pelajarans,id'],
            'mata_pelajaran_guru' => ['nullable', 'array'],
            'mata_pelajaran_guru.*' => ['nullable', 'integer', 'exists:gurus,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.regex' => 'Format tahun ajaran tidak valid. Gunakan format YYYY/YYYY, contoh: 2023/2024.',
            'wali_kelas_id.exists' => 'Guru yang dipilih sebagai Wali Kelas tidak valid.',
            'wali_kelas_id.integer' => 'ID Wali Kelas tidak valid.',
            'mata_pelajaran_guru.*.exists' => 'Guru yang dipilih untuk mata pelajaran tidak valid.',
        ];
    }
}