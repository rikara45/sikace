<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajarans';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
    ];

    public function gurus()
    {
        // Relasi Many-to-Many ke Guru melalui tabel pivot guru_mata_pelajaran
        return $this->belongsToMany(Guru::class, 'guru_mata_pelajaran')
                    ->withTimestamps();
    }

     public function kelas()
    {
         // Relasi Many-to-Many ke Kelas melalui tabel pivot kelas_mata_pelajaran
        return $this->belongsToMany(Kelas::class, 'kelas_mata_pelajaran')
                    ->withPivot('guru_id', 'tahun_ajaran')
                    ->withTimestamps();
    }

    public function nilais()
    {
        // Satu mata pelajaran punya banyak entri nilai
        return $this->hasMany(Nilai::class);
    }

    public function getGuruPengampuAttribute()
    {
        if (!$this->pivot || !$this->pivot->guru_id) {
            return null;
        }
        return \App\Models\Guru::find($this->pivot->guru_id);
    }
}