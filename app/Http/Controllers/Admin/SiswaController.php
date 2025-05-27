<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\StoreSiswaRequest;
use App\Http\Requests\Admin\UpdateSiswaRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
}