<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas; // Import model Kelas
use App\Models\User; // Import model User
use App\Http\Requests\Admin\StoreSiswaRequest; // Import request
use App\Http\Requests\Admin\UpdateSiswaRequest; // Import request
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Import Hash
use Illuminate\Support\Facades\DB; // Import DB facade for transactions

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Ambil data siswa dengan pagination dan relasi kelas
        // Tambahkan fitur search jika perlu
        $search = $request->input('search');
        $siswas = Siswa::with('kelas') // Eager load relasi kelas
            ->when($search, function ($query, $search) {
                return $query->where('nama_siswa', 'like', "%{$search}%")
                             ->orWhere('nis', 'like', "%{$search}%");
            })
            ->latest() // Urutkan berdasarkan terbaru
            ->paginate(10); // Tampilkan 10 data per halaman

        return view('admin.siswa.index', compact('siswas'));
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
        // Gunakan transaction untuk memastikan konsistensi data User dan Siswa
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $userId = null;

            // 1. Buat User jika email diisi
            if (!empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['nama_siswa'],
                    'email' => $validated['email'],
                    // Berikan password default atau dari input, jangan lupa hash
                    'password' => isset($validated['password']) ? Hash::make($validated['password']) : Hash::make('password'), // Contoh password default 'password'
                    'email_verified_at' => now(), // Anggap langsung verified
                ]);
                $user->assignRole('siswa'); // Assign role siswa
                $userId = $user->id;
            }

            // 2. Buat data Siswa
            Siswa::create([
                'nama_siswa' => $validated['nama_siswa'],
                'nis' => $validated['nis'],
                'nisn' => $validated['nisn'],
                'kelas_id' => $validated['kelas_id'],
                'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
                'user_id' => $userId, // Masukkan user_id jika user dibuat
            ]);

            DB::commit(); // Simpan perubahan jika semua berhasil

            return redirect()->route('admin.siswa.index')
                             ->with('success', 'Data siswa berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan perubahan jika terjadi error
            // Log error $e->getMessage()
            return redirect()->back()
                             ->with('error', 'Gagal menambahkan data siswa: ' . $e->getMessage())
                             ->withInput(); // Kembalikan input sebelumnya
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
        DB::beginTransaction();
        try {
            $validated = $request->validated();
             $userId = $siswa->user_id; // Ambil user_id yang sudah ada

             // 1. Update atau Buat User jika perlu
             if (!empty($validated['email'])) {
                 if ($siswa->user) { // Jika siswa sudah punya akun user
                     $siswa->user->update([
                         'name' => $validated['nama_siswa'],
                         'email' => $validated['email'],
                         // Hanya update password jika diisi
                         'password' => !empty($validated['password']) ? Hash::make($validated['password']) : $siswa->user->password,
                     ]);
                 } else { // Jika siswa belum punya akun user, buat baru
                     $user = User::create([
                         'name' => $validated['nama_siswa'],
                         'email' => $validated['email'],
                         'password' => !empty($validated['password']) ? Hash::make($validated['password']) : Hash::make('password'),
                         'email_verified_at' => now(),
                     ]);
                     $user->assignRole('siswa');
                     $userId = $user->id; // Update userId untuk disimpan di tabel siswa
                 }
             } elseif ($siswa->user && empty($validated['email'])) {
                 // Jika email dikosongkan & sebelumnya ada user, mungkin hapus user? (Optional, perlu konfirmasi)
                 // $siswa->user->delete();
                 // $userId = null;
             }


             // 2. Update data Siswa
            $siswa->update([
                 'nama_siswa' => $validated['nama_siswa'],
                 'nis' => $validated['nis'],
                 'nisn' => $validated['nisn'],
                 'kelas_id' => $validated['kelas_id'],
                 'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
                 'user_id' => $userId, // Update user_id
             ]);

             DB::commit();

            return redirect()->route('admin.siswa.index')
                             ->with('success', 'Data siswa berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui data siswa: ' . $e->getMessage())
                             ->withInput();
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
}