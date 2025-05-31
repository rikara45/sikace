<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    use HasFactory;

    protected $table = 'nilais';

    protected $fillable = [
        'siswa_id', 'mata_pelajaran_id', 'kelas_id', 'guru_id', 'tahun_ajaran', 'semester',
        'nilai_tugas', 'nilai_uts', 'nilai_uas', 'nilai_akhir', 'predikat', 'catatan',
    ];

    protected $casts = [
        'nilai_tugas' => 'array',
        'nilai_uts' => 'decimal:2',
        'nilai_uas' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    // Relasi
    public function siswa(): BelongsTo { return $this->belongsTo(Siswa::class, 'siswa_id'); }
    public function mataPelajaran(): BelongsTo { return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id'); }
    public function kelas(): BelongsTo { return $this->belongsTo(Kelas::class, 'kelas_id'); }
    public function guru(): BelongsTo { return $this->belongsTo(Guru::class, 'guru_id'); }

    /**
     * Kalkulasi rata-rata tugas dengan memperhitungkan jumlah total slot tugas.
     *
     * @param array|null $nilaiTugasArray Array nilai tugas siswa.
     * @param int $totalAssignmentSlots Jumlah total slot tugas yang seharusnya ada.
     * @return float|null
     */
    public static function calculateRataRataTugas($nilaiTugasArray, $totalSlots = 0)
    {
        if (!is_array($nilaiTugasArray) || $totalSlots <= 0) {
            return null;
        }
        $nilaiLengkap = [];
        for ($i = 0; $i < $totalSlots; $i++) {
            $nilaiLengkap[] = isset($nilaiTugasArray[$i]) && is_numeric($nilaiTugasArray[$i])
                ? floatval($nilaiTugasArray[$i])
                : 0;
        }
        $sum = array_sum($nilaiLengkap);
        return $totalSlots > 0 ? $sum / $totalSlots : null;
    }

    /**
     * Kalkulasi nilai akhir dengan memperhitungkan jumlah total slot tugas.
     *
     * @param array|null $nilaiTugasArray
     * @param float|null $uts
     * @param float|null $uas
     * @param int $bobotTugasPersen
     * @param int $bobotUtsPersen
     * @param int $bobotUasPersen
     * @param int $totalAssignmentSlots
     * @return float|null
     */
    public static function calculateNilaiAkhir(
        ?array $nilaiTugasArray,
        ?float $uts,
        ?float $uas,
        int $bobotTugasPersen,
        int $bobotUtsPersen,
        int $bobotUasPersen,
        int $totalAssignmentSlots
    ): ?float {
        $bobotTugas = $bobotTugasPersen / 100;
        $bobotUts = $bobotUtsPersen / 100;
        $bobotUas = $bobotUasPersen / 100;

        $rataRataTugas = self::calculateRataRataTugas($nilaiTugasArray, $totalAssignmentSlots);

        if (is_null($rataRataTugas) && $bobotTugasPersen > 0 && $totalAssignmentSlots > 0) return null;
        if (is_null($uts) && $bobotUtsPersen > 0) return null;
        if (is_null($uas) && $bobotUasPersen > 0) return null;
        
        if ($bobotTugas == 0 && $bobotUts == 0 && $bobotUas == 0 && 
            ($rataRataTugas !== null || $uts !== null || $uas !== null)) return 0;
        
        if (($rataRataTugas === null && $bobotTugas > 0 && $totalAssignmentSlots > 0) || 
            ($uts === null && $bobotUts > 0) || 
            ($uas === null && $bobotUas > 0)) {
            return null;
        }

        $nilaiAkhir = 0;
        if (!is_null($rataRataTugas) && $totalAssignmentSlots > 0) {
            $nilaiAkhir += ($rataRataTugas * $bobotTugas);
        }
        if (!is_null($uts)) {
            $nilaiAkhir += ($uts * $bobotUts);
        }
        if (!is_null($uas)) {
            $nilaiAkhir += ($uas * $bobotUas);
        }
        
        return round($nilaiAkhir, 2);
    }

    /**
     * Menentukan Predikat berdasarkan Nilai Akhir dan KKM.
     */
    public static function getPredikat(?float $nilaiAkhir, int $kkm, int $batasCtersimpan, int $batasBtersimpan, int $batasAtersimpan): ?string
    {
        if (is_null($nilaiAkhir)) {
            return null;
        }
        if ($nilaiAkhir < $kkm) {
            return 'D';
        }
        if ($nilaiAkhir >= $batasAtersimpan && $nilaiAkhir <= 100) {
            return 'A';
        }
        if ($nilaiAkhir >= $batasBtersimpan) {
            return 'B';
        }
        return 'C';
    }
}