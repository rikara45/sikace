<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Http\Requests\Admin\StoreKelasRequest;
use App\Http\Requests\Admin\UpdateKelasRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class KelasController extends Controller
{
    /**
     * Menampilkan daftar semua kelas dengan pagination dan search.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'tahun_ajaran');
        $direction = $request->input('direction', 'desc');

        $kelasList = Kelas::withCount('siswas')
            ->with('waliKelas')
            ->when($search, function ($query, $search) {
                // Logic pencarian berdasarkan nama kelas, tahun ajaran, atau nama wali kelas
                return $query->where('nama_kelas', 'like', "%{$search}%")
                             ->orWhere('tahun_ajaran', 'like', "%{$search}%")
                             ->orWhereHas('waliKelas', function ($q) use ($search) {
                                 $q->where('nama_guru', 'like', "%{$search}%");
                             });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        // Kirim data ke view index
        return view('admin.kelas.index', compact('kelasList'));
    }

    /**
     * Menampilkan form untuk membuat kelas baru.
     */
    public function create()
    {
        $gurus = Guru::orderBy('nama_guru')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
        $tahunAjaranAktif = Setting::getValue('tahun_ajaran_aktif');
        $tahunAjaranOptions = [];

        if ($tahunAjaranAktif && preg_match('/^(\d{4})\/(\d{4})$/', $tahunAjaranAktif, $matches)) {
            $startYearAktif = (int)$matches[1];
            $tahunAjaranOptions[] = $tahunAjaranAktif;
            $tahunAjaranOptions[] = ($startYearAktif - 1) . '/' . $startYearAktif;
            $tahunAjaranOptions[] = ($startYearAktif + 1) . '/' . ($startYearAktif + 2);
            $tahunAjaranOptions[] = ($startYearAktif + 2) . '/' . ($startYearAktif + 3);
        } else {
            $currentSystemYear = Carbon::now()->year;
            if (Carbon::now()->month >= 7) {
                $tahunAjaranOptions[] = $currentSystemYear . '/' . ($currentSystemYear + 1);
                $tahunAjaranOptions[] = ($currentSystemYear - 1) . '/' . $currentSystemYear;
                $tahunAjaranOptions[] = ($currentSystemYear + 1) . '/' . ($currentSystemYear + 2);
            } else {
                $tahunAjaranOptions[] = ($currentSystemYear - 1) . '/' . $currentSystemYear;
                $tahunAjaranOptions[] = ($currentSystemYear - 2) . '/' . ($currentSystemYear - 1);
                $tahunAjaranOptions[] = $currentSystemYear . '/' . ($currentSystemYear + 1);
            }
        }

        $existingTahunAjaran = Kelas::select('tahun_ajaran')->distinct()->pluck('tahun_ajaran')->toArray();
        $tahunAjaranOptions = array_merge($tahunAjaranOptions, $existingTahunAjaran);
        $tahunAjaranOptions = array_unique($tahunAjaranOptions);
        rsort($tahunAjaranOptions);

        return view('admin.kelas.create', compact('gurus', 'mataPelajaranList', 'tahunAjaranOptions', 'tahunAjaranAktif'));
    }

    /**
     * Menyimpan data kelas baru ke database.
     */
    public function store(StoreKelasRequest $request)
    {
        $validatedData = $request->validated();
        Log::info('Data Kelas Divalidasi untuk Store:', $validatedData);

        DB::beginTransaction();
        try {
            $kelas = Kelas::create([
                'nama_kelas' => $validatedData['nama_kelas'],
                'tahun_ajaran' => $validatedData['tahun_ajaran'],
                'wali_kelas_id' => $validatedData['wali_kelas_id'] ?? null,
            ]);

            $attachedCount = 0;
            if (!empty($validatedData['mata_pelajaran_ids']) && is_array($validatedData['mata_pelajaran_ids'])) {
                $mapelGuruPairs = $validatedData['mata_pelajaran_guru'] ?? [];

                foreach ($validatedData['mata_pelajaran_ids'] as $mapelId) {
                    $guruIdUntukMapel = $mapelGuruPairs[$mapelId] ?? null;
                    if ($guruIdUntukMapel === '') {
                        $guruIdUntukMapel = null;
                    }

                    Log::info("Attach Mapel ID: {$mapelId}, Guru ID: " . ($guruIdUntukMapel ?? 'NULL') . ", TA: {$kelas->tahun_ajaran}");

                    $kelas->mataPelajarans()->attach($mapelId, [
                        'guru_id' => $guruIdUntukMapel,
                        'tahun_ajaran' => $kelas->tahun_ajaran,
                    ]);
                    $attachedCount++;
                }
            }
            DB::commit();

            $successMessage = $this->generateSuccessMessage($attachedCount, $validatedData);
            return redirect()->route('admin.kelas.index')->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saat menambah kelas baru: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan kelas: Terjadi kesalahan pada sistem. Silakan cek log.')
                ->withInput();
        }
    }

    /**
     * Menampilkan form untuk mengedit data kelas yang sudah ada.
     */
    public function edit(Kelas $kelas)
    {
        $gurus = Guru::orderBy('nama_guru')->get();
        $tahunAjaranAktif = Setting::getValue('tahun_ajaran_aktif');
        $tahunAjaranOptions = [];

        if ($tahunAjaranAktif && preg_match('/^(\d{4})\/(\d{4})$/', $tahunAjaranAktif, $matches)) {
            $startYearAktif = (int)$matches[1];
            $tahunAjaranOptions[] = $tahunAjaranAktif;
            $tahunAjaranOptions[] = ($startYearAktif - 1) . '/' . $startYearAktif;
            $tahunAjaranOptions[] = ($startYearAktif + 1) . '/' . ($startYearAktif + 2);
            $tahunAjaranOptions[] = ($startYearAktif + 2) . '/' . ($startYearAktif + 3);
        } else {
            $currentSystemYear = Carbon::now()->year;
            if (Carbon::now()->month >= 7) {
                $tahunAjaranOptions[] = $currentSystemYear . '/' . ($currentSystemYear + 1);
            } else {
                $tahunAjaranOptions[] = ($currentSystemYear - 1) . '/' . $currentSystemYear;
            }
        }

        $existingTahunAjaran = Kelas::select('tahun_ajaran')->distinct()->pluck('tahun_ajaran')->toArray();
        $tahunAjaranOptions = array_merge($tahunAjaranOptions, $existingTahunAjaran);
        
        if (!in_array($kelas->tahun_ajaran, $tahunAjaranOptions)) {
            $tahunAjaranOptions[] = $kelas->tahun_ajaran;
        }
        
        $tahunAjaranOptions = array_unique($tahunAjaranOptions);
        rsort($tahunAjaranOptions);

        return view('admin.kelas.edit', compact('kelas', 'gurus', 'tahunAjaranOptions', 'tahunAjaranAktif'));
    }

    /**
     * Mengupdate data kelas yang sudah ada di database.
     */
    public function update(UpdateKelasRequest $request, Kelas $kelas)
    {
        // UpdateKelasRequest akan otomatis melakukan validasi
        // Jika validasi gagal, akan otomatis redirect back dengan error

        // Ambil data yang sudah divalidasi
        $validated = $request->validated();
        // Lakukan update pada model $kelas yang didapat dari route model binding
        $kelas->update($validated);
        
        if (isset($validated['mata_pelajaran_ids'])) {
            $kelas->mataPelajarans()->sync($validated['mata_pelajaran_ids']);
        } else {
            $kelas->mataPelajarans()->detach();
        }
        
        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    /**
     * Menampilkan detail satu kelas beserta data terkait (siswa, jadwal mapel).
     */
    public function show(Kelas $kelas)
    {
        $kelas->load(['waliKelas', 'siswas', 'mataPelajarans' => function ($query) use ($kelas) {
            $query->wherePivot('tahun_ajaran', $kelas->tahun_ajaran)
                  ->withPivot('id', 'guru_id', 'tahun_ajaran')
                  ->orderBy('nama_mapel');
        }]);

        $assignedSubjects = $kelas->mataPelajarans;
        $availableSubjects = MataPelajaran::orderBy('nama_mapel')->get();
        $availableTeachers = Guru::orderBy('nama_guru')->get();

        return view('admin.kelas.show', compact(
            'kelas',
            'assignedSubjects',
            'availableSubjects',
            'availableTeachers'
        ));
    }

    /**
     * Menghapus data kelas dari database.
     */
    public function destroy(Kelas $kelas)
    {
        if ($kelas->siswas()->exists()) {
            return redirect()->route('admin.kelas.index')
                ->with('error', 'Tidak dapat menghapus kelas karena masih ada siswa yang terdaftar di kelas ini.');
        }

        if (DB::table('nilais')->where('kelas_id', $kelas->id)->exists()) {
            return redirect()->route('admin.kelas.index')
                ->with('error', 'Tidak dapat menghapus kelas karena masih ada data nilai yang terkait dengan kelas ini.');
        }

        DB::beginTransaction();
        try {
            DB::table('kelas_mata_pelajaran')->where('kelas_id', $kelas->id)->delete();
            $kelas->delete();
            DB::commit();
            return redirect()->route('admin.kelas.index')
                ->with('success', 'Kelas berhasil dihapus beserta semua penugasan mata pelajaran terkait.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat menghapus kelas: " . $e->getMessage());
            return redirect()->route('admin.kelas.index')
                ->with('error', 'Gagal menghapus kelas: ' . $e->getMessage());
        }
    }

    /**
     * Menambahkan penugasan Mata Pelajaran dan Guru ke Kelas.
     */
    public function assignSubject(Request $request, Kelas $kelas)
    {
        $request->validate([
            'mata_pelajaran_id' => [
                'required',
                'exists:mata_pelajarans,id',
                Rule::unique('kelas_mata_pelajaran')->where(function ($query) use ($kelas, $request) {
                    return $query->where('kelas_id', $kelas->id)
                                 ->where('mata_pelajaran_id', $request->mata_pelajaran_id)
                                 ->where('tahun_ajaran', $kelas->tahun_ajaran);
                })->ignore(null, 'id')
            ],
            'guru_id' => 'required|exists:gurus,id',
        ], [
            'mata_pelajaran_id.unique' => 'Mata pelajaran ini sudah ditugaskan ke kelas ini pada tahun ajaran yang sama.'
        ]);

        // Simpan ke pivot dengan data tambahan guru_id
        $kelas->mataPelajarans()->attach($request->mata_pelajaran_id, [
            'guru_id' => $request->guru_id,
            'tahun_ajaran' => $kelas->tahun_ajaran,
        ]);

        return redirect()->route('admin.kelas.show', $kelas)->with('success', 'Mata pelajaran berhasil ditambahkan ke kelas.');
    }

    private function generateSuccessMessage($attachedCount, $validatedData)
    {
        $successMessage = 'Kelas berhasil ditambahkan.';
        if ($attachedCount > 0) {
            $successMessage .= " Sebanyak {$attachedCount} mata pelajaran ditugaskan.";
        } elseif (!empty($validatedData['mata_pelajaran_ids'])) {
            $successMessage .= " Tidak ada mata pelajaran yang berhasil ditugaskan (periksa pemilihan guru).";
        }
        return $successMessage;
    }
}