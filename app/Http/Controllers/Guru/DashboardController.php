<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Guru;
use App\Models\Kelas; // Untuk mengambil data kelas yang diajar
use App\Models\MataPelajaran; // Untuk mengambil data mapel yang diampu
use App\Models\BobotPenilaian; // Untuk cek pengaturan bobot & KKM
use App\Models\Nilai; // Untuk cek kelengkapan nilai
use App\Models\Setting; // Untuk tahun ajaran & semester aktif
use Illuminate\Support\Facades\DB; // Untuk query yang lebih kompleks jika perlu

class DashboardController extends Controller
{
    public function index()
    {
        $guru = Auth::user()->guru;
        if (!$guru) {
            // Seharusnya tidak terjadi jika middleware role:guru aktif
            abort(403, 'Akses Ditolak: Anda bukan Guru.');
        }

        // Informasi Guru
        $namaGuru = $guru->nama_guru;

        // Mengambil tahun ajaran dan semester aktif dari settings
        $tahunAjaranAktif = Setting::getValue('tahun_ajaran_aktif');
        $semesterAktif = Setting::getValue('semester_aktif');

        // Daftar mata pelajaran yang diampu pada tahun ajaran aktif
        // Relasi mataPelajaransDiampu di model Guru sudah difilter berdasarkan tahun ajaran aktif jika perlu
        // Jika belum, Anda mungkin perlu menambahkan filter tahun_ajaran di sini atau di relasi model
        $mapelDiampu = $guru->mataPelajaransDiampu()
                            ->wherePivot('tahun_ajaran', $tahunAjaranAktif) // Pastikan pivot table memiliki tahun_ajaran
                            ->distinct() // Hindari duplikasi mapel jika diajar di banyak kelas
                            ->orderBy('nama_mapel')
                            ->get();

        // Daftar kelas yang diajar pada tahun ajaran aktif
        $kelasDiajar = $guru->kelasDiajar()
                             ->wherePivot('tahun_ajaran', $tahunAjaranAktif) // Filter berdasarkan tahun ajaran aktif di pivot
                             ->with('mataPelajarans') // Eager load mapel yg diajar di kelas tsb
                             ->distinct('kelas.id') // Ambil kelas unik
                             ->orderBy('nama_kelas')
                             ->get();


        // Notifikasi Pengaturan Bobot/KKM yang Belum Lengkap
        $pengaturanBelumLengkap = [];
        if ($tahunAjaranAktif) {
            // Ambil semua kombinasi kelas & mapel yang diajar guru pada TA aktif
            $jadwalMengajar = DB::table('kelas_mata_pelajaran as kmp')
                ->join('kelas as k', 'kmp.kelas_id', '=', 'k.id')
                ->join('mata_pelajarans as mp', 'kmp.mata_pelajaran_id', '=', 'mp.id')
                ->where('kmp.guru_id', $guru->id)
                ->where('kmp.tahun_ajaran', $tahunAjaranAktif)
                ->select('k.id as kelas_id', 'k.nama_kelas', 'mp.id as mapel_id', 'mp.nama_mapel', 'kmp.tahun_ajaran')
                ->distinct()
                ->get();

            foreach ($jadwalMengajar as $jadwal) {
                $bobot = BobotPenilaian::where('guru_id', $guru->id)
                    ->where('mata_pelajaran_id', $jadwal->mapel_id)
                    ->where('kelas_id', $jadwal->kelas_id)
                    ->where('tahun_ajaran', $jadwal->tahun_ajaran)
                    ->first();

                // Cek apakah bobot atau KKM belum diisi (atau masih default jika ada penanda default)
                // Untuk contoh ini, kita anggap "belum lengkap" jika record BobotPenilaian tidak ada
                // atau jika KKM masih default (misal 0 atau 70 tergantung logika default Anda)
                // Anda bisa membuat logika pengecekan yang lebih spesifik
                if (!$bobot || $bobot->kkm == 0 || ($bobot->bobot_tugas == 0 && $bobot->bobot_uts == 0 && $bobot->bobot_uas == 0 && $bobot->kkm == 70) ) { // Contoh kondisi "belum lengkap"
                    $pengaturanBelumLengkap[] = $jadwal;
                }
            }
        }


        // Notifikasi Nilai Siswa yang Belum Lengkap (Contoh Sederhana)
        // Ini bisa jadi query yang kompleks tergantung definisi "belum lengkap"
        // Misalnya, nilai akhir masih null untuk siswa aktif di kelas yang diajar pada semester ini
        $nilaiBelumLengkap = [];
        if ($tahunAjaranAktif && $semesterAktif) {
            foreach ($kelasDiajar as $kelas) {
                foreach ($kelas->mataPelajarans()->wherePivot('guru_id', $guru->id)->wherePivot('tahun_ajaran', $tahunAjaranAktif)->get() as $mapel) {
                    $jumlahSiswaDiKelas = $kelas->siswas()->count();
                    $jumlahNilaiMasuk = Nilai::where('kelas_id', $kelas->id)
                        ->where('mata_pelajaran_id', $mapel->id)
                        ->where('guru_id', $guru->id)
                        ->where('tahun_ajaran', $tahunAjaranAktif)
                        ->where('semester', $semesterAktif)
                        ->whereNotNull('nilai_akhir') // Anggap lengkap jika nilai akhir ada
                        ->count();

                    if ($jumlahSiswaDiKelas > 0 && $jumlahNilaiMasuk < $jumlahSiswaDiKelas) {
                        $nilaiBelumLengkap[] = (object)[
                            'nama_kelas' => $kelas->nama_kelas,
                            'nama_mapel' => $mapel->nama_mapel,
                            'siswa_dinilai' => $jumlahNilaiMasuk,
                            'total_siswa' => $jumlahSiswaDiKelas,
                            'kelas_id_raw' => $kelas->id,
                            'mapel_id_raw' => $mapel->id,
                        ];
                    }
                }
            }
        }


        return view('guru.dashboard', compact(
            'namaGuru',
            'mapelDiampu',
            'kelasDiajar',
            'tahunAjaranAktif',
            'semesterAktif',
            'pengaturanBelumLengkap',
            'nilaiBelumLengkap'
        ));
    }
}