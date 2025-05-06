<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule untuk validasi unique ignore
use App\Models\Siswa; // Opsional: bisa di-import untuk type hinting jika diperlukan

class UpdateSiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Pastikan user yang login adalah admin
        // Middleware 'role:admin' pada route juga seharusnya sudah melindungi ini
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Dapatkan objek Siswa yang sedang diupdate dari route model binding.
        // Nama 'siswa' berasal dari nama parameter di definisi route resource.
        // Contoh: Route::resource('siswa', SiswaController::class);
        /** @var Siswa $siswa */
        $siswa = $this->route('siswa');

        // Dapatkan user_id yang terkait dengan siswa ini (jika ada) untuk diabaikan saat cek unique email
        $userIdToIgnore = $siswa->user_id;

        return [
            // Aturan untuk tabel 'siswas'
            'nama_siswa' => ['required', 'string', 'max:100'],
            'nis' => [
                'required',
                'string',
                'max:20',
                Rule::unique('siswas', 'nis')->ignore($siswa->id) // Pastikan NIS unik, abaikan ID siswa saat ini
            ],
            'nisn' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('siswas', 'nisn')->ignore($siswa->id) // Pastikan NISN unik jika diisi, abaikan ID siswa saat ini
            ],
            'kelas_id' => [
                'required',
                'integer',
                'exists:kelas,id' // Pastikan kelas_id ada di tabel 'kelas'
            ],
            'jenis_kelamin' => [
                'nullable',
                Rule::in(['L', 'P']) // Hanya boleh 'L' atau 'P'
            ],

            // Aturan untuk tabel 'users' (jika email/password diisi untuk akun login)
            'email' => [
                'nullable', // Boleh kosong
                'string',
                'email',
                'max:255',
                 Rule::unique('users', 'email')->ignore($userIdToIgnore) // Pastikan email unik, abaikan user ID siswa saat ini
            ],
            'password' => [
                'nullable', // Boleh kosong (artinya tidak ingin ganti password)
                'string',
                'min:8', // Minimal 8 karakter jika diisi
                'confirmed' // Harus ada field 'password_confirmation' yang cocok jika diisi
            ],
        ];
    }

    /**
     * Dapatkan pesan error kustom untuk aturan validasi. (Opsional)
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_siswa.required' => 'Nama siswa wajib diisi.',
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS ini sudah digunakan oleh siswa lain.',
            'nisn.unique' => 'NISN ini sudah digunakan oleh siswa lain.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh user lain.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ];
    }
}