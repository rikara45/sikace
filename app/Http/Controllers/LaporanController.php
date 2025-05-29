<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\BobotPenilaian;
use App\Models\MataPelajaran; // Import MataPelajaran model
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF Facade
use Illuminate\Support\Facades\Auth; // Untuk panel guru

class LaporanController extends Controller
{
    /**
     * Method untuk mencetak rapor siswa (dari panel siswa atau admin)
     */
    public function cetakRaporSiswa(Request $request, Siswa $siswa)
    {
        $filterTahunAjaran = $request->input('tahun_ajaran', Setting::getValue('tahun_ajaran_aktif'));
        $filterSemester = $request->input('semester', Setting::getValue('semester_aktif'));

        // Validasi apakah siswa yang diminta benar-benar ada
        if (!$siswa) {
            abort(404, 'Siswa tidak ditemukan.');
        }

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
            ->with(['guru', 'mataPelajaran']) // Eager load mataPelajaran juga
            ->select('nilais.*') // Pastikan semua kolom nilais terpilih
            ->orderBy('mata_pelajarans.nama_mapel', 'asc')
            ->get();

        // Ambil informasi kelas dan wali kelas pada periode tersebut
        $infoPeriode = $nilais->first(); // Ambil dari salah satu entri nilai
        $kelasPeriode = $infoPeriode ? Kelas::find($infoPeriode->kelas_id) : $siswa->kelas; // Fallback ke kelas siswa jika tidak ada nilai
        $waliKelasPeriode = $kelasPeriode ? $kelasPeriode->waliKelas : null;

        // Ambil KKM untuk setiap mapel pada periode tersebut
        // Ini mungkin perlu disesuaikan jika KKM disimpan per guru/mapel/kelas/TA
        $kkmMapel = [];
        foreach ($nilais as $n) {
            if ($n->mataPelajaran && $n->kelas && $n->guru) {
                $bobot = BobotPenilaian::where('guru_id', $n->guru_id)
                                      ->where('mata_pelajaran_id', $n->mata_pelajaran_id)
                                      ->where('kelas_id', $n->kelas_id)
                                      ->where('tahun_ajaran', $filterTahunAjaran)
                                      ->first();
                $kkmMapel[$n->mata_pelajaran_id] = $bobot ? $bobot->kkm : null;
            }
        }


        // Data untuk view PDF
        $data = [
            'siswa' => $siswa,
            'nilais' => $nilais,
            'tahunAjaran' => $filterTahunAjaran,
            'semester' => $filterSemester,
            'kelasPeriode' => $kelasPeriode,
            'waliKelasPeriode' => $waliKelasPeriode,
            'kkmMapel' => $kkmMapel, // Kirim KKM ke view
            'namaSekolah' => Setting::getValue('nama_sekolah', 'Nama Sekolah Default'), // Contoh ambil dari settings
            'alamatSekolah' => Setting::getValue('alamat_sekolah', 'Alamat Sekolah Default'),
            // Tambahkan data lain yang diperlukan seperti nama kepala sekolah, dll.
        ];

        // Load view dan generate PDF
        $pdf = Pdf::loadView('laporan.rapor_siswa_pdf', $data);

        // Format nama file: Rapor_NamaSiswa_TA_Semester.pdf
        $fileName = 'Rapor_' . str_replace(' ', '_', $siswa->nama_siswa) . '_' . str_replace('/', '-', $filterTahunAjaran) . '_Semester_' . $filterSemester . '.pdf';

        // return $pdf->download($fileName); // Untuk langsung download
        return $pdf->stream($fileName); // Untuk tampilkan di browser
    }


    /**
     * Method untuk mencetak rekap nilai per kelas (dari panel guru)
     */
    public function cetakRekapNilaiKelas(Request $request, Kelas $kelas)
    {
        $guru = Auth::user()->guru;
        if (!$guru) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $filterTahunAjaran = $request->input('tahun_ajaran', Setting::getValue('tahun_ajaran_aktif'));
        $filterSemester = $request->input('semester', Setting::getValue('semester_aktif'));
        $filterMapelId = $request->input('mapel'); // Ambil dari query parameter

        if (!$filterMapelId) {
            return redirect()->back()->with('error', 'Mata pelajaran harus dipilih untuk mencetak rekap.');
        }

        $mapel = MataPelajaran::findOrFail($filterMapelId);

        // Ambil data siswa di kelas tersebut
        $siswaList = $kelas->siswas()->orderBy('nama_siswa')->get();
        $siswaIds = $siswaList->pluck('id');

        $nilaiData = Nilai::where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mapel->id)
            ->where('tahun_ajaran', $filterTahunAjaran)
            ->where('semester', $filterSemester)
            ->whereIn('siswa_id', $siswaIds)
            ->get()->keyBy('siswa_id');

        $bobotAktif = BobotPenilaian::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mapel->id)
            ->where('kelas_id', $kelas->id)
            ->where('tahun_ajaran', $filterTahunAjaran)
            ->first();

        $data = [
            'kelas' => $kelas,
            'mapel' => $mapel,
            'siswaList' => $siswaList,
            'nilaiData' => $nilaiData,
            'bobotAktif' => $bobotAktif,
            'tahunAjaran' => $filterTahunAjaran,
            'semester' => $filterSemester,
            'guru' => $guru,
            'namaSekolah' => Setting::getValue('nama_sekolah', 'Nama Sekolah Default'),
        ];

        $pdf = Pdf::loadView('laporan.rekap_nilai_kelas_pdf', $data);
        $fileName = 'Rekap_Nilai_' . str_replace(' ', '_', $kelas->nama_kelas) . '_' . str_replace(' ', '_', $mapel->nama_mapel) . '_' . str_replace('/', '-', $filterTahunAjaran) . '_Semester_' . $filterSemester . '.pdf';

        // return $pdf->download($fileName);
        return $pdf->stream($fileName);
    }
}