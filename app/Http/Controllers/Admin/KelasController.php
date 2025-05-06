<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\MataPelajaran; // Diperlukan untuk form assign di method show
use App\Http\Requests\Admin\StoreKelasRequest;
use App\Http\Requests\Admin\UpdateKelasRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Diperlukan untuk removeAssignment
use Illuminate\Validation\Rule; // Diperlukan untuk validasi unik di assignSubject

class KelasController extends Controller
{
    /**
     * Menampilkan daftar semua kelas dengan pagination dan search.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $kelasList = Kelas::with('waliKelas') // Eager load relasi waliKelas untuk efisiensi
            ->when($search, function ($query, $search) {
                // Logic pencarian berdasarkan nama kelas, tahun ajaran, atau nama wali kelas
                return $query->where('nama_kelas', 'like', "%{$search}%")
                             ->orWhere('tahun_ajaran', 'like', "%{$search}%")
                             ->orWhereHas('waliKelas', function ($q) use ($search) {
                                 $q->where('nama_guru', 'like', "%{$search}%");
                             });
            })
            ->orderBy('tahun_ajaran', 'desc') // Urutkan berdasarkan tahun ajaran terbaru
            ->orderBy('nama_kelas', 'asc') // Lalu urutkan berdasarkan nama kelas
            ->paginate(10); // Tampilkan 10 data per halaman

        // Kirim data ke view index
        return view('admin.kelas.index', ['kelasList' => $kelasList]);
    }

    /**
     * Menampilkan form untuk membuat kelas baru.
     */
    public function create()
    {
        // Ambil daftar guru untuk pilihan dropdown Wali Kelas
        $gurus = Guru::orderBy('nama_guru')->get();
        // Tampilkan view create dengan data guru
        return view('admin.kelas.create', compact('gurus'));
    }

