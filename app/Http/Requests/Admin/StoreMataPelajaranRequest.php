<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMataPelajaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'nama_mapel' => ['required', 'string', 'max:100'],
            'kode_mapel' => ['nullable', 'string', 'max:10', 'unique:mata_pelajarans,kode_mapel'],
        ];
    }
}