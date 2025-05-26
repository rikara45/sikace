<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\Setting; // <-- Import Setting

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

        $mataPelajaran = \App\Models\MataPelajaran::findOrFail($matapelajaran_id); // Ambil data mapel, error 404 jika tidak ada

        // Ambil opsi filter (Tahun Ajaran & Semester) HANYA untuk mapel ini
        $availableFilters = Nilai::where('siswa_id', $siswa->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->select('tahun_ajaran', 'semester')
            ->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        if ($availableFilters->isEmpty()) {
            // Jika siswa belum punya nilai sama sekali untuk mapel ini
            return redirect()->route('siswa.nilai.index')
                             ->with('info', 'Anda belum memiliki data nilai untuk mata pelajaran ' . $mataPelajaran->nama_mapel . '.');
        }

        // Tentukan filter yang aktif
        $filterTahunAjaran = $request->input('tahun_ajaran', $availableFilters->first()?->tahun_ajaran);
        $filterSemester = $request->input('semester', $availableFilters->first()?->semester);

        // Ambil Data Nilai Sesuai Filter untuk mapel ini
        $nilaiDetail = Nilai::where('siswa_id', $siswa->id)
            ->where('mata_pelajaran_id', $mataPelajaran->id)
            ->when($filterTahunAjaran, function ($query, $tahun) {
                return $query->where('tahun_ajaran', $tahun);
            })
            ->when($filterSemester, function ($query, $semester) {
                return $query->where('semester', $semester);
            })
            ->with(['guru', 'kelas']) // Eager load relasi Guru Penginput dan Kelas saat nilai diberikan
            ->first(); // Asumsi satu record nilai per siswa per mapel per semester per tahun ajaran

        $rataRataTugas = null;
        if ($nilaiDetail && is_array($nilaiDetail->nilai_tugas)) {
            $rataRataTugas = Nilai::calculateRataRataTugas($nilaiDetail->nilai_tugas);
        }

        return view('siswa.nilai.show_mapel', compact(
            'siswa',
            'mataPelajaran',
            'nilaiDetail', // Bisa null jika tidak ada data untuk filter terpilih
            'availableFilters',
            'filterTahunAjaran',
            'filterSemester',
            'rataRataTugas'
        ));
    }
}