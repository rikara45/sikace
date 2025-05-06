<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswas';

    protected $fillable = [
        'user_id',
        'nis',
        'nisn',
        'nama_siswa',
        'kelas_id',
        'jenis_kelamin',
    ];

    public function user()
    {
        // Relasi ke user (jika siswa bisa login)
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        // Siswa belongs to satu Kelas
        return $this->belongsTo(Kelas::class);
    }

    public function nilais()
    {
        // Satu siswa punya banyak entri nilai
        return $this->hasMany(Nilai::class);
    }
}