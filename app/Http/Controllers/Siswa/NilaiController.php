<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\BobotPenilaian;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class NilaiController extends Controller
{
    /**
     * Menampilkan rapor digital siswa.
     */
    public function index(Request $request)
    {
        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            abort(403, 'Akses Ditolak: Anda bukan Siswa.');
        }

        // Ambil default dari Setting
        $defaultTahunAjaran = Setting::getValue('tahun_ajaran_aktif', date('Y').'/'.(date('Y')+1));
        $defaultSemester = Setting::getValue('semester_aktif', '1');

        // --- Ambil Opsi Filter (Tahun Ajaran & Semester) ---
        $availableFilters = Nilai::where('siswa_id', $siswa->id)
            ->select('tahun_ajaran', 'semester')
            ->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        // Tentukan filter yang aktif dengan fallback ke default dari Setting
        $filterTahunAjaran = $request->input(
            'tahun_ajaran',
            $availableFilters->where('tahun_ajaran', $defaultTahunAjaran)->isNotEmpty()
                ? $defaultTahunAjaran
                : $availableFilters->first()?->tahun_ajaran
        );
        $filterSemester = $request->input(
            'semester',
            $availableFilters->where('tahun_ajaran', $filterTahunAjaran)->where('semester', (int)$defaultSemester)->isNotEmpty()
                ? (int)$defaultSemester
                : $availableFilters->where('tahun_ajaran', $filterTahunAjaran)->first()?->semester
        );

        // --- Ambil Data Nilai Sesuai Filter ---
        $nilaisQuery = Nilai::query()
            ->join('mata_pelajarans', 'nilais.mata_pelajaran_id', '=', 'mata_pelajarans.id')
            ->where('nilais.siswa_id', $siswa->id);

        if ($filterTahunAjaran) {
            $nilaisQuery->where('nilais.tahun_ajaran', $filterTahunAjaran);
        }

        if ($filterSemester) {
            $nilaisQuery->where('nilais.semester', $filterSemester);
        }

        $nilais = $nilaisQuery
            ->with(['guru'])
            ->select('nilais.*')
            ->orderBy('mata_pelajarans.nama_mapel', 'asc')
            ->get();

        // Ambil informasi kelas & wali kelas pada periode tersebut (jika perlu)
        $infoPeriode = $nilais->first();
        $kelasPeriode = $infoPeriode?->kelas;
        $waliKelasPeriode = $kelasPeriode?->waliKelas;

        return view('siswa.nilai.index', compact(
            'siswa',
            'nilais',
            'availableFilters',
            'filterTahunAjaran',
            'filterSemester',
            'kelasPeriode',
            'waliKelasPeriode'
        ));
    }

    /**
     * Menampilkan detail nilai siswa untuk satu mata pelajaran tertentu,
     * dengan filter tahun ajaran dan semester.
     */
    public function showMapel(Request $request, $matapelajaran_id)
    {
        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            abort(403, 'Akses Ditolak: Anda bukan Siswa.');
        }

        $mataPelajaran = MataPelajaran::findOrFail($matapelajaran_id);

        $availableFilters = Nilai::where('siswa_id', $siswa->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->select('tahun_ajaran', 'semester')
            ->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        if ($availableFilters->isEmpty()) {
            return redirect()->route('siswa.nilai.index')
                             ->with('info', 'Anda belum memiliki data nilai untuk mata pelajaran ' . $mataPelajaran->nama_mapel . '.');
        }

        $filterTahunAjaran = $request->input('tahun_ajaran', $availableFilters->first()?->tahun_ajaran);
        $filterSemester = $request->input('semester', $availableFilters->first()?->semester);

        $nilaiDetail = Nilai::where('siswa_id', $siswa->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->when($filterTahunAjaran, function ($query, $tahun) {
                return $query->where('tahun_ajaran', $tahun);
            })
            ->when($filterSemester, function ($query, $semester) {
                return $query->where('semester', $semester);
            })
            ->with(['guru', 'kelas'])
            ->first();

        $rataRataTugas = null;
        $kkmValue = null;
        $totalAssignmentSlots = 0;
        $bobotSetting = null;

        if ($nilaiDetail) {
            // Get BobotPenilaian settings
            if ($nilaiDetail->guru_id && $nilaiDetail->kelas_id && 
                $nilaiDetail->mata_pelajaran_id && $nilaiDetail->tahun_ajaran) {
                $bobotSetting = BobotPenilaian::where('guru_id', $nilaiDetail->guru_id)
                    ->where('mata_pelajaran_id', $nilaiDetail->mata_pelajaran_id)
                    ->where('kelas_id', $nilaiDetail->kelas_id)
                    ->where('tahun_ajaran', $nilaiDetail->tahun_ajaran)
                    ->first();
                
                if ($bobotSetting) {
                    $kkmValue = $bobotSetting->kkm;
                }
            }

            // Determine totalAssignmentSlots
            if ($nilaiDetail->kelas_id && $nilaiDetail->mata_pelajaran_id && 
                $nilaiDetail->tahun_ajaran && $nilaiDetail->semester) {
                
                $allNilaiTugasInContext = Nilai::where('kelas_id', $nilaiDetail->kelas_id)
                    ->where('mata_pelajaran_id', $nilaiDetail->mata_pelajaran_id)
                    ->where('tahun_ajaran', $nilaiDetail->tahun_ajaran)
                    ->where('semester', $nilaiDetail->semester)
                    ->pluck('nilai_tugas');

                foreach ($allNilaiTugasInContext as $tugasArraySiswaLain) {
                    if (is_array($tugasArraySiswaLain)) {
                        $totalAssignmentSlots = max($totalAssignmentSlots, count($tugasArraySiswaLain));
                    }
                }
                Log::info("[showMapel] Context slots: {$totalAssignmentSlots} for Student ID: {$siswa->id}, Subject ID: {$matapelajaran_id}, Year: {$filterTahunAjaran}, Term: {$filterSemester}");
            }

            // Fallback to current student's assignment count
            if ($totalAssignmentSlots == 0 && is_array($nilaiDetail->nilai_tugas) && 
                !empty($nilaiDetail->nilai_tugas)) {
                $totalAssignmentSlots = count($nilaiDetail->nilai_tugas);
                Log::info("[showMapel] Using current student slots: {$totalAssignmentSlots}");
            }

            // Final fallback for bobot_tugas > 0
            if ($totalAssignmentSlots == 0 && $bobotSetting && $bobotSetting->bobot_tugas > 0) {
                $totalAssignmentSlots = 1;
                Log::info("[showMapel] Using fallback slot: {$totalAssignmentSlots}");
            }

            // Calculate average if we have slots and data
            $currentStudentNilaiTugas = is_array($nilaiDetail->nilai_tugas) ? 
                                      $nilaiDetail->nilai_tugas : [];

            if ($totalAssignmentSlots > 0) {
                $rataRataTugas = Nilai::calculateRataRataTugas(
                    $currentStudentNilaiTugas, 
                    $totalAssignmentSlots
                );
                Log::info("[showMapel] Calculated average: {$rataRataTugas}");
            } else {
                Log::warning("[showMapel] No slots available for average calculation");
            }
        } else {
            Log::warning("[showMapel] No grade details found for Student ID: {$siswa->id}");
        }

        return view('siswa.nilai.show_mapel', compact(
            'siswa',
            'mataPelajaran',
            'nilaiDetail',
            'availableFilters',
            'filterTahunAjaran',
            'filterSemester',
            'rataRataTugas',
            'kkmValue'
        ));
    }
}