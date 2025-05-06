<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule jika Anda memerlukannya nanti (misal untuk unique)

class UpdateKelasRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Memastikan hanya user dengan role 'admin' yang bisa melakukan request ini
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Ambil objek kelas dari route jika perlu validasi unique yg ignore diri sendiri
        // $kelas = $this->route('kelas');

        // Aturan validasi dasar untuk update
        return [
            'nama_kelas' => ['required', 'string', 'max:50'],
            'tahun_ajaran' => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'], // Memastikan format YYYY/YYYY
            'wali_kelas_id' => ['nullable', 'integer', 'exists:gurus,id'], // Pastikan ID guru ada di tabel gurus jika diisi
        ];
    }

    /**
     * Dapatkan pesan error kustom untuk aturan validasi. (Opsional)
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        // Pesan error kustom agar lebih informatif
        return [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'tahun_ajaran.required' => 'Tahun ajaran wajib diisi.',
            'tahun_ajaran.regex' => 'Format Tahun Ajaran harus YYYY/YYYY (contoh: 2024/2025).',
            'wali_kelas_id.exists' => 'Guru yang dipilih sebagai Wali Kelas tidak valid.',
            'wali_kelas_id.integer' => 'ID Wali Kelas tidak valid.',
        ];
    }
}