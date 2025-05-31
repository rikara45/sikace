<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\BobotPenilaian;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class NilaiController extends Controller
{
    private function getFilterDataForNilai(Request $request, $guru)
    {
        $data = [];
        $data['selectedTahunAjaran'] = $request->input('filter_tahun_ajaran', Setting::getValue('tahun_ajaran_aktif'));
        $data['selectedSemester'] = $request->input('filter_semester', Setting::getValue('semester_aktif'));
        $data['selectedKelasId'] = $request->input('filter_kelas_id');
        $data['selectedMapelId'] = $request->input('filter_matapelajaran_id');

        $data['availableTahunAjaran'] = DB::table('kelas_mata_pelajaran')
            ->where('guru_id', $guru->id)
            ->select('tahun_ajaran')->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->pluck('tahun_ajaran');
        if ($data['availableTahunAjaran']->isEmpty()) {
            $data['availableTahunAjaran'] = collect([Setting::getValue('tahun_ajaran_aktif', date('Y').'/'.(date('Y')+1))]);
        }

        $data['availableSemester'] = [];
        $data['availableKelas'] = collect([]);
        $data['availableMapel'] = collect([]);

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
                }
            }
        }
        return $data;
    }

    // Halaman untuk memilih konteks (TA, Smt, Kls, Mapel) sebelum input nilai
    public function showPilihKonteksUntukInput(Request $request)
    {
        $guru = Auth::user()->guru;
        $filterData = $this->getFilterDataForNilai($request, $guru);

        $allFiltersSelected = $filterData['selectedTahunAjaran'] &&
                              $filterData['selectedSemester'] &&
                              $filterData['selectedKelasId'] &&
                              $filterData['selectedMapelId'];

        return view('guru.nilai.pilih_konteks_input', array_merge(
            ['guru' => $guru, 'allFiltersSelected' => $allFiltersSelected],
            $filterData
        ));
    }

    // Method UTAMA untuk halaman input nilai yang digabung
    public function showFormInputNilaiGabungan(Request $request)
    {
        $guru = Auth::user()->guru;
        if (!$guru) { abort(403); }

        // --- 1. Ambil dan Atur Filter ---
        $defaultTahunAjaran = Setting::getValue('tahun_ajaran_aktif', date('Y').'/'.(date('Y')+1));
        $defaultSemester = (int) Setting::getValue('semester_aktif', '1');

        $selectedTahunAjaran = $request->input('filter_tahun_ajaran', $defaultTahunAjaran);
        $selectedSemester = (int) $request->input('filter_semester', $defaultSemester);
        $selectedKelasId = $request->input('filter_kelas_id');
        $selectedMapelId = $request->input('filter_matapelajaran_id');

        // Opsi untuk dropdown filter
        $availableTahunAjaran = DB::table('kelas_mata_pelajaran')
            ->where('guru_id', $guru->id)
            ->select('tahun_ajaran')->distinct()
            ->orderBy('tahun_ajaran', 'desc')->pluck('tahun_ajaran');
        if ($availableTahunAjaran->isEmpty()) {
            $availableTahunAjaran = collect([$defaultTahunAjaran]);
        }
        $availableSemester = [1, 2];
        $availableKelas = collect([]);
        $availableMapel = collect([]);

        if ($selectedTahunAjaran && $selectedSemester) {
            $availableKelas = DB::table('kelas_mata_pelajaran as kmp')
                ->join('kelas', 'kmp.kelas_id', '=', 'kelas.id')
                ->where('kmp.guru_id', $guru->id)
                ->where('kmp.tahun_ajaran', $selectedTahunAjaran)
                ->select('kelas.id', 'kelas.nama_kelas')
                ->distinct()->orderBy('kelas.nama_kelas')->get();

            if ($selectedKelasId) {
                $availableMapel = DB::table('kelas_mata_pelajaran as kmp')
                    ->join('mata_pelajarans as mapel', 'kmp.mata_pelajaran_id', '=', 'mapel.id')
                    ->where('kmp.guru_id', $guru->id)
                    ->where('kmp.kelas_id', $selectedKelasId)
                    ->where('kmp.tahun_ajaran', $selectedTahunAjaran)
                    ->select('mapel.id', 'mapel.nama_mapel', 'mapel.kode_mapel')
                    ->distinct()->orderBy('mapel.nama_mapel')->get();
            }
        }

        // --- 2. Ambil Data Jika Semua Filter Terpilih ---
        $kelas = null;
        $mapel = null;
        $bobot = null;
        $siswaList = collect([]);
        $existingGrades = collect([]);
        $showInputSection = false;

        if ($selectedTahunAjaran && $selectedSemester && $selectedKelasId && $selectedMapelId) {
            $showInputSection = true;
            $kelas = Kelas::find($selectedKelasId);
            $mapel = MataPelajaran::find($selectedMapelId);

            if ($kelas && $mapel) {
                $bobot = BobotPenilaian::firstOrCreate(
                    [
                        'guru_id' => $guru->id,
                        'mata_pelajaran_id' => $mapel->id,
                        'kelas_id' => $kelas->id,
                        'tahun_ajaran' => $selectedTahunAjaran,
                    ],
                    [
                        'bobot_tugas' => 30, 'bobot_uts' => 30, 'bobot_uas' => 40,
                        'kkm' => 70, 'batas_a' => 85, 'batas_b' => 75, 'batas_c' => 65,
                    ]
                );

                $siswaList = $kelas->siswas()->orderBy('nama_siswa')->get();
                $siswaIds = $siswaList->pluck('id');

                $existingGrades = Nilai::where('kelas_id', $kelas->id)
                    ->where('mata_pelajaran_id', $mapel->id)
                    ->where('tahun_ajaran', $selectedTahunAjaran)
                    ->where('semester', $selectedSemester)
                    ->whereIn('siswa_id', $siswaIds)
                    ->get()->keyBy('siswa_id');
            } else {
                $showInputSection = false;
            }
        }

        // Tambahkan kode berikut:
        $maxNilaiTugasCount = 1; // Default minimal 1 kolom tugas
        if ($showInputSection && $existingGrades->isNotEmpty()) {
            foreach ($existingGrades as $nilai) {
                if (isset($nilai->nilai_tugas) && is_array($nilai->nilai_tugas)) {
                    $maxNilaiTugasCount = max($maxNilaiTugasCount, count($nilai->nilai_tugas));
                }
            }
        }
        // Jika tidak ada nilai tugas sama sekali di existingGrades, pastikan tetap 1
        $maxNilaiTugasCount = max(1, $maxNilaiTugasCount);

        return view('guru.nilai.input', array_merge(
            compact(
                'availableTahunAjaran', 'selectedTahunAjaran',
                'availableSemester', 'selectedSemester',
                'availableKelas', 'selectedKelasId',
                'availableMapel', 'selectedMapelId',
                'showInputSection',
                'kelas',
                'mapel',
                'bobot',
                'siswaList',
                'existingGrades',
                'maxNilaiTugasCount' // <-- Variabel baru untuk jumlah kolom tugas
            ),
            [
                'currentTahunAjaran' => $defaultTahunAjaran,
                'currentSemester' => $defaultSemester
            ]
        ));
    }

    // Method simpanBobot (pindahkan dari GuruPengaturanController)
    public function simpanBobot(Request $request)
    {
        $guru = Auth::user()->guru;
        $validated = $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'matapelajaran_id' => 'required|integer|exists:mata_pelajarans,id',
            'tahun_ajaran' => 'required|string|max:9|regex:/^\d{4}\/\d{4}$/',
            'bobot_tugas' => 'required|integer|min:0|max:100',
            'bobot_uts' => 'required|integer|min:0|max:100',
            'bobot_uas' => 'required|integer|min:0|max:100',
        ]);

        if (($validated['bobot_tugas'] + $validated['bobot_uts'] + $validated['bobot_uas']) != 100) {
            return back()->withErrors(['bobot_total' => 'Total bobot Tugas, UTS, dan UAS harus 100%.'])
                         ->withInput()
                         ->with('scroll_to', 'bobot');
        }

        $pengaturan = BobotPenilaian::firstOrNew([
            'guru_id' => $guru->id,
            'mata_pelajaran_id' => $validated['matapelajaran_id'],
            'kelas_id' => $validated['kelas_id'],
            'tahun_ajaran' => $validated['tahun_ajaran'],
        ]);
        $pengaturan->bobot_tugas = $validated['bobot_tugas'];
        $pengaturan->bobot_uts = $validated['bobot_uts'];
        $pengaturan->bobot_uas = $validated['bobot_uas'];
        if (!$pengaturan->exists) {
            $pengaturan->kkm = $pengaturan->kkm ?? 70;
            $pengaturan->batas_a = $pengaturan->batas_a ?? 85;
            $pengaturan->batas_b = $pengaturan->batas_b ?? 75;
            $pengaturan->batas_c = $pengaturan->batas_c ?? 65;
        }
        $pengaturan->save();

        return redirect()->route('guru.nilai.input', [
            'filter_tahun_ajaran' => $request->input('filter_tahun_ajaran', $validated['tahun_ajaran']),
            'filter_semester' => $request->input('filter_semester'),
            'filter_kelas_id' => $validated['kelas_id'],
            'filter_matapelajaran_id' => $validated['matapelajaran_id'],
        ])->with('success_bobot', 'Pengaturan Bobot berhasil disimpan.')
          ->with('scroll_to', 'bobot');
    }

    // Method simpanKkm dengan logika perhitungan predikat baru
    public function simpanKkm(Request $request)
    {
        $guru = Auth::user()->guru;
        // Validasi input dasar
        $validated = $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'matapelajaran_id' => 'required|integer|exists:mata_pelajarans,id',
            'tahun_ajaran' => 'required|string|max:9|regex:/^\d{4}\/\d{4}$/',
            'kkm' => 'required|integer|min:0|max:100',
            // filter_ parameter hanya untuk redirect, tidak disimpan langsung
            'filter_tahun_ajaran' => 'required|string|max:9',
            'filter_semester' => 'required|integer|in:1,2',
            'filter_kelas_id' => 'required|integer|exists:kelas,id',
            'filter_matapelajaran_id' => 'required|integer|exists:mata_pelajarans,id',
        ]);

        $kkmInput = (int) $validated['kkm'];

        // Hitung batas predikat sesuai formula Anda
        // 100 - kkm = x
        // x / 3 = y (interval dasar)
        // Predikat D = < KKM
        // Predikat C = KKM sampai dengan (KKM + y) -> Batas bawah C = KKM
        // Predikat B = (batas atas predikat C + 1) sampai dengan (batas atas C + 1 + y) -> Batas bawah B = (batas atas C) + 1
        // Predikat A = (batas atas predikat B + 1) sampai 100 -> Batas bawah A = (batas atas B) + 1

        $batas_c_db = $kkmInput;
        $batas_b_db = 0;
        $batas_a_db = 0;

        if ($kkmInput < 100) {
            $x = 100 - $kkmInput;
            $y = floor($x / 3); // Ambil bagian bulat dari interval

            // Batas atas C (inklusif)
            $batasAtasC = $kkmInput + $y;
            // Jika KKM 75, x=25, y=8. C = 75 s/d (75+8) = 83.
            // Batas bawah B adalah batas atas C + 1
            $batas_b_db = $batasAtasC + 1;

            // Batas atas B (inklusif)
            $batasAtasB = $batas_b_db + $y -1; // -1 karena batas_b_db sudah inklusif
            // Jika B mulai 84, batas atas B = 84+8-1 = 91
            // Batas bawah A adalah batas atas B + 1
            $batas_a_db = $batasAtasB + 1;

            // Penyesuaian agar rentang A mengisi sisa sampai 100 dan tidak tumpang tindih
            if ($batas_a_db > 100) $batas_a_db = 100; // A tidak boleh lebih dari 100 untuk batas bawahnya
            if ($batas_b_db >= $batas_a_db && $batas_a_db <= 100) $batas_b_db = $batas_a_db -1;
            if ($batas_b_db < $kkmInput +1 && $kkmInput < 100) $batas_b_db = $kkmInput +1; // Minimal B adalah KKM+1 jika KKM < 99

            // Jika KKM sangat tinggi (misal 95), interval y bisa jadi kecil
            // C: 95 - (95+1) = 96 -> 95-96
            // B: 97 - (97+1-1) = 97 -> 97-97
            // A: 98 - 100
             if ($kkmInput >= 98) { // KKM 98, 99
                $batas_c_db = $kkmInput;
                $batas_b_db = $kkmInput + 1 > 100 ? 100 : $kkmInput + 1;
                $batas_a_db = $kkmInput + 2 > 100 ? 100 : $kkmInput + 2;
                if ($batas_b_db > $batas_a_db) $batas_b_db = $batas_a_db;
                if ($batas_c_db > $batas_b_db) $batas_c_db = $batas_b_db;


            } else if ($kkmInput == 100){
                 $batas_c_db = 100; $batas_b_db = 100; $batas_a_db = 100;
            }


        } else { // KKM = 100
            $batas_c_db = 100;
            $batas_b_db = 100; // Tidak ada B atau C efektif, semua jadi A jika >= 100
            $batas_a_db = 100;
        }
        
        // Finalisasi urutan
        if($kkmInput < 100) {
            if ($batas_b_db <= $batas_c_db) $batas_b_db = $batas_c_db + 1;
            if ($batas_a_db <= $batas_b_db) $batas_a_db = $batas_b_db + 1;
        }
        if ($batas_a_db > 100) $batas_a_db = 100;
        if ($batas_b_db > 100) $batas_b_db = 100; // Jika B jadi > 100, samakan dengan A
        if ($batas_b_db > $batas_a_db) $batas_b_db = $batas_a_db;
        if ($batas_c_db > $batas_b_db) $batas_c_db = $batas_b_db;


        // Security check isAssigned (sama seperti sebelumnya)
        // ...

        $pengaturan = BobotPenilaian::firstOrNew([
            'guru_id' => $guru->id,
            'mata_pelajaran_id' => $validated['matapelajaran_id'],
            'kelas_id' => $validated['kelas_id'],
            'tahun_ajaran' => $validated['tahun_ajaran'],
        ]);
        $pengaturan->kkm = $kkmInput;
        $pengaturan->batas_a = $batas_a_db;
        $pengaturan->batas_b = $batas_b_db;
        $pengaturan->batas_c = $batas_c_db;
        if (!$pengaturan->exists) { // Jika record baru, isi bobot dengan default
            $pengaturan->bobot_tugas = $pengaturan->bobot_tugas ?? 30;
            $pengaturan->bobot_uts = $pengaturan->bobot_uts ?? 30;
            $pengaturan->bobot_uas = $pengaturan->bobot_uas ?? 40;
        }
        $pengaturan->save();

        return redirect()->route('guru.nilai.input', [
            'filter_tahun_ajaran' => $request->input('filter_tahun_ajaran'),
            'filter_semester' => $request->input('filter_semester'),
            'filter_kelas_id' => $validated['kelas_id'],
            'filter_matapelajaran_id' => $validated['matapelajaran_id'],
        ])->with('success_kkm', 'Pengaturan KKM & Predikat berhasil disimpan. Rentang dihitung berdasarkan KKM.')
          ->with('scroll_to', 'kkm');
    }

    // Ganti nama method store sebelumnya menjadi storeNilai
    public function storeNilai(Request $request)
    {
        $guru = Auth::user()->guru;
        $kelasId = $request->input('kelas_id');
        $mapelId = $request->input('matapelajaran_id');
        $tahunAjaran = $request->input('tahun_ajaran');
        $semester = $request->input('semester');
        $gradesData = $request->input('grades', []);

        $validator = Validator::make($request->all(), [
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'matapelajaran_id' => ['required', 'integer', 'exists:mata_pelajarans,id'],
            'tahun_ajaran' => ['required', 'string', 'max:9'],
            'semester' => ['required', 'integer'],
            'grades' => ['required', 'array'],
            'grades.*.nilai_tugas' => ['nullable', 'array'],
            'grades.*.nilai_tugas.*' => ['nullable', 'numeric', 'between:0,100'],
            'grades.*.nilai_uts' => ['nullable', 'numeric', 'between:0,100'],
            'grades.*.nilai_uas' => ['nullable', 'numeric', 'between:0,100'],
        ], [
            'grades.*.*.numeric' => 'Nilai harus berupa angka.',
            'grades.*.*.between' => 'Nilai harus antara 0 dan 100.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('scroll_to', 'form-nilai-siswa-section');
        }

        $pengaturanPenilaian = BobotPenilaian::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mapelId)
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran', $tahunAjaran)
            ->first();

        if (!$pengaturanPenilaian) {
            return redirect()->back()
                             ->with('error_nilai', 'Pengaturan KKM dan Bobot belum diatur. Harap atur terlebih dahulu.')
                             ->withInput()
                             ->with('scroll_to','nilai'); // Scroll ke bagian input nilai
        }

        $maxTugasCountThisSubmission = 0;
        if (isset($gradesData) && is_array($gradesData)) {
            foreach ($gradesData as $siswaId => $dataSiswa) {
                if (isset($dataSiswa['nilai_tugas']) && is_array($dataSiswa['nilai_tugas'])) {
                    $maxTugasCountThisSubmission = max($maxTugasCountThisSubmission, count($dataSiswa['nilai_tugas']));
                }
            }
        }

        $totalAssignmentSlots = $maxTugasCountThisSubmission > 0 ? $maxTugasCountThisSubmission : 0;

        $bobotTugasPersen = $pengaturanPenilaian->bobot_tugas;
        $bobotUtsPersen = $pengaturanPenilaian->bobot_uts;
        $bobotUasPersen = $pengaturanPenilaian->bobot_uas;
        $kkmDb = $pengaturanPenilaian->kkm;
        $batasA_db = $pengaturanPenilaian->batas_a;
        $batasB_db = $pengaturanPenilaian->batas_b;
        $batasC_db = $pengaturanPenilaian->batas_c;

        DB::beginTransaction();
        try {
            foreach ($gradesData as $siswaId => $nilaiKomponen) {
                $nilaiTugasSiswaDariForm = $nilaiKomponen['nilai_tugas'] ?? [];
                
                $processedNilaiTugasArray = [];
                for ($i = 0; $i < $totalAssignmentSlots; $i++) {
                    $score = $nilaiTugasSiswaDariForm[$i] ?? null;
                    if (is_numeric($score)) {
                        $processedNilaiTugasArray[] = (float)$score;
                    } else {
                        $processedNilaiTugasArray[] = null;
                    }
                }

                $nilaiUts = isset($nilaiKomponen['nilai_uts']) && is_numeric($nilaiKomponen['nilai_uts']) ? 
                            (float)$nilaiKomponen['nilai_uts'] : null;
                $nilaiUas = isset($nilaiKomponen['nilai_uas']) && is_numeric($nilaiKomponen['nilai_uas']) ? 
                            (float)$nilaiKomponen['nilai_uas'] : null;

                $nilaiAkhir = Nilai::calculateNilaiAkhir(
                    $processedNilaiTugasArray,
                    $nilaiUts, 
                    $nilaiUas,
                    $bobotTugasPersen, 
                    $bobotUtsPersen, 
                    $bobotUasPersen,
                    $totalAssignmentSlots
                );
                
                $predikat = Nilai::getPredikat($nilaiAkhir, $kkmDb, $batasC_db, $batasB_db, $batasA_db);

                Nilai::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'kelas_id' => $kelasId,
                        'mata_pelajaran_id' => $mapelId,
                        'tahun_ajaran' => $tahunAjaran,
                        'semester' => $semester,
                    ],
                    [
                        'nilai_tugas' => !empty($processedNilaiTugasArray) ? $processedNilaiTugasArray : null,
                        'nilai_uts' => $nilaiUts,
                        'nilai_uas' => $nilaiUas,
                        'nilai_akhir' => $nilaiAkhir,
                        'predikat' => $predikat,
                        'guru_id' => $guru->id,
                    ]
                );
            }
            DB::commit();
            
            return redirect()->route('guru.nilai.input', [
                'filter_tahun_ajaran' => $tahunAjaran,
                'filter_semester' => $semester,
                'filter_kelas_id' => $kelasId,
                'filter_matapelajaran_id' => $mapelId,
            ])->with('success_nilai', 'Semua nilai berhasil disimpan.')
              ->with('scroll_to', 'nilai');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Gagal menyimpan nilai: " . $e->getMessage() . " - Trace: " . $e->getTraceAsString());
            return redirect()->route('guru.nilai.input', [
                'filter_tahun_ajaran' => $tahunAjaran,
                'filter_semester' => $semester,
                'filter_kelas_id' => $kelasId,
                'filter_matapelajaran_id' => $mapelId,
            ])->with('error_nilai', 'Terjadi kesalahan saat menyimpan nilai: ' . $e->getMessage());
        }
    }

    // showRekapNilaiForm tetap sama seperti sebelumnya
    public function showRekapNilaiForm(Request $request)
    {
        $guru = Auth::user()->guru;
        if (!$guru) { abort(403, 'Akses Ditolak.'); }

        $selectedTahunAjaran = $request->input('filter_tahun_ajaran');
        $selectedSemester = $request->input('filter_semester');
        $selectedKelasId = $request->input('filter_kelas_id');
        $selectedMapelId = $request->input('filter_matapelajaran_id');

        $availableTahunAjaran = DB::table('kelas_mata_pelajaran')
            ->where('guru_id', $guru->id)
            ->select('tahun_ajaran')->distinct()
            ->orderBy('tahun_ajaran', 'desc')
            ->pluck('tahun_ajaran');
        if ($availableTahunAjaran->isEmpty() && Auth::user()->guru) { // Tambahkan pengecekan role jika perlu
             $settingTa = Setting::getValue('tahun_ajaran_aktif');
             if ($settingTa) {
                 $availableTahunAjaran = collect([$settingTa]);
             } else {
                 // Fallback jika guru tidak punya jadwal & setting tidak ada
                 $availableTahunAjaran = collect([date('Y').'/'.(date('Y')+1)]);
             }
        }


        $availableSemester = [];
        $availableKelas = collect([]);
        $availableMapel = collect([]);
        $nilaiData = collect([]); // Ganti nama dari $nilaiKelas agar tidak ambigu
        $bobotAktif = null;
        $kelasModel = null;
        $mapelModel = null;
        $showNilaiTable = false;

        if ($selectedTahunAjaran) {
            $availableSemester = [1, 2];
            if ($selectedSemester) {
                $availableKelas = DB::table('kelas_mata_pelajaran as kmp')
                    ->join('kelas', 'kmp.kelas_id', '=', 'kelas.id')
                    ->where('kmp.guru_id', $guru->id)
                    ->where('kmp.tahun_ajaran', $selectedTahunAjaran)
                    ->select('kelas.id', 'kelas.nama_kelas')
                    ->distinct()->orderBy('kelas.nama_kelas')->get();

                if ($selectedKelasId) {
                    $kelasModel = Kelas::with('siswas')->find($selectedKelasId); // Load siswa di sini

                    $availableMapel = DB::table('kelas_mata_pelajaran as kmp')
                        ->join('mata_pelajarans as mapel', 'kmp.mata_pelajaran_id', '=', 'mapel.id')
                        ->where('kmp.guru_id', $guru->id)
                        ->where('kmp.kelas_id', $selectedKelasId)
                        ->where('kmp.tahun_ajaran', $selectedTahunAjaran)
                        ->select('mapel.id', 'mapel.nama_mapel', 'mapel.kode_mapel')
                        ->distinct()->orderBy('mapel.nama_mapel')->get();

                    if ($selectedMapelId && $kelasModel) {
                         $mapelModel = MataPelajaran::find($selectedMapelId);
                        $showNilaiTable = true;

                        // Ambil data nilai
                        $nilaiData = Nilai::where('kelas_id', $selectedKelasId)
                            ->where('mata_pelajaran_id', $selectedMapelId)
                            ->where('tahun_ajaran', $selectedTahunAjaran)
                            ->where('semester', $selectedSemester)
                            // ->with('siswa') // Tidak perlu with siswa di sini karena kita loop $kelasModel->siswas
                            ->get()
                            ->keyBy('siswa_id');

                        // Ambil bobot dan pengaturan KKM/Predikat
                        $bobotAktif = BobotPenilaian::where('guru_id', $guru->id)
                            ->where('mata_pelajaran_id', $selectedMapelId)
                            ->where('kelas_id', $selectedKelasId)
                            ->where('tahun_ajaran', $selectedTahunAjaran)
                            ->first();
                    }
                }
            }
        }

        return view('guru.nilai.rekap_nilai_form', compact(
            'availableTahunAjaran',
            'selectedTahunAjaran',
            'availableSemester',
            'selectedSemester',
            'availableKelas',
            'selectedKelasId',
            'availableMapel',
            'selectedMapelId',
            'showNilaiTable',
            'kelasModel', // Kirim model Kelas (yang sudah di-load siswanya)
            'mapelModel', // Kirim model MataPelajaran
            'nilaiData',  // Kirim data nilai yang sudah di-keyBy siswa_id
            'bobotAktif'  // Kirim bobot dan pengaturan KKM/Predikat
        ));
    }
}