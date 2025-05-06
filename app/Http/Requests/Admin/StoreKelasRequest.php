<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }

     public function messages(): array
     {
         return [
             'tahun_ajaran.regex' => 'Format Tahun Ajaran harus YYYY/YYYY (contoh: 2024/2025).',
             'wali_kelas_id.exists' => 'Guru yang dipilih sebagai Wali Kelas tidak valid.',
         ];
     }
}