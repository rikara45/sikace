<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\Nilai;
use Illuminate\Support\Facades\DB; // Import DB
use Illuminate\Support\Facades\Gate; // Import Gate
use Illuminate\Support\Facades\Validator; // Import Validator

class NilaiController extends Controller
{
    /**
     * Menampilkan halaman untuk memilih Kelas dan Mata Pelajaran.
     */
    public function create()
    {
        $guru = Auth::user()->guru;
        if (!$guru) {
            // Handle jika user login bukan guru (seharusnya tidak terjadi karena middleware)
            abort(403, 'Akses Ditolak: Anda bukan Guru.');
        }

        // --- Tentukan Tahun Ajaran & Semester Aktif (Solusi Sementara) ---
        // Idealnya didapat dari setting global atau tanggal
        $currentTahunAjaran = '2024/2025'; // Ganti dengan logic yang benar nanti
        // $currentSemester = 1; // Ganti dengan logic yang benar nanti

        // Ambil data penugasan guru dari tabel pivot kelas_mata_pelajaran
        // Kita butuh ID Kelas, Nama Kelas, ID Mapel, Nama Mapel
        $penugasan = DB::table('kelas_mata_pelajaran as kmp')
            ->join('kelas', 'kmp.kelas_id', '=', 'kelas.id')
            ->join('mata_pelajarans as mapel', 'kmp.mata_pelajaran_id', '=', 'mapel.id')
            ->where('kmp.guru_id', $guru->id)
            ->where('kmp.tahun_ajaran', $currentTahunAjaran) // Filter berdasarkan tahun ajaran aktif
            ->select('kelas.id as kelas_id', 'kelas.nama_kelas', 'mapel.id as mapel_id', 'mapel.nama_mapel', 'mapel.kode_mapel')
            ->distinct() // Hanya ambil kombinasi unik jika perlu
            ->orderBy('kelas.nama_kelas')
            ->orderBy('mapel.nama_mapel')
            ->get();

        // Kelompokkan berdasarkan kelas untuk memudahkan di view (opsional)
        $assignedClasses = $penugasan->groupBy('kelas_id')->map(function ($items, $kelas_id) {
            return [
                'kelas_id' => $kelas_id,
                'nama_kelas' => $items->first()->nama_kelas, // Ambil nama kelas dari item pertama
                'subjects' => $items->map(function ($item) {
                    return [
                        'mapel_id' => $item->mapel_id,
                        'nama_mapel' => $item->nama_mapel,
                        'kode_mapel' => $item->kode_mapel,
                    ];
                })->unique('mapel_id')->values()->toArray() // <-- tambahkan toArray()
            ];
        })->values()->toArray(); // <-- TAMBAHKAN toArray() DI SINI JUGA!

        if (empty($assignedClasses)) {
             // Tampilkan pesan jika guru belum punya jadwal mengajar
             return back()->with('error', 'Anda belum memiliki jadwal mengajar untuk tahun ajaran ' . $currentTahunAjaran);
             // atau redirect ke view khusus
             // return view('guru.nilai.no_schedule');
        }

        return view('guru.nilai.create', compact('assignedClasses', 'currentTahunAjaran'));
    }


