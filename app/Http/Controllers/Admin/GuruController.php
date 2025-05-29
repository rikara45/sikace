<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use App\Models\MataPelajaran;
use App\Http\Requests\Admin\StoreGuruRequest;
use App\Http\Requests\Admin\UpdateGuruRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // <-- Tambahkan ini

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // 1. Ambil input search
        $search = $request->input('search');

        // 2. Validasi dan ambil input sort & direction
        $validator = Validator::make($request->only('sort', 'direction'), [
            'sort' => 'sometimes|in:nip,nama_guru', // Hanya izinkan kolom ini
            'direction' => 'sometimes|in:asc,desc', // Hanya izinkan asc atau desc
        ]);

        // Jika validasi gagal atau input tidak ada, gunakan default
        if ($validator->fails()) {
            $sort = 'nama_guru'; // Default sort
            $direction = 'asc';     // Default direction
        } else {
            $sort = $request->input('sort', 'nama_guru');
            $direction = $request->input('direction', 'asc');
        }


        // 3. Bangun query
        $gurus = Guru::with('user') // Eager load relasi user
            ->when($search, function ($query, $search) {
                // Terapkan pencarian jika ada
                return $query->where('nama_guru', 'like', "%{$search}%")
                             ->orWhere('nip', 'like', "%{$search}%")
                             ->orWhereHas('user', function ($q) use ($search) {
                                 $q->where('email', 'like', "%{$search}%");
                             });
            })
            // 4. Terapkan sorting (Ganti ->latest())
            ->orderBy($sort, $direction)
            // 5. Lakukan pagination dan pertahankan query string
            ->paginate(10)
            ->withQueryString(); // <-- Tambahkan ini!

        // 6. Kembalikan view dengan data
        return view('admin.guru.index', compact('gurus'));
    }

    // ... (Metode lainnya tetap sama) ...

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
        $guru->load('mataPelajaransDiampu');
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

            // 1. Update atau Buat User
            if (!empty($validated['email'])) {
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
                        'password' => !empty($validated['password']) ? Hash::make($validated['password']) : Hash::make('password'), // Beri password default jika kosong
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('guru');
                    $userId = $user->id;
                }
            } elseif ($guru->user && empty($validated['email'])) {
                 // Jika email dikosongkan (misalnya tidak diizinkan), Anda bisa menambahkan logic di sini
                 // Untuk saat ini, kita anggap email tidak bisa dikosongkan jika sudah ada.
                 // Atau jika bisa, mungkin user-nya perlu dihapus? Ini tergantung kebutuhan.
            }

            // 2. Update data Guru
            $guru->update([
                'nama_guru' => $validated['nama_guru'],
                'nip' => $validated['nip'] ?? null,
                'user_id' => $userId,
            ]);

            // 3. Sinkronisasi Mata Pelajaran yang Diampu
            $mapelIds = $request->input('mapel_diampu', []);
            $guru->mataPelajaransDiampu()->sync($mapelIds);

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
            // Hapus user terkait jika ada
             if ($guru->user) {
                 $guru->user->delete();
             }

             // Hapus relasi pivot
             $guru->mataPelajaransDiampu()->detach();

             // Hapus data guru
             $guru->delete();

             DB::commit();

            return redirect()->route('admin.guru.index')
                             ->with('success', 'Data guru berhasil dihapus.');
        } catch (\Exception $e) {
             DB::rollBack();
             // Log::error('Error delete guru: ' . $e->getMessage());
             if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                 return redirect()->route('admin.guru.index')
                                  ->with('error', 'Gagal menghapus guru. Pastikan guru tidak terdaftar sebagai wali kelas atau memiliki relasi data lain.');
             }
             return redirect()->route('admin.guru.index')
                              ->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }
}