<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa; // Tambahkan ini
use App\Models\Guru;   // Tambahkan ini
use App\Models\Kelas;  // Tambahkan ini
use App\Models\MataPelajaran; // Tambahkan ini
use App\Models\Setting; // Tambahkan ini
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik Umum
        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalKelas = Kelas::count();
        $totalMataPelajaran = MataPelajaran::count();

        // Informasi Tahun Ajaran & Semester Aktif
        $tahunAjaranAktif = Setting::getValue('tahun_ajaran_aktif', 'Belum Diatur'); //
        $semesterAktif = Setting::getValue('semester_aktif', 'Belum Diatur'); //

        // Notifikasi atau Tugas
        // Daftar kelas yang belum memiliki wali kelas
        $kelasTanpaWali = Kelas::whereNull('wali_kelas_id')->get(); //

        // Informasi jika ada guru yang belum memiliki akun login (user_id nya null)
        $guruTanpaAkun = Guru::whereNull('user_id')->get();

        // Data Terbaru (misalnya, 5 data terakhir)
        $siswaTerbaru = Siswa::latest()->take(5)->get(); //
        $guruTerbaru = Guru::latest()->take(5)->get(); //

        return view('admin.dashboard', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'totalMataPelajaran',
            'tahunAjaranAktif',
            'semesterAktif',
            'kelasTanpaWali',
            'guruTanpaAkun',
            'siswaTerbaru',
            'guruTerbaru'
        ));
    }
}