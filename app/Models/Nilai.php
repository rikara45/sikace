<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilais';

    protected $fillable = [
        'siswa_id',
        'mata_pelajaran_id',
        'kelas_id',
        'guru_id', // Simpan ID guru yang input/update terakhir
        'tahun_ajaran',
        'semester',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_akhir',
        'predikat',
        'catatan',
    ];

    protected $casts = [
        'nilai_tugas' => 'decimal:2',
        'nilai_uts' => 'decimal:2',
        'nilai_uas' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    // --- Relasi (sudah ada sebelumnya) ---
    public function siswa() { /* ... */ }
    public function mataPelajaran() { /* ... */ }
    public function kelas() { /* ... */ }
    public function guru() { /* ... */ }


    // --- LOGIC KALKULASI NILAI ---

    /**
     * Menghitung Nilai Akhir berdasarkan komponen.
     * Bobot bisa diatur di sini atau diambil dari konfigurasi.
     *
     * @param float|null $tugas
     * @param float|null $uts
     * @param float|null $uas
     * @return float|null Nilai akhir, atau null jika tidak bisa dihitung.
     */
    public static function calculateNilaiAkhir(?float $tugas, ?float $uts, ?float $uas): ?float
    {
        // Definisikan Bobot (contoh: bisa ditaruh di config/app.php atau setting database)
        $bobotTugas = 0.30; // 30%
        $bobotUts = 0.30;   // 30%
        $bobotUas = 0.40;   // 40%

        // Handle jika salah satu nilai null (sesuaikan aturan sekolah)
        // Contoh: Jika ada yg null, nilai akhir dianggap null
        if (is_null($tugas) || is_null($uts) || is_null($uas)) {
            return null; // Atau bisa diberi nilai 0, atau aturan lain
        }

        // Kalkulasi
        $nilaiAkhir = ($tugas * $bobotTugas) + ($uts * $bobotUts) + ($uas * $bobotUas);

        // Bulatkan ke 2 desimal (opsional)
        return round($nilaiAkhir, 2);
    }

    /**
     * Menentukan Predikat berdasarkan Nilai Akhir.
     * Skala predikat bisa diatur di sini atau diambil dari konfigurasi.
     *
     * @param float|null $nilaiAkhir
     * @return string|null Predikat (A, B, C, D, E), atau null jika nilai akhir null.
     */
    public static function getPredikat(?float $nilaiAkhir): ?string
    {
        if (is_null($nilaiAkhir)) {
            return null;
        }

        // Definisikan Skala Predikat (contoh: >= 85 A, >=75 B, dst.)
        // Pastikan urutan dari tertinggi ke terendah
        if ($nilaiAkhir >= 85) return 'A';
        if ($nilaiAkhir >= 75) return 'B';
        if ($nilaiAkhir >= 65) return 'C';
        if ($nilaiAkhir >= 50) return 'D';
        return 'E'; // Di bawah 50
    }
}