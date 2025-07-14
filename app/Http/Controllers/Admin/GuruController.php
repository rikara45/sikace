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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class GuruController extends Controller
{
    public function update(UpdateGuruRequest $request, Guru $guru)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $userId = $guru->user_id;
            $userToUpdate = $guru->user;

            if ($userToUpdate) { 
                $userData = [
                    'name' => $validated['nama_guru'],
                    'username' => $validated['username'] ?? null,
                ];

                $currentEmailIsOldNipBased = $guru->getOriginal('nip') && $userToUpdate->email === ($guru->getOriginal('nip') . '@teacher.sikace.internal');
                $nipChanged = $validated['nip'] !== $guru->getOriginal('nip');

                if ($nipChanged && $currentEmailIsOldNipBased) {
                    $newNipBasedEmail = $validated['nip'] . '@teacher.sikace.internal';
                    if (User::where('email', $newNipBasedEmail)->where('id', '!=', $userToUpdate->id)->exists()) {
                        DB::rollBack();
                        return redirect()->back()
                                         ->with('error', "Perubahan NIP akan menghasilkan email internal '{$newNipBasedEmail}' yang sudah digunakan user lain.")
                                         ->withInput();
                    }
                    $userData['email'] = $newNipBasedEmail;
                }
                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }
                $userToUpdate->update($userData);

            } else { 
                $userEmailForCreation = $validated['nip'] . '@teacher.sikace.internal';
                $userPassword = $validated['password'] ?? $validated['nip'];
                
                if (User::where('email', $userEmailForCreation)->exists()) {
                    DB::rollBack();
                    return redirect()->back()
                                     ->with('error', 'Email internal yang akan dibuat untuk guru ini sudah terpakai.')
                                     ->withInput();
                }

                $newUser = User::create([
                    'name' => $validated['nama_guru'],
                    'username' => $validated['username'] ?? null,
                    'email' => $userEmailForCreation,
                    'password' => Hash::make($userPassword),
                    'email_verified_at' => now(),
                ]);
                $newUser->assignRole('guru');
                $userId = $newUser->id;
            }

            $guruDataToUpdate = [
                'nama_guru' => $validated['nama_guru'],
                'nip' => $validated['nip'],
                'user_id' => $userId,
            ];
            $guru->update($guruDataToUpdate);

            if ($request->has('mapel_diampu')) {
                $guru->mataPelajaransDiampu()->sync($request->input('mapel_diampu', []));
            } else {
                $guru->mataPelajaransDiampu()->detach();
            }

            DB::commit();

            return redirect()->route('admin.guru.index')
                             ->with('success', 'Data guru berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat memperbarui data guru: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui data guru: Terjadi kesalahan pada sistem.')
                             ->withInput();
        }
    }
    
    public function index(Request $request)
    {
        $search = $request->input('search');
        $validator = Validator::make($request->only('sort', 'direction'), [
            'sort' => 'sometimes|in:nip,nama_guru',
            'direction' => 'sometimes|in:asc,desc',
        ]);

        if ($validator->fails()) {
            $sort = 'nama_guru';
            $direction = 'asc';
        } else {
            $sort = $request->input('sort', 'nama_guru');
            $direction = $request->input('direction', 'asc');
        }

        $gurus = Guru::with('user')
            ->when($search, function ($query, $search) {
                return $query->where('nama_guru', 'like', "%{$search}%")
                             ->orWhere('nip', 'like', "%{$search}%")
                             ->orWhereHas('user', function ($q) use ($search) {
                                 $q->where('email', 'like', "%{$search}%");
                             });
            })
            ->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        return view('admin.guru.index', compact('gurus'));
    }

    public function create()
    {
        return view('admin.guru.create');
    }
    
    public function store(StoreGuruRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated(); 

            $userEmailForCreation = $validated['nip'] . '@teacher.sikace.internal';
            $userPassword = $validated['nip']; 

            if (User::where('email', $userEmailForCreation)->exists()) {
                DB::rollBack();
                Log::error("Email internal yang dihasilkan '{$userEmailForCreation}' untuk guru dengan NIP '{$validated['nip']}' sudah ada.");
                return redirect()->back()
                                 ->with('error', 'Gagal membuat akun untuk guru. NIP ini mungkin sudah terasosiasi dengan akun lain melalui email internal.')
                                 ->withInput();
            }

            $user = User::create([
                'name' => $validated['nama_guru'],
                'username' => $validated['username'] ?? null,
                'email' => $userEmailForCreation,
                'password' => Hash::make($userPassword),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('guru');
            $userId = $user->id;
            
            Guru::create([
                'nama_guru' => $validated['nama_guru'],
                'nip' => $validated['nip'], 
                'user_id' => $userId,
            ]);

            DB::commit();

            return redirect()->route('admin.guru.index')
                             ->with('success', 'Data guru berhasil ditambahkan. Akun login telah dibuat otomatis dengan NIP sebagai password awal.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Kesalahan saat menyimpan data guru baru: ' . $e->getMessage() . ' Stack: ' . $e->getTraceAsString());
            return redirect()->back()
                             ->with('error', 'Gagal menambahkan data guru: Terjadi kesalahan pada sistem.')
                             ->withInput();
        }
    }
    
    public function show(Guru $guru)
    {
        $guru->load('user');
        $teachingAssignments = DB::table('kelas_mata_pelajaran as kmp')
            ->join('mata_pelajarans as mp', 'kmp.mata_pelajaran_id', '=', 'mp.id')
            ->join('kelas as k', 'kmp.kelas_id', '=', 'k.id')
            ->where('kmp.guru_id', $guru->id)
            ->select('mp.nama_mapel', 'mp.kode_mapel', 'k.nama_kelas', 'k.tahun_ajaran', 'k.id as kelas_id_for_link')
            ->orderBy('mp.nama_mapel', 'asc') 
            ->orderBy('k.tahun_ajaran', 'desc') 
            ->orderBy('k.nama_kelas', 'asc')     
            ->get();

        return view('admin.guru.show', compact('guru', 'teachingAssignments'));
    }

    public function edit(Guru $guru)
    {
        $guru->load('user');
        $semuaMapel = MataPelajaran::orderBy('nama_mapel')->get();
        $mapelDiampuIds = $guru->mataPelajaransDiampu()->pluck('mata_pelajarans.id')->toArray();

        return view('admin.guru.edit', compact('guru', 'semuaMapel', 'mapelDiampuIds'));
    }

    public function destroy(Guru $guru)
    {
        DB::beginTransaction();
        try {
             if ($guru->user) {
                 $guru->user->delete();
             }
             DB::table('kelas_mata_pelajaran')->where('guru_id', $guru->id)->delete();
             DB::table('kelas')->where('wali_kelas_id', $guru->id)->update(['wali_kelas_id' => null]);
             DB::table('bobot_penilaians')->where('guru_id', $guru->id)->delete();
             $guru->delete();
             DB::commit();

            return redirect()->route('admin.guru.index')
                             ->with('success', 'Data guru berhasil dihapus.');
        } catch (\Exception $e) {
             DB::rollBack();
             Log::error('Kesalahan saat menghapus guru: '.$e->getMessage(). ' Stack: '.$e->getTraceAsString());
             if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                 return redirect()->route('admin.guru.index')
                                  ->with('error', 'Gagal menghapus guru. Pastikan guru tidak terdaftar sebagai wali kelas atau memiliki relasi data lain yang belum ditangani.');
             }
             return redirect()->route('admin.guru.index')
                              ->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }
}