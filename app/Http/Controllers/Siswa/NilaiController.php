<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\MataPelajaran;
use App\Models\BobotPenilaian; // <-- Import BobotPenilaian
use App\Models\Setting; // Import Setting class

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
        $kkmValue = null; // Inisialisasi KKM

        if ($nilaiDetail) {
            if (is_array($nilaiDetail->nilai_tugas) && count(array_filter($nilaiDetail->nilai_tugas, 'is_numeric')) > 0) {
                $rataRataTugas = Nilai::calculateRataRataTugas($nilaiDetail->nilai_tugas);
            }

            // Ambil KKM dari BobotPenilaian berdasarkan konteks nilaiDetail
            // $nilaiDetail->guru_id adalah guru yang menginput nilai, yang seharusnya sama dengan guru yang set KKM
            if ($nilaiDetail->kelas_id && $nilaiDetail->mata_pelajaran_id && $nilaiDetail->tahun_ajaran && $nilaiDetail->guru_id) {
                 $bobotSetting = BobotPenilaian::where('guru_id', $nilaiDetail->guru_id)
                    ->where('mata_pelajaran_id', $nilaiDetail->mata_pelajaran_id)
                    ->where('kelas_id', $nilaiDetail->kelas_id)
                    ->where('tahun_ajaran', $nilaiDetail->tahun_ajaran)
                    ->first();
                if ($bobotSetting) {
                    $kkmValue = $bobotSetting->kkm;
                }
            }
            // Jika $kkmValue masih null, bisa jadi guru belum set KKM untuk konteks ini.
            // Anda bisa menambahkan fallback ke KKM default jika ada, atau biarkan 'N/A'.
        }

        return view('siswa.nilai.show_mapel', compact(
            'siswa',
            'mataPelajaran',
            'nilaiDetail',
            'availableFilters',
            'filterTahunAjaran',
            'filterSemester',
            'rataRataTugas',
            'kkmValue' // <-- Kirim KKM ke view
        ));
    }
}