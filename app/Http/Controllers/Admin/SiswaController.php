<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas; // Import model Kelas
use App\Models\User; // Import model User
use App\Http\Requests\Admin\StoreSiswaRequest;
use App\Http\Requests\Admin\UpdateSiswaRequest; // Import StoreSiswaRequest
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Import Hash facade
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; // Import Log facade

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'nis');
        $direction = $request->input('direction', 'asc');
        $kelasIds = $request->input('kelas_ids', []); // Ambil array kelas_ids dari request

        $siswas = Siswa::with('kelas')
            ->when($search, function ($query, $search) {
                return $query->where('nama_siswa', 'like', "%{$search}%")
                             ->orWhere('nis', 'like', "%{$search}%");
            })
            ->when(!empty($kelasIds), function ($query) use ($kelasIds) {
                // Filter berdasarkan kelas_id jika kelasIds tidak kosong
                return $query->whereIn('kelas_id', $kelasIds);
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString(); // Pertahankan query string (termasuk filter kelas)

        $kelas = Kelas::orderBy('nama_kelas')->get(); // Ambil semua data kelas

        return view('admin.siswa.index', compact('siswas', 'kelas')); // Kirim data kelas ke view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get(); // Ambil semua data kelas untuk dropdown
        return view('admin.siswa.create', compact('kelas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiswaRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Buat "email" unik untuk tabel users berdasarkan NIS
            // Ini hanya untuk memenuhi constraint unique email di tabel users,
            // Siswa TIDAK AKAN LOGIN dengan ini.
            $pseudoEmail = $validated['nis'] . '@internal.siswa'; // Pastikan domain ini tidak dipakai untuk email asli

            // Buat user baru
            $user = User::create([
                'name' => $validated['nama_siswa'],
                'email' => $pseudoEmail,
                'password' => Hash::make($validated['nis']), // Password awal adalah NIS
                'email_verified_at' => now(), // Anggap langsung terverifikasi
            ]);
            $user->assignRole('siswa');

            // Buat data Siswa
            Siswa::create([
                'user_id' => $user->id,
                'nama_siswa' => $validated['nama_siswa'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'kelas_id' => $validated['kelas_id'],
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            ]);

            DB::commit();
            return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan data siswa: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Siswa $siswa)
    {
         // Load relasi yang mungkin dibutuhkan
         $siswa->load('kelas', 'user', 'nilais.mataPelajaran'); // Contoh load relasi nilai dan mapel
        return view('admin.siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
         $siswa->load('user'); // Load data user terkait jika ada
         $kelas = Kelas::orderBy('nama_kelas')->get();
        return view('admin.siswa.edit', compact('siswa', 'kelas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Update data Siswa
            $siswa->update([
                'nama_siswa' => $validated['nama_siswa'],
                'nis' => $validated['nis'], // Jika NIS diubah, password user juga harus dipertimbangkan untuk diubah
                'nisn' => $validated['nisn'] ?? null,
                'kelas_id' => $validated['kelas_id'],
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            ]);

            // Update data User terkait (nama, dan mungkin password jika NIS berubah dan password belum diubah siswa)
            if ($siswa->user) {
                $userDataToUpdate = ['name' => $validated['nama_siswa']];

                // Jika NIS (yang jadi password awal) diubah oleh admin, DAN siswa belum pernah ganti password sendiri
                // Anda mungkin ingin mengupdate password user juga. Ini butuh logika tambahan
                // untuk mengecek apakah password user masih hash dari NIS lama.
                // Untuk saat ini, kita tidak update password dari sini kecuali ada input password khusus.
                // Jika Anda menyediakan field password di form edit siswa oleh admin:
                // if (!empty($validated['password'])) {
                //     $userDataToUpdate['password'] = Hash::make($validated['password']);
                // }
                // Jika NIS diubah dan password user masih sama dengan hash NIS lama:
                // if ($siswa->isDirty('nis') && Hash::check($siswa->getOriginal('nis'), $siswa->user->password)) {
                //     $userDataToUpdate['password'] = Hash::make($validated['nis']);
                // }


                // Jika NIS diubah, "email" pseudo juga perlu diubah
                if ($siswa->isDirty('nis')) {
                     $userDataToUpdate['email'] = $validated['nis'] . '@internal.siswa';
                }


                $siswa->user->update($userDataToUpdate);
            }

            DB::commit();
            return redirect()->route('admin.siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memperbarui data siswa: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Siswa $siswa)
    {
        DB::beginTransaction();
        try {
            // Hapus user terkait jika ada (opsional, tergantung kebijakan)
             if ($siswa->user) {
                 $siswa->user->delete();
             }

             // Hapus data siswa (nilai terkait akan terhapus otomatis karena cascade delete)
             $siswa->delete();

             DB::commit();

            return redirect()->route('admin.siswa.index')
                         ->with('success', 'Data siswa berhasil dihapus.');
        } catch (\Exception $e) {
             DB::rollBack();
             return redirect()->route('admin.siswa.index')
                          ->with('error', 'Gagal menghapus data siswa: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for importing students.
     */
    public function showImportForm()
    {
        $kelasList = Kelas::orderBy('tahun_ajaran', 'desc')->orderBy('nama_kelas', 'asc')->get();
        return view('admin.siswa.import', compact('kelasList'));
    }

    /**
     * Handle the import of students from CSV.
     */
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'kelas_id' => 'required|integer|exists:kelas,id',
        ]);

        $file = $request->file('csv_file');
        $kelasId = $request->input('kelas_id');

        $import = new SiswaImport($kelasId);

        try {
            Excel::import($import, $file);

            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();
            $errorsEncountered = $import->getErrorsEncountered();

            $messages = [];
            if ($importedCount > 0) {
                $messages[] = "Berhasil mengimpor {$importedCount} data siswa.";
            }
            if ($skippedCount > 0) {
                $messages[] = "Gagal/Melewati {$skippedCount} data siswa.";
            }

            if (!empty($errorsEncountered)) {
                // Batasi jumlah error yang ditampilkan agar tidak terlalu panjang
                $displayErrors = array_slice($errorsEncountered, 0, 10);
                $errorMessage = "Detail kesalahan: <br>" . implode("<br>", $displayErrors);
                if (count($errorsEncountered) > 10) {
                    $errorMessage .= "<br>...dan " . (count($errorsEncountered) - 10) . " kesalahan lainnya (lihat log untuk detail).";
                }
                 return redirect()->route('admin.siswa.index')
                             ->with('warning', implode(" ", $messages)) // Menggunakan warning jika ada yg diskip
                             ->with('import_errors', $errorMessage); // Kirim detail error terpisah
            }

            if ($importedCount > 0 && $skippedCount == 0) {
                 return redirect()->route('admin.siswa.index')->with('success', implode(" ", $messages));
            } elseif ($importedCount == 0 && $skippedCount > 0) {
                 return redirect()->route('admin.siswa.index')->with('error', implode(" ", $messages));
            } elseif ($importedCount > 0 && $skippedCount > 0) {
                 return redirect()->route('admin.siswa.index')->with('warning', implode(" ", $messages));
            } else {
                 return redirect()->route('admin.siswa.index')->with('info', 'Tidak ada data siswa yang diproses dari file.');
            }


        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(", ", $failure->errors()) . " (Nilai: " . implode(', ', $failure->values()) . ")";
            }
            return redirect()->back()
                         ->with('error', 'Gagal mengimpor data. Terdapat kesalahan validasi pada file CSV.')
                         ->with('import_validation_errors', $errorMessages)
                         ->withInput();
        } catch (\Exception $e) {
            Log::error('Error importing siswa: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->back()
                         ->with('error', 'Terjadi kesalahan saat mengimpor data siswa: ' . $e->getMessage())
                         ->withInput();
        }
    }
}