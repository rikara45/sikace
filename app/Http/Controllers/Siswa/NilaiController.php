<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Nilai;
use App\Models\Siswa; // Import Siswa

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

        // --- Ambil Opsi Filter (Tahun Ajaran & Semester) ---
        $availableFilters = Nilai::where('siswa_id', $siswa->id)
            ->select('tahun_ajaran', 'semester')
            ->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->orderBy('semester', 'desc')
            ->get();

        // Tentukan filter yang aktif
        $filterTahunAjaran = $request->input('tahun_ajaran', $availableFilters->first()?->tahun_ajaran); // Default ke terbaru
        $filterSemester = $request->input('semester', $availableFilters->first()?->semester); // Default ke terbaru

        // --- Ambil Data Nilai Sesuai Filter ---
        $nilais = Nilai::where('siswa_id', $siswa->id)
            ->when($filterTahunAjaran, function ($query, $tahun) {
                return $query->where('tahun_ajaran', $tahun);
            })
            ->when($filterSemester, function ($query, $semester) {
                return $query->where('semester', $semester);
            })
            ->with(['mataPelajaran', 'guru']) // Eager load relasi Mapel dan Guru Penginput
            ->orderBy('mataPelajaran.nama_mapel', 'asc') // Urutkan berdasarkan nama mapel
            ->get();

        // Ambil informasi kelas & wali kelas pada periode tersebut (jika perlu)
        // Ini agak tricky jika siswa pindah kelas, kita ambil dari data nilai saja jika ada
        $infoPeriode = $nilais->first(); // Ambil data dari record nilai pertama (asumsi 1 kelas per semester)
        $kelasPeriode = $infoPeriode?->kelas; // Ambil data kelas dari relasi di Nilai
        $waliKelasPeriode = $kelasPeriode?->waliKelas; // Ambil wali kelas dari relasi di Kelas


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
      * Menampilkan detail nilai per mapel (jika diperlukan nanti).
      * public function showMapel($matapelajaran_id) { ... }
      */
}