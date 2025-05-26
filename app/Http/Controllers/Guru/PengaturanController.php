<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\BobotPenilaian;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PengaturanController extends Controller
{
    // Method helper untuk mengambil data filter dasar (TA, Semester, Kelas, Mapel)
    private function getFilterData(Request $request, $guru)
    {
        $data = [];
        $data['selectedTahunAjaran'] = $request->input('filter_tahun_ajaran');
        $data['selectedSemester'] = $request->input('filter_semester'); // Semester tidak selalu mempengaruhi daftar kelas/mapel
        $data['selectedKelasId'] = $request->input('filter_kelas_id');
        $data['selectedMapelId'] = $request->input('filter_matapelajaran_id');

        $data['availableTahunAjaran'] = DB::table('kelas_mata_pelajaran')
            ->where('guru_id', $guru->id)
            ->select('tahun_ajaran')->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->pluck('tahun_ajaran');

        $data['availableSemester'] = [];
        $data['availableKelas'] = collect([]);
        $data['availableMapel'] = collect([]);
        $data['pengaturanPilihan'] = null; // Untuk menyimpan BobotPenilaian yang sudah ada

        if ($data['selectedTahunAjaran']) {
            $data['availableSemester'] = [1, 2];
            if ($data['selectedSemester']) {
                $data['availableKelas'] = DB::table('kelas_mata_pelajaran as kmp')
                    ->join('kelas', 'kmp.kelas_id', '=', 'kelas.id')
                    ->where('kmp.guru_id', $guru->id)
                    ->where('kmp.tahun_ajaran', $data['selectedTahunAjaran'])
                    ->select('kelas.id', 'kelas.nama_kelas')
                    ->distinct()->orderBy('kelas.nama_kelas')->get();

                if ($data['selectedKelasId']) {
                    $data['availableMapel'] = DB::table('kelas_mata_pelajaran as kmp')
                        ->join('mata_pelajarans as mapel', 'kmp.mata_pelajaran_id', '=', 'mapel.id')
                        ->where('kmp.guru_id', $guru->id)
                        ->where('kmp.kelas_id', $data['selectedKelasId'])
                        ->where('kmp.tahun_ajaran', $data['selectedTahunAjaran'])
                        ->select('mapel.id', 'mapel.nama_mapel', 'mapel.kode_mapel')
                        ->distinct()->orderBy('mapel.nama_mapel')->get();

                    if ($data['selectedMapelId']) {
                        // Ambil pengaturan yang sudah ada
                        $data['pengaturanPilihan'] = BobotPenilaian::firstOrCreate(
                            [
                                'guru_id' => $guru->id,
                                'mata_pelajaran_id' => $data['selectedMapelId'],
                                'kelas_id' => $data['selectedKelasId'],
                                'tahun_ajaran' => $data['selectedTahunAjaran'],
                            ],
                            [ // Nilai default jika record baru dibuat
                                'bobot_tugas' => 30, 'bobot_uts' => 30, 'bobot_uas' => 40,
                                'kkm' => 70, 'batas_a' => 85, 'batas_b' => 75, 'batas_c' => 65,
                            ]
                        );
                    }
                }
            }
        }
        return $data;
    }

    // Halaman untuk Set KKM & Predikat
    public function showKkmForm(Request $request)
    {
        $guru = Auth::user()->guru;
        $filterData = $this->getFilterData($request, $guru);

        return view('guru.pengaturan.form_kkm_predikat', array_merge(
            ['guru' => $guru],
            $filterData
        ));
    }

    public function storeKkm(Request $request)
    {
        $guru = Auth::user()->guru;
        $validated = $request->validate([
            'filter_tahun_ajaran' => 'required|string|max:9|regex:/^\d{4}\/\d{4}$/',
            'filter_semester' => 'required|integer|in:1,2',
            'filter_kelas_id' => 'required|integer|exists:kelas,id',
            'filter_matapelajaran_id' => 'required|integer|exists:mata_pelajarans,id',
            'kkm' => 'required|integer|min:0|max:100',
        ]);

        $kkmInput = (int) $validated['kkm'];

        // Hitung batas predikat berdasarkan KKM dan formula yang Anda inginkan
        $batas_c_db = $kkmInput;
        $batas_b_db = 0;
        $batas_a_db = 0;

        if ($kkmInput < 100) {
            $rentangAtasKKM = 100 - $kkmInput;
            $panjangDasarPredikat = floor($rentangAtasKKM / 3);
            $sisaPembagian = $rentangAtasKKM % 3;

            $panjangPredikatC = $panjangDasarPredikat;
            $panjangPredikatB = $panjangDasarPredikat;
            $panjangPredikatA = $panjangDasarPredikat;

            if ($sisaPembagian == 1) {
                $panjangPredikatA += 1;
            } elseif ($sisaPembagian == 2) {
                $panjangPredikatA += 1;
                $panjangPredikatB += 1;
            }

            $delta = 10; // Atur delta sesuai kebutuhan, atau gunakan $panjangDasarPredikat jika ingin dinamis
            $batas_c_db = $kkmInput;
            $batas_b_db = $kkmInput + $delta + 1;
            $batas_a_db = $batas_b_db + $delta;

            if ($batas_a_db > 100) $batas_a_db = 101;
            if ($batas_b_db >= $batas_a_db && $batas_a_db <= 100) $batas_b_db = $batas_a_db - 1;
            if ($batas_c_db >= $batas_b_db && $batas_b_db <= 100) $batas_c_db = $batas_b_db - 1;
            if ($batas_c_db < $kkmInput) $batas_c_db = $kkmInput;
        } else {
            $batas_c_db = 100;
            $batas_b_db = 101;
            $batas_a_db = 100;
        }

        // Security check
        $isAssigned = DB::table('kelas_mata_pelajaran')
            ->where('guru_id', $guru->id)
            ->where('tahun_ajaran', $validated['filter_tahun_ajaran'])
            ->where('kelas_id', $validated['filter_kelas_id'])
            ->where('mata_pelajaran_id', $validated['filter_matapelajaran_id'])
            ->exists();

        if (!$isAssigned) {
            return back()->withErrors(['access' => 'Anda tidak memiliki akses untuk mengubah pengaturan ini.'])
                         ->withInput();
        }

        $pengaturan = BobotPenilaian::firstOrNew([
            'guru_id' => $guru->id,
            'mata_pelajaran_id' => $validated['filter_matapelajaran_id'],
            'kelas_id' => $validated['filter_kelas_id'],
            'tahun_ajaran' => $validated['filter_tahun_ajaran'],
        ]);

        $pengaturan->kkm = $kkmInput;
        $pengaturan->batas_a = $batas_a_db;
        $pengaturan->batas_b = $batas_b_db;
        $pengaturan->batas_c = $batas_c_db;

        if (!$pengaturan->exists) {
            $pengaturan->bobot_tugas = $pengaturan->bobot_tugas ?? 30;
            $pengaturan->bobot_uts = $pengaturan->bobot_uts ?? 30;
            $pengaturan->bobot_uas = $pengaturan->bobot_uas ?? 40;
        }
        $pengaturan->save();

        return redirect()->route('guru.pengaturan.kkm.form', [
            'filter_tahun_ajaran' => $validated['filter_tahun_ajaran'],
            'filter_semester' => $validated['filter_semester'],
            'filter_kelas_id' => $validated['filter_kelas_id'],
            'filter_matapelajaran_id' => $validated['filter_matapelajaran_id'],
        ])->with('success', 'Pengaturan KKM & Predikat (dihitung otomatis berdasarkan KKM) berhasil disimpan.');
    }

    // Halaman untuk Set Bobot
    public function showBobotForm(Request $request)
    {
        $guru = Auth::user()->guru;
        $filterData = $this->getFilterData($request, $guru);

        return view('guru.pengaturan.form_bobot', array_merge(
            ['guru' => $guru],
            $filterData
        ));
    }

    public function storeBobot(Request $request)
    {
        $guru = Auth::user()->guru;
        $validated = $request->validate([
            'filter_tahun_ajaran' => 'required|string|max:9|regex:/^\d{4}\/\d{4}$/',
            'filter_semester' => 'required|integer|in:1,2',
            'filter_kelas_id' => 'required|integer|exists:kelas,id',
            'filter_matapelajaran_id' => 'required|integer|exists:mata_pelajarans,id',
            'bobot_tugas' => 'required|integer|min:0|max:100',
            'bobot_uts' => 'required|integer|min:0|max:100',
            'bobot_uas' => 'required|integer|min:0|max:100',
        ]);

        if (($validated['bobot_tugas'] + $validated['bobot_uts'] + $validated['bobot_uas']) != 100) {
            return back()->withErrors(['bobot_total' => 'Total bobot Tugas, UTS, dan UAS harus 100%.'])
                         ->withInput();
        }

        // Security check
        // ...

        $pengaturan = BobotPenilaian::firstOrNew([
            'guru_id' => $guru->id,
            'mata_pelajaran_id' => $validated['filter_matapelajaran_id'],
            'kelas_id' => $validated['filter_kelas_id'],
            'tahun_ajaran' => $validated['filter_tahun_ajaran'],
        ]);

        $pengaturan->bobot_tugas = $validated['bobot_tugas'];
        $pengaturan->bobot_uts = $validated['bobot_uts'];
        $pengaturan->bobot_uas = $validated['bobot_uas'];

        if (!$pengaturan->exists) { // Jika record baru, isi KKM/Predikat dengan default
            $pengaturan->kkm = $pengaturan->kkm ?? 70;
            $pengaturan->batas_a = $pengaturan->batas_a ?? 85;
            $pengaturan->batas_b = $pengaturan->batas_b ?? 75;
            $pengaturan->batas_c = $pengaturan->batas_c ?? 65;
        }
        $pengaturan->save();

        return redirect()->route('guru.pengaturan.bobot.form', [
             'filter_tahun_ajaran' => $validated['filter_tahun_ajaran'],
             'filter_semester' => $validated['filter_semester'],
             'filter_kelas_id' => $validated['filter_kelas_id'],
             'filter_matapelajaran_id' => $validated['filter_matapelajaran_id'],
        ])->with('success', 'Pengaturan Bobot berhasil disimpan.');
    }
}
