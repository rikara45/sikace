<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\Setting;
use App\Models\BobotPenilaian;
use App\Models\Kelas; // Tambahkan ini
use Illuminate\Support\Facades\DB; // Tambahkan ini

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $siswa = $user->siswa; // Dapatkan data siswa yang login

        if (!$siswa) {
            // Seharusnya tidak terjadi jika middleware role:siswa aktif
            abort(403, 'Akses Ditolak: Data siswa tidak ditemukan untuk user ini.');
        }

        // Informasi Siswa
        $namaSiswa = $siswa->nama_siswa;
        $nis = $siswa->nis;
        $nisn = $siswa->nisn;

        // Mengambil tahun ajaran dan semester aktif dari settings
        $tahunAjaranAktif = Setting::getValue('tahun_ajaran_aktif');
        $semesterAktif = Setting::getValue('semester_aktif');

        // Kelas dan Wali Kelas saat ini (berdasarkan tahun ajaran aktif)
        // Asumsi siswa hanya terdaftar di satu kelas per tahun ajaran.
        // Jika siswa bisa pindah kelas di tengah tahun ajaran, logika ini perlu disesuaikan.
        // Untuk sederhana, kita ambil kelas terakhir siswa berdasarkan tahun ajaran.
        // Atau, jika model Siswa memiliki relasi `kelas()` yang sudah benar, kita bisa gunakan itu.
        // Kita coba ambil data kelas berdasarkan tahun ajaran aktif dari tabel siswa jika ada,
        // atau dari data nilai terakhir jika perlu.
        $kelasSaatIni = null;
        $waliKelasSaatIni = null;

        if ($siswa->kelas_id) {
             // Cek apakah kelas_id di tabel siswa sesuai dengan tahun ajaran aktif
             // Ini asumsi sederhana. Jika ada tabel histori kelas siswa, itu lebih baik.
            $kelasSiswaModel = Kelas::with('waliKelas')->find($siswa->kelas_id);
            if ($kelasSiswaModel && $kelasSiswaModel->tahun_ajaran == $tahunAjaranAktif) {
                $kelasSaatIni = $kelasSiswaModel;
                $waliKelasSaatIni = $kelasSiswaModel->waliKelas;
            }
        }
        // Jika tidak ketemu dari $siswa->kelas_id atau tahunnya beda, coba cari dari nilai terakhir
        if (!$kelasSaatIni && $tahunAjaranAktif && $semesterAktif) {
            $nilaiTerakhir = Nilai::where('siswa_id', $siswa->id)
                                ->where('tahun_ajaran', $tahunAjaranAktif)
                                ->where('semester', $semesterAktif)
                                ->orderBy('created_at', 'desc')
                                ->first();
            if ($nilaiTerakhir && $nilaiTerakhir->kelas) {
                $kelasSaatIni = $nilaiTerakhir->kelas;
                $waliKelasSaatIni = $kelasSaatIni->waliKelas;
            }
        }


        // Nilai Terbaru (Ringkasan untuk semester aktif/terakhir)
        // Mengambil 5 nilai terakhir di tahun ajaran dan semester aktif
        $nilaiTerbaru = collect([]); // Default koleksi kosong
        $kkmMapelDashboard = []; // Untuk menyimpan KKM

        if ($tahunAjaranAktif && $semesterAktif) {
            $nilaiTerbaru = Nilai::where('siswa_id', $siswa->id)
                ->where('tahun_ajaran', $tahunAjaranAktif)
                ->where('semester', $semesterAktif)
                ->with(['mataPelajaran', 'guru']) // Eager load relasi
                ->orderBy('mata_pelajaran_id') // Atau urutkan berdasarkan nama mapel
                ->take(5) // Ambil beberapa saja untuk ringkasan
                ->get();

            // Ambil KKM untuk mapel yang ada di nilai terbaru
            foreach ($nilaiTerbaru as $nilai) {
                if ($nilai->mataPelajaran && $nilai->kelas_id && $nilai->guru_id) {
                    $bobot = BobotPenilaian::where('guru_id', $nilai->guru_id)
                                          ->where('mata_pelajaran_id', $nilai->mata_pelajaran_id)
                                          ->where('kelas_id', $nilai->kelas_id) // KKM bisa spesifik per kelas
                                          ->where('tahun_ajaran', $tahunAjaranAktif)
                                          ->first();
                    $kkmMapelDashboard[$nilai->mata_pelajaran_id] = $bobot ? $bobot->kkm : 'N/A';
                }
            }
        }


        return view('siswa.dashboard', compact(
            'namaSiswa',
            'nis',
            'nisn',
            'kelasSaatIni',
            'waliKelasSaatIni',
            'tahunAjaranAktif',
            'semesterAktif',
            'nilaiTerbaru',
            'kkmMapelDashboard'
        ));
    }
}