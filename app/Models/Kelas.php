<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import tipe relasi
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kelas extends Model
{
    use HasFactory;

    /**
     * Nama tabel database yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'kelas'; // Eksplisit mendefinisikan nama tabel

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_kelas',
        'wali_kelas_id',
        'tahun_ajaran',
    ];

    /**
     * Mendapatkan data guru yang menjadi wali kelas ini.
     * Relasi one-to-one atau one-to-many inverse (belongsTo).
     */
    public function waliKelas(): BelongsTo
    {
        // Relasi ke model Guru, menggunakan foreign key 'wali_kelas_id'
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    /**
     * Mendapatkan semua siswa yang berada di kelas ini.
     * Relasi one-to-many (hasMany).
     */
    public function siswas(): HasMany
    {
        // Relasi ke model Siswa, Laravel akan mengasumsikan foreign key 'kelas_id' di tabel siswas
        return $this->hasMany(Siswa::class);
    }

    /**
     * Mendapatkan mata pelajaran yang diajarkan di kelas ini.
     * Relasi many-to-many (belongsToMany) melalui tabel pivot 'kelas_mata_pelajaran'.
     * Menyertakan data pivot: id (primary key tabel pivot), guru_id, tahun_ajaran.
     * Juga melakukan join ke tabel gurus untuk mengambil nama guru pengampu secara langsung.
     */
    public function mataPelajarans(): BelongsToMany
{
    return $this->belongsToMany(MataPelajaran::class, 'kelas_mata_pelajaran', 'kelas_id', 'mata_pelajaran_id')
                ->withPivot('id', 'guru_id', 'tahun_ajaran')
                ->withTimestamps()
                // ---> PERIKSA BAGIAN INI <---
                ->join('gurus', 'kelas_mata_pelajaran.guru_id', '=', 'gurus.id') // Apakah join ini benar? Nama tabel/kolom?
                ->select('mata_pelajarans.*', 'gurus.nama_guru as pivot_nama_guru', 'kelas_mata_pelajaran.id as pivot_id'); // Apakah select ini benar?
                // ---> AKHIR BAGIAN PERIKSA <---
}

    /**
     * Mendapatkan semua data nilai yang terkait dengan kelas ini.
     * Relasi one-to-many (hasMany).
     */
    public function nilais(): HasMany
    {
        // Relasi ke model Nilai, Laravel akan mengasumsikan foreign key 'kelas_id' di tabel nilais
        return $this->hasMany(Nilai::class);
    }

    /**
     * Mendapatkan semua guru yang mengajar di kelas ini (unik).
     * Relasi many-to-many (belongsToMany) melalui tabel pivot 'kelas_mata_pelajaran'.
     */
    public function gurusPengajar(): BelongsToMany
    {
        // Parameter: Model Target, Nama Tabel Pivot, Foreign Key model ini di pivot, Foreign Key model target (guru) di pivot
        return $this->belongsToMany(Guru::class, 'kelas_mata_pelajaran', 'kelas_id', 'guru_id')
                    ->withPivot('id', 'mata_pelajaran_id', 'tahun_ajaran') // Bisa sertakan mapel yg diajar guru ini di kelas ini
                    ->withTimestamps()
                    ->distinct(); // Pastikan setiap guru hanya muncul sekali meskipun mengajar banyak mapel
    }
}