    /**
     * Menyimpan data kelas baru ke database.
     */
    public function store(StoreKelasRequest $request)
    {
        // StoreKelasRequest akan otomatis melakukan validasi
        // Jika validasi gagal, akan otomatis redirect back dengan error

        // Ambil data yang sudah divalidasi
        $validatedData = $request->validated();

        // Buat record baru di database
        Kelas::create($validatedData);

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.kelas.index')
                         ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail satu kelas beserta data terkait (siswa, jadwal mapel).
     */
    public function show(Kelas $kelas)
    {
        // Load relasi yang dibutuhkan untuk ditampilkan di halaman detail
        $kelas->load(['waliKelas', 'siswas']); // Load wali kelas dan daftar siswa

        // Ambil data untuk section manajemen jadwal mapel/guru di kelas ini
        $assignedSubjects = $kelas->mataPelajarans; // Gunakan relasi yg sudah dimodifikasi di model Kelas
        $availableSubjects = MataPelajaran::orderBy('nama_mapel')->get(); // Semua mapel untuk dropdown tambah
        $availableTeachers = Guru::orderBy('nama_guru')->get(); // Semua guru untuk dropdown tambah

        // Tampilkan view show dengan semua data yang diperlukan
        return view('admin.kelas.show', compact(
            'kelas',
            'assignedSubjects',
            'availableSubjects',
            'availableTeachers'
         ));
    }

    /**
     * Menampilkan form untuk mengedit data kelas yang sudah ada.
     */
    public function edit(Kelas $kelas)
    {
        // Ambil daftar guru untuk pilihan dropdown Wali Kelas
        $gurus = Guru::orderBy('nama_guru')->get();
        // Tampilkan view edit dengan data kelas yang akan diedit dan daftar guru
        return view('admin.kelas.edit', compact('kelas', 'gurus'));
    }

    /**
     * Mengupdate data kelas yang sudah ada di database.
     */
    public function update(UpdateKelasRequest $request, Kelas $kelas)
    {
        // UpdateKelasRequest akan otomatis melakukan validasi
        // Jika validasi gagal, akan otomatis redirect back dengan error

        // Ambil data yang sudah divalidasi
        $validatedData = $request->validated();

        // Lakukan update pada model $kelas yang didapat dari route model binding
        $kelas->update($validatedData); // <-- Kunci dari proses update!

        // Redirect ke halaman index dengan pesan sukses
        return redirect()->route('admin.kelas.index')
                         ->with('success', 'Data kelas berhasil diperbarui.');
    }

    /**
     * Menghapus data kelas dari database.
     */
    public function destroy(Kelas $kelas)
    {
        try {
            // Anda bisa menambahkan pengecekan di sini jika diperlukan
            // Misalnya, jangan hapus kelas jika masih ada siswa atau nilai terkait
            // if ($kelas->siswas()->exists() || $kelas->nilais()->exists()) {
            //     return redirect()->route('admin.kelas.index')
            //                      ->with('error', 'Kelas tidak dapat dihapus karena masih memiliki data terkait.');
            // }

            // Lakukan penghapusan
            $kelas->delete();

            // Redirect ke halaman index dengan pesan sukses
            return redirect()->route('admin.kelas.index')
                             ->with('success', 'Data kelas berhasil dihapus.');

        } catch (\Illuminate\Database\QueryException $e) {
            // Tangani error jika ada foreign key constraint yang mencegah penghapusan
             return redirect()->route('admin.kelas.index')
                              ->with('error', 'Gagal menghapus data kelas. Pastikan tidak ada data siswa, nilai, atau jadwal terkait. Kode Error: ' . $e->getCode());
        } catch (\Exception $e) {
             // Tangani error umum lainnya
             return redirect()->route('admin.kelas.index')
                              ->with('error', 'Gagal menghapus data kelas: ' . $e->getMessage());
        }
    }


    // =======================================================
    // == METHOD UNTUK MANAJEMEN JADWAL MAPEL/GURU DI KELAS ==
    // =======================================================

    /**
     * Menambahkan penugasan Mata Pelajaran dan Guru ke Kelas.
     */
    public function assignSubject(Request $request, Kelas $kelas)
    {
        // Validasi input (mapel & guru ada, kombinasinya unik di kelas & tahun ini)
        $request->validate([
            'mata_pelajaran_id' => [
                'required', 'integer', 'exists:mata_pelajarans,id',
                Rule::unique('kelas_mata_pelajaran')->where(function ($query) use ($kelas, $request) {
                    return $query->where('kelas_id', $kelas->id)
                                 ->where('mata_pelajaran_id', $request->mata_pelajaran_id)
                                 ->where('guru_id', $request->guru_id)
                                 ->where('tahun_ajaran', $kelas->tahun_ajaran);
                }),
            ],
            'guru_id' => ['required', 'integer', 'exists:gurus,id'],
        ], [
            'mata_pelajaran_id.unique' => 'Kombinasi Mata Pelajaran dan Guru ini sudah ditugaskan untuk kelas dan tahun ajaran ini.',
            'mata_pelajaran_id.exists' => 'Mata Pelajaran tidak valid.',
            'guru_id.exists' => 'Guru tidak valid.',
        ]);

        try {
            // Tambahkan relasi ke tabel pivot 'kelas_mata_pelajaran'
            $kelas->mataPelajarans()->attach($request->mata_pelajaran_id, [
                'guru_id' => $request->guru_id,
                'tahun_ajaran' => $kelas->tahun_ajaran,
                'created_at' => now(), // Set timestamps jika perlu
                'updated_at' => now(),
            ]);

            return redirect()->route('admin.kelas.show', $kelas)
                             ->with('success', 'Mata pelajaran berhasil ditugaskan ke kelas.');

        } catch (\Exception $e) {
            return redirect()->route('admin.kelas.show', $kelas)
                             ->with('error', 'Gagal menugaskan mata pelajaran: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus penugasan Mata Pelajaran/Guru dari Kelas berdasarkan ID pivot.
     */
    public function removeAssignment(Kelas $kelas, $pivotId)
    {
        try {
            // Cari & Hapus baris pivot berdasarkan ID nya, pastikan milik kelas yg benar
            $deleted = DB::table('kelas_mata_pelajaran')
                         ->where('id', $pivotId)
                         ->where('kelas_id', $kelas->id) // Keamanan tambahan
                         ->delete();

            if ($deleted) {
                 return redirect()->route('admin.kelas.show', $kelas)
                                  ->with('success', 'Penugasan mata pelajaran berhasil dihapus.');
            } else {
                 // Jika $deleted = 0 (tidak ada baris yg terhapus)
                 return redirect()->route('admin.kelas.show', $kelas)
                                  ->with('error', 'Penugasan tidak ditemukan atau sudah dihapus.');
            }

        } catch (\Exception $e) {
            return redirect()->route('admin.kelas.show', $kelas)
                             ->with('error', 'Gagal menghapus penugasan: ' . $e->getMessage());
        }
    }
}