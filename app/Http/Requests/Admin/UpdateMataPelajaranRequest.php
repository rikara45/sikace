<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\MataPelajaran; // Opsional, untuk type hinting jika diperlukan

class UpdateMataPelajaranRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // Pastikan user yang login adalah admin
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Ambil instance MataPelajaran dari route.
        // PENTING: Gunakan 'mataPelajaran' (camelCase) sesuai dengan definisi
        // ->parameters(['matapelajaran' => 'mataPelajaran']) di routes/web.php
        /** @var MataPelajaran|null $mataPelajaran */ // Type hint opsional
        $mataPelajaran = $this->route('mataPelajaran'); // <-- Perbaikan di sini (P besar)

        // Tambahkan pengecekan jika $mataPelajaran null (meskipun seharusnya tidak terjadi jika URL valid)
        if (!$mataPelajaran) {
             // Jika null, aturan unique tidak bisa mengabaikan ID.
             // Mungkin throw exception atau berikan aturan default.
             // Untuk kasus ini, kita asumsikan $mataPelajaran selalu ada jika request valid.
             // Jika error masih terjadi, ada masalah di route model binding itu sendiri.
             // Sementara kita bisa return array kosong atau throw exception:
             // throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Mata Pelajaran tidak ditemukan untuk divalidasi.');
             // return []; // Atau kembalikan aturan tanpa ignore (akan selalu gagal jika kode mapel sama)
        }


        return [
            'nama_mapel' => ['required', 'string', 'max:100'],
            'kode_mapel' => [
                'nullable',
                'string',
                'max:10',
                // Pastikan $mataPelajaran ada sebelum memanggil ->id
                 Rule::unique('mata_pelajarans', 'kode_mapel')->ignore($mataPelajaran ? $mataPelajaran->id : null)
            ],
        ];
    }

     /**
      * Dapatkan pesan error kustom (opsional)
      *
      * @return array<string, string>
      */
     public function messages(): array
     {
         return [
             'nama_mapel.required' => 'Nama mata pelajaran wajib diisi.',
             'kode_mapel.unique' => 'Kode mata pelajaran ini sudah digunakan.',
         ];
     }
}