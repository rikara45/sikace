<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_guru',
    ];

    public function user()
    {
        // Relasi ke user (jika guru bisa login)
        return $this->belongsTo(User::class);
    }

    public function kelasWali()
    {
        // Relasi ke kelas di mana guru ini menjadi wali kelas
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    public function mataPelajaransDiampu()
    {
        return $this->belongsToMany(
            \App\Models\MataPelajaran::class,
            'kelas_mata_pelajaran',
            'guru_id', // foreign key di tabel pivot untuk guru
            'mata_pelajaran_id' // foreign key di tabel pivot untuk mapel
        )->withPivot('kelas_id', 'tahun_ajaran')->withTimestamps();
    }

    public function mataPelajaransDiajar()
    {
        // Relasi ke Mapel yang diajar di kelas tertentu (melalui pivot kelas_mata_pelajaran)
         return $this->belongsToMany(MataPelajaran::class, 'kelas_mata_pelajaran', 'guru_id', 'mata_pelajaran_id')
                    ->withPivot('kelas_id', 'tahun_ajaran')
                    ->withTimestamps();
    }

    public function kelasDiajar()
    {
        // Relasi ke Kelas yang diajar oleh guru ini (melalui pivot kelas_mata_pelajaran)
         return $this->belongsToMany(Kelas::class, 'kelas_mata_pelajaran', 'guru_id', 'kelas_id')
                    ->withPivot('mata_pelajaran_id', 'tahun_ajaran')
                    ->withTimestamps();
    }


    public function nilais()
    {
        // Relasi ke nilai yang diinput oleh guru ini
        return $this->hasMany(Nilai::class);
    }
}