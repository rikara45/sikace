<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use App\Models\MataPelajaran; // <-- Import MataPelajaran
use App\Http\Requests\Admin\StoreGuruRequest;
use App\Http\Requests\Admin\UpdateGuruRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $gurus = Guru::with('user') // Eager load relasi user jika ada
            ->when($search, function ($query, $search) {
                return $query->where('nama_guru', 'like', "%{$search}%")
                             ->orWhere('nip', 'like', "%{$search}%")
                             ->orWhereHas('user', function ($q) use ($search) { // Cari berdasarkan email user juga
                                 $q->where('email', 'like', "%{$search}%");
                             });
            })
            ->latest()
            ->paginate(10);

        return view('admin.guru.index', compact('gurus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.guru.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGuruRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $userId = null;

            // 1. Buat User jika email diisi
            if (!empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['nama_guru'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']), // Password sudah divalidasi required_with
                    'email_verified_at' => now(),
                ]);
                $user->assignRole('guru'); // Assign role guru
                $userId = $user->id;
            }

            // 2. Buat data Guru
            Guru::create([
                'nama_guru' => $validated['nama_guru'],
                'nip' => $validated['nip'] ?? null,
                'user_id' => $userId,
            ]);

            DB::commit();

            return redirect()->route('admin.guru.index')
                             ->with('success', 'Data guru berhasil ditambahkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error store guru: ' . $e->getMessage()); // Sebaiknya di-log
            return redirect()->back()
                             ->with('error', 'Gagal menambahkan data guru: ' . $e->getMessage())
                             ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Guru $guru)
    {
        // Load relasi yang mungkin ingin ditampilkan
        $guru->load('user', 'kelasWali', 'mataPelajaransDiampu');
        return view('admin.guru.show', compact('guru'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guru $guru)
    {
        $guru->load('user');
        $semuaMapel = MataPelajaran::orderBy('nama_mapel')->get(); // Ambil semua mapel
        // Ambil ID mapel yang sudah diampu guru ini
        $mapelDiampuIds = $guru->mataPelajaransDiampu()->pluck('mata_pelajarans.id')->toArray();

        return view('admin.guru.edit', compact('guru', 'semuaMapel', 'mapelDiampuIds')); // Kirim data mapel ke view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGuruRequest $request, Guru $guru) // Gunakan UpdateGuruRequest
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated(); // Validasi data guru & user
            $userId = $guru->user_id;

            // 1. Update atau Buat User (Logic sama seperti sebelumnya)
            if (!empty($validated['email'])) {
                // ... (logic update/create user seperti sebelumnya) ...
                 if ($guru->user) { // Jika guru sudah punya akun user
                    $updateData = [
                        'name' => $validated['nama_guru'],
                        'email' => $validated['email'],
                    ];
                    if (!empty($validated['password'])) {
                        $updateData['password'] = Hash::make($validated['password']);
                    }
                    $guru->user->update($updateData);
                 } else { // Jika guru belum punya akun user, buat baru
                    $user = User::create([
                        'name' => $validated['nama_guru'],
                        'email' => $validated['email'],
                        'password' => !empty($validated['password']) ? Hash::make($validated['password']) : Hash::make('password'),
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('guru');
                    $userId = $user->id;
                 }
            } elseif ($guru->user && empty($validated['email'])) {
                // Handle jika email dikosongkan? (Sama seperti sebelumnya)
            }

            // 2. Update data Guru
            $guru->update([
                'nama_guru' => $validated['nama_guru'],
                'nip' => $validated['nip'] ?? null,
                'user_id' => $userId,
            ]);

            // 3. Sinkronisasi Mata Pelajaran yang Diampu <-- TAMBAHAN
            // Ambil array ID mapel dari request (jika tidak ada, default ke array kosong)
            $mapelIds = $request->input('mapel_diampu', []);
            $guru->mataPelajaransDiampu()->sync($mapelIds); // Sync akan otomatis menambah/menghapus relasi di pivot table

            DB::commit();

            return redirect()->route('admin.guru.index')
                             ->with('success', 'Data guru dan mata pelajaran yang diampu berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Log::error('Error update guru: ' . $e->getMessage());
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui data guru: ' . $e->getMessage())
                             ->withInput();
        }
    }

     /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guru $guru)
    {
        DB::beginTransaction();
        try {
            // Hapus user terkait jika ada (opsional, tergantung kebijakan)
            // Pertimbangkan apa yang terjadi jika guru ini adalah wali kelas atau pengampu mapel
            // Mungkin perlu validasi tambahan atau set foreign key ke null
             if ($guru->user) {
                 // Sebelum hapus user, pastikan role-nya hanya 'guru' atau handle jika punya role lain
                 // $guru->user->syncRoles([]); // Hapus semua role jika perlu
                 $guru->user->delete();
             }

             // Hapus relasi pivot (misal: mata pelajaran yang diampu)
             $guru->mataPelajaransDiampu()->detach();

             // Hapus data guru
             $guru->delete();

             DB::commit();

            return redirect()->route('admin.guru.index')
                         ->with('success', 'Data guru berhasil dihapus.');
        } catch (\Exception $e) {
             DB::rollBack();
             // Log::error('Error delete guru: ' . $e->getMessage());
             // Cek constraint violation error (misal: jika guru masih jadi wali kelas)
             if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                 return redirect()->route('admin.guru.index')
                              ->with('error', 'Gagal menghapus guru. Pastikan guru tidak terdaftar sebagai wali kelas atau memiliki relasi data lain.');
             }
             return redirect()->route('admin.guru.index')
                          ->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }
}