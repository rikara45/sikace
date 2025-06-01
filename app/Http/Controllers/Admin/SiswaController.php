<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use App\Http\Requests\Admin\StoreSiswaRequest;
use App\Http\Requests\Admin\UpdateSiswaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $sort = $request->input('sort', 'nama_siswa'); // Default sort by nama_siswa
        $direction = $request->input('direction', 'asc');
        $kelasIds = $request->input('kelas_ids', []);
        $statusFilter = $request->input('status', Siswa::STATUS_AKTIF); // Default filter status aktif

        $siswas = Siswa::with('kelas')
            ->when($search, function ($query, $search) {
                return $query->where('nama_siswa', 'like', "%{$search}%")
                             ->orWhere('nis', 'like', "%{$search}%");
            })
            ->when(!empty($kelasIds), function ($query) use ($kelasIds) {
                return $query->whereIn('kelas_id', $kelasIds);
            })
            ->when($statusFilter, function ($query, $statusFilter) { // Filter berdasarkan status
                return $query->where('status', $statusFilter);
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $kelas = Kelas::orderBy('nama_kelas')->get();
        $allStatus = [
            Siswa::STATUS_AKTIF => 'Aktif',
            Siswa::STATUS_LULUS => 'Lulus',
            Siswa::STATUS_PINDAH => 'Pindah',
            Siswa::STATUS_DIKELUARKAN => 'Dikeluarkan',
            // Tambahkan status lain jika ada
        ];

        // Kirim $allStatus dan $statusFilter ke view
        return view('admin.siswa.index', compact('siswas', 'kelas', 'allStatus', 'statusFilter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $allStatus = [
            Siswa::STATUS_AKTIF => 'Aktif',
            Siswa::STATUS_LULUS => 'Lulus',
            Siswa::STATUS_PINDAH => 'Pindah',
            Siswa::STATUS_DIKELUARKAN => 'Dikeluarkan',
        ];
        return view('admin.siswa.create', compact('kelas', 'allStatus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiswaRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $pseudoEmail = $validated['nis'] . '@internal.siswa';

            $user = User::create([
                'name' => $validated['nama_siswa'],
                'email' => $pseudoEmail,
                'password' => Hash::make($validated['nis']),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('siswa');

            // Pastikan status default 'aktif' jika tidak ada input
            $status = $validated['status'] ?? Siswa::STATUS_AKTIF;

            Siswa::create([
                'user_id' => $user->id,
                'nama_siswa' => $validated['nama_siswa'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'kelas_id' => $validated['kelas_id'],
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
                'status' => $status,
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
         $siswa->load('kelas', 'user', 'nilais.mataPelajaran');
        return view('admin.siswa.show', compact('siswa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Siswa $siswa)
    {
         $siswa->load('user');
         $kelas = Kelas::orderBy('nama_kelas')->get();
         $allStatus = [
            Siswa::STATUS_AKTIF => 'Aktif',
            Siswa::STATUS_LULUS => 'Lulus',
            Siswa::STATUS_PINDAH => 'Pindah',
            Siswa::STATUS_DIKELUARKAN => 'Dikeluarkan',
         ];
        return view('admin.siswa.edit', compact('siswa', 'kelas', 'allStatus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiswaRequest $request, Siswa $siswa)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $siswaData = [
                'nama_siswa' => $validated['nama_siswa'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'] ?? null,
                'kelas_id' => $validated['kelas_id'],
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
                'status' => $request->input('status', $siswa->status), // Ambil status dari request
            ];
            $siswa->update($siswaData);

            if ($siswa->user) {
                $userDataToUpdate = ['name' => $validated['nama_siswa']];
                if ($siswa->isDirty('nis')) {
                     $userDataToUpdate['email'] = $validated['nis'] . '@internal.siswa';
                }
                // Jika status siswa diubah menjadi bukan 'aktif', mungkin nonaktifkan user
                if ($siswaData['status'] !== Siswa::STATUS_AKTIF) {
                    // Logika untuk menonaktifkan user, misal:
                    // $siswa->user->update(['is_active' => false]); // Jika ada kolom is_active di tabel users
                    // Atau hapus role siswa agar tidak bisa login ke dashboard siswa
                    // $siswa->user->removeRole('siswa');
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
     * Ubah method destroy untuk update status siswa menjadi 'lulus' atau status lain.
     * Jika benar-benar ingin menghapus, mungkin perlu fitur terpisah atau konfirmasi berlapis.
     */
    public function destroy(Siswa $siswa)
    {
        DB::beginTransaction();
        try {
            // Untuk siswa lulus, ubah statusnya
            $siswa->update(['status' => Siswa::STATUS_LULUS]);
            // Anda bisa juga menonaktifkan user login nya di sini jika perlu
            // if ($siswa->user) {
            //     $siswa->user->update(['is_active' => false]);
            //     // $siswa->user->removeRole('siswa');
            // }

            DB::commit();

            return redirect()->route('admin.siswa.index')
                         ->with('success', 'Status siswa ' . $siswa->nama_siswa . ' berhasil diubah menjadi Lulus.');
        } catch (\Exception $e) {
             DB::rollBack();
             return redirect()->route('admin.siswa.index')
                          ->with('error', 'Gagal mengubah status siswa: ' . $e->getMessage());
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
                $displayErrors = array_slice($errorsEncountered, 0, 10);
                $errorMessage = "Detail kesalahan: <br>" . implode("<br>", $displayErrors);
                if (count($errorsEncountered) > 10) {
                    $errorMessage .= "<br>...dan " . (count($errorsEncountered) - 10) . " kesalahan lainnya (lihat log untuk detail).";
                }
                 return redirect()->route('admin.siswa.index')
                             ->with('warning', implode(" ", $messages))
                             ->with('import_errors', $errorMessage);
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