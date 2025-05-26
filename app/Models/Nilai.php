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

    public static function calculateRataRataTugas(?array $nilaiTugasArray): ?float
    {
        if (empty($nilaiTugasArray)) {
            return null;
        }
        $validNilaiTugas = array_filter($nilaiTugasArray, function($nilai) {
            return is_numeric($nilai) && $nilai >= 0 && $nilai <= 100;
        });
        if (empty($validNilaiTugas)) {
            return null;
        }
        return round(array_sum($validNilaiTugas) / count($validNilaiTugas), 2);
    }

    public static function calculateNilaiAkhir(?array $nilaiTugasArray, ?float $uts, ?float $uas, int $bobotTugasPersen, int $bobotUtsPersen, int $bobotUasPersen): ?float
    {
        $bobotTugas = $bobotTugasPersen / 100;
        $bobotUts = $bobotUtsPersen / 100;
        $bobotUas = $bobotUasPersen / 100;

        $rataRataTugas = self::calculateRataRataTugas($nilaiTugasArray);

        if (is_null($rataRataTugas) && $bobotTugasPersen > 0) return null; // Tugas wajib jika berbobot
        if (is_null($uts) && $bobotUtsPersen > 0) return null;         // UTS wajib jika berbobot
        if (is_null($uas) && $bobotUasPersen > 0) return null;         // UAS wajib jika berbobot
        
        // Jika ada komponen yang null tapi bobotnya 0, kita bisa anggap komponen itu tidak dihitung
        // Atau, jika ada komponen inti yang null (misal UTS/UAS), nilai akhir bisa null
        // Untuk contoh ini, jika salah satu ada yang null dan berbobot, return null
        if ((is_null($rataRataTugas) && $bobotTugas > 0) || (is_null($uts) && $bobotUts > 0) || (is_null($uas) && $bobotUas > 0) ) {
             // Handle jika salah satu komponen inti null. Bisa disesuaikan.
             // Jika semua bobot 0 tapi ada nilai, hasilnya 0. Jika ada bobot tapi nilai null, hasilnya null.
            if ($bobotTugas == 0 && $bobotUts == 0 && $bobotUas == 0) return 0; // Atau null
            return null;
        }


        $nilaiAkhir = 0;
        if(!is_null($rataRataTugas)) $nilaiAkhir += ($rataRataTugas * $bobotTugas);
        if(!is_null($uts)) $nilaiAkhir += ($uts * $bobotUts);
        if(!is_null($uas)) $nilaiAkhir += ($uas * $bobotUas);
        
        // Jika total bobot tidak 100%, nilai akhir akan diskalakan (atau biarkan apa adanya)
        // Untuk sekarang, kita biarkan apa adanya hasil penjumlahan bobot.
        // Validasi total bobot 100% sebaiknya ada di form input bobot.

        return round($nilaiAkhir, 2);
    }

    /**
     * Menentukan Predikat berdasarkan Nilai Akhir, KKM, dan batas-batas yang sudah dihitung & disimpan.
     * @param float|null $nilaiAkhir
     * @param int $kkm KKM yang ditetapkan guru
     * @param int $batasC Batas bawah nilai untuk predikat C (seharusnya sama dengan KKM)
     * @param int $batasB Batas bawah nilai untuk predikat B
     * @param int $batasA Batas bawah nilai untuk predikat A
     * @return string|null Predikat (A, B, C, D), atau null jika nilai akhir null.
     */
    public static function getPredikat(?float $nilaiAkhir, int $kkm, int $batasCtersimpan, int $batasBtersimpan, int $batasAtersimpan): ?string
    {
        if (is_null($nilaiAkhir)) {
            return null;
        }

        // Perbaikan urutan pengecekan:
        // 1. Cek untuk predikat D (di bawah KKM) terlebih dahulu
        if ($nilaiAkhir < $kkm) { // Menggunakan KKM langsung untuk batas D
            return 'D';
        }
        // 2. Cek dari predikat tertinggi (A) ke terendah (C) untuk nilai di atas atau sama dengan KKM
        // Asumsi $batasAtersimpan, $batasBtersimpan, $batasCtersimpan adalah batas BAWAH (inklusif)
        // dan $batasCtersimpan seharusnya sama dengan $kkm
        elseif ($nilaiAkhir >= $batasAtersimpan && $nilaiAkhir <= 100) {
            return 'A';
        } elseif ($nilaiAkhir >= $batasBtersimpan) {
            return 'B';
        } elseif ($nilaiAkhir >= $batasCtersimpan) { // Ini sama dengan $nilaiAkhir >= $kkm
            return 'C';
        }
        
        // Fallback ini seharusnya tidak pernah tercapai jika logika di atas benar
        // dan batas-batas predikat (A,B,C) sudah benar di atas KKM.
        // Jika nilaiAkhir >= KKM tapi tidak masuk A, B, atau C, maka ada yang salah dengan
        // nilai batasA, batasB, atau batasC yang disimpan.
        // Untuk sementara, jika terjadi, bisa kembalikan 'C' karena sudah >= KKM.
        // Namun, idealnya ini tidak terjadi.
        return 'C'; // Atau 'D' jika ingin lebih ketat, tapi KKM sudah terpenuhi.
                    // Sebaiknya logika batasA, B, C dipastikan benar saat disimpan.
    }
}