    /**
     * Menampilkan form untuk input nilai siswa berdasarkan kelas dan mapel.
     */
    public function inputNilai($kelas_id, $matapelajaran_id)
    {
        $guru = Auth::user()->guru;
        if (!$guru) {
            abort(403, 'Akses Ditolak: Anda bukan Guru.');
        }

        // --- Validasi Kelas & Mapel ---
        $kelas = Kelas::find($kelas_id);
        $mapel = MataPelajaran::find($matapelajaran_id);

        if (!$kelas || !$mapel) {
            abort(404, 'Kelas atau Mata Pelajaran tidak ditemukan.');
        }

        // --- Tentukan Tahun Ajaran & Semester Aktif (Solusi Sementara) ---
        $tahunAjaran = $kelas->tahun_ajaran; // Ambil dari kelas yang dipilih
        $semester = 1; // Asumsi Semester 1 (Ganti dengan logic yang benar nanti)

        // --- Security Check: Pastikan guru ini memang mengajar mapel ini di kelas ini tahun ini ---
         $isAssigned = DB::table('kelas_mata_pelajaran')
             ->where('guru_id', $guru->id)
             ->where('kelas_id', $kelas->id)
             ->where('mata_pelajaran_id', $mapel->id)
             ->where('tahun_ajaran', $tahunAjaran)
             ->exists();

         if (!$isAssigned) {
              abort(403, 'Akses Ditolak: Anda tidak ditugaskan mengajar mata pelajaran ini di kelas ini.');
         }
        // Alternatif: Gunakan Laravel Gates/Policies untuk check authorization yang lebih rapi

        // --- Ambil Data Siswa di Kelas Ini ---
        $siswaList = $kelas->siswas()->orderBy('nama_siswa')->get();
        if ($siswaList->isEmpty()) {
             return redirect()->route('guru.nilai.create') // Kembali ke halaman pilih
                              ->with('error', 'Tidak ada siswa terdaftar di kelas '.$kelas->nama_kelas.'.');
        }
        $siswaIds = $siswaList->pluck('id'); // Ambil array ID siswa

        // --- Ambil Nilai yang Sudah Ada ---
        $existingGrades = Nilai::where('kelas_id', $kelas->id)
            ->where('mata_pelajaran_id', $mapel->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswaIds) // Hanya ambil nilai utk siswa di kelas ini
            ->get()
            ->keyBy('siswa_id'); // Jadikan array asosiatif [siswa_id => Nilai]

        return view('guru.nilai.input', compact(
            'kelas',
            'mapel',
            'siswaList',
            'existingGrades',
            'tahunAjaran',
            'semester'
        ));
    }

    /**
     * Menyimpan atau memperbarui nilai siswa yang diinput.
     */
    public function store(Request $request)
    {
        $guru = Auth::user()->guru;
        if (!$guru) {
            abort(403);
        }

        // Ambil data konteks dari request
        $kelasId = $request->input('kelas_id');
        $mapelId = $request->input('matapelajaran_id');
        $tahunAjaran = $request->input('tahun_ajaran');
        $semester = $request->input('semester');
        $gradesData = $request->input('grades', []); // Ambil array nilai

        // --- Validasi Input Nilai ---
        $validator = Validator::make($request->all(), [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'matapelajaran_id' => ['required', 'integer', 'exists:mata_pelajarans,id'],
            'tahun_ajaran' => ['required', 'string', 'max:9'],
            'semester' => ['required', 'integer'],
            'grades' => ['required', 'array'], // Pastikan grades adalah array
            // Validasi setiap item dalam array grades
            'grades.*.nilai_tugas' => ['nullable', 'numeric', 'between:0,100'],
            'grades.*.nilai_uts' => ['nullable', 'numeric', 'between:0,100'],
            'grades.*.nilai_uas' => ['nullable', 'numeric', 'between:0,100'],
        ], [
            // Pesan error kustom (opsional)
            'grades.*.*.numeric' => 'Nilai harus berupa angka.',
            'grades.*.*.between' => 'Nilai harus antara 0 dan 100.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator) // Kirim error validasi kembali
                        ->withInput(); // Kirim input sebelumnya kembali
        }

        // --- Optional: Security Check lagi (guru berhak input nilai ini) ---
        $isAssigned = DB::table('kelas_mata_pelajaran')
            ->where('guru_id', $guru->id)
            ->where('kelas_id', $kelasId)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->exists();
        if (!$isAssigned) {
             abort(403, 'Akses Ditolak.');
        }

        // --- Proses Penyimpanan Nilai ---
        DB::beginTransaction();
        try {
            foreach ($gradesData as $siswaId => $nilaiKomponen) {
                // Konversi ke float, default ke null jika kosong atau tidak valid
                $nilaiTugas = isset($nilaiKomponen['nilai_tugas']) && is_numeric($nilaiKomponen['nilai_tugas']) ? (float)$nilaiKomponen['nilai_tugas'] : null;
                $nilaiUts = isset($nilaiKomponen['nilai_uts']) && is_numeric($nilaiKomponen['nilai_uts']) ? (float)$nilaiKomponen['nilai_uts'] : null;
                $nilaiUas = isset($nilaiKomponen['nilai_uas']) && is_numeric($nilaiKomponen['nilai_uas']) ? (float)$nilaiKomponen['nilai_uas'] : null;

                // 1. Hitung Nilai Akhir
                $nilaiAkhir = Nilai::calculateNilaiAkhir($nilaiTugas, $nilaiUts, $nilaiUas);

                // 2. Tentukan Predikat
                $predikat = Nilai::getPredikat($nilaiAkhir);

                // 3. Gunakan updateOrCreate untuk menyimpan/memperbarui
                Nilai::updateOrCreate(
                    [
                        // Kunci unik untuk mencari record
                        'siswa_id' => $siswaId,
                        'kelas_id' => $kelasId,
                        'mata_pelajaran_id' => $mapelId,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semester,
                    ],
                    [
                        // Data yang akan diisi atau diperbarui
                        'nilai_tugas' => $nilaiTugas,
                        'nilai_uts' => $nilaiUts,
                        'nilai_uas' => $nilaiUas,
                        'nilai_akhir' => $nilaiAkhir,
                        'predikat' => $predikat,
                        'guru_id' => $guru->id, // Simpan ID guru yang melakukan aksi
                    ]
                );
            }

            DB::commit(); // Simpan semua perubahan jika loop berhasil

            return redirect()->route('guru.nilai.input', ['kelas_id' => $kelasId, 'matapelajaran_id' => $mapelId])
                             ->with('success', 'Semua nilai berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan semua perubahan jika ada error
            // Log::error("Error saving grades: " . $e->getMessage()); // Sebaiknya log error
            return redirect()->route('guru.nilai.input', ['kelas_id' => $kelasId, 'matapelajaran_id' => $mapelId])
                             ->with('error', 'Terjadi kesalahan saat menyimpan nilai: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan rekap nilai satu kelas untuk mapel yang diajar guru.
     */
    public function showKelas($kelas_id, Request $request) // Tambahkan Request untuk filter Mapel
    {
        $guru = Auth::user()->guru;
        if (!$guru) { abort(403); }

        $kelas = Kelas::with('siswas')->find($kelas_id);
        if (!$kelas) { abort(404, 'Kelas tidak ditemukan.'); }

        // --- Tentukan Tahun Ajaran & Semester Aktif (Sementara) ---
        $tahunAjaran = $kelas->tahun_ajaran;
        $semester = 1; // Asumsi

        // --- Ambil Mapel yg Diajar Guru di Kelas Ini ---
        $mapelsDiajar = DB::table('kelas_mata_pelajaran as kmp')
            ->join('mata_pelajarans as mapel', 'kmp.mata_pelajaran_id', '=', 'mapel.id')
            ->where('kmp.guru_id', $guru->id)
            ->where('kmp.kelas_id', $kelas->id)
            ->where('kmp.tahun_ajaran', $tahunAjaran)
            ->select('mapel.id', 'mapel.nama_mapel', 'mapel.kode_mapel')
            ->orderBy('mapel.nama_mapel')
            ->get();

        if ($mapelsDiajar->isEmpty()) {
            return back()->with('error', 'Anda tidak mengajar mata pelajaran apapun di kelas ini.');
        }

        // Tentukan mapel yang aktif (dari request atau default ke yang pertama)
        $filterMapelId = $request->input('matapelajaran_id', $mapelsDiajar->first()?->id);

        // --- Ambil Data Nilai untuk Mapel Terpilih ---
        $nilaiKelas = [];
        if ($filterMapelId) {
            $nilaiKelas = Nilai::where('kelas_id', $kelas->id)
                ->where('mata_pelajaran_id', $filterMapelId)
                ->where('tahun_ajaran', $tahunAjaran)
                ->where('semester', $semester)
                ->with('siswa') // Eager load siswa
                ->get()
                ->keyBy('siswa_id'); // Key by siswa_id for easy lookup
        }

        $mapelAktif = $mapelsDiajar->firstWhere('id', $filterMapelId);

        return view('guru.nilai.show_kelas', compact(
            'guru',
            'kelas',
            'mapelsDiajar', // Untuk dropdown filter mapel
            'filterMapelId',
            'mapelAktif',
            'nilaiKelas', // Data nilai yg sudah di keyBy siswa_id
            'tahunAjaran',
            'semester'
        ));
    }

     /**
      * Menampilkan rekap nilai per mapel across kelas (jika diperlukan nanti).
      * public function showMapel($matapelajaran_id) { ... }
      */
}