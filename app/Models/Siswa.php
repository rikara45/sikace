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
        'status', // <-- Tambahkan ini
    ];

    // Definisikan konstanta untuk status agar mudah dikelola
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_LULUS = 'lulus';
    public const STATUS_PINDAH = 'pindah';
    public const STATUS_DIKELUARKAN = 'dikeluarkan';
    // Tambahkan status lain jika perlu

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

    // Scope untuk filter siswa aktif
    public function scopeAktif($query)
    {
        return $query->where('status', self::STATUS_AKTIF);
    }

    // Scope untuk filter siswa lulus
    public function scopeLulus($query)
    {
        return $query->where('status', self::STATUS_LULUS);
    }
}