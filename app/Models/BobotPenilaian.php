<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BobotPenilaian extends Model
{
    use HasFactory;

    protected $table = 'bobot_penilaians';

    protected $fillable = [
        'guru_id',
        'mata_pelajaran_id',
        'kelas_id',
        'tahun_ajaran',
        'bobot_tugas',
        'bobot_uts',
        'bobot_uas',
        'kkm',          // <-- Tambahkan
        'batas_a',      // <-- Tambahkan
        'batas_b',      // <-- Tambahkan
        'batas_c',      // <-- Tambahkan
    ];

    // Relasi (opsional, jika ingin mengambil data guru/mapel/kelas dari bobot)
    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }
}