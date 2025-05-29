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

class GuruController extends Controller
{
    // ... (method index, create, store, edit, update, destroy tetap sama)

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
            $userId = null;

            if (!empty($validated['email'])) {
                $user = User::create([
                    'name' => $validated['nama_guru'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'email_verified_at' => now(),
                ]);
                $user->assignRole('guru');
                $userId = $user->id;
            }

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
            return redirect()->back()
                             ->with('error', 'Gagal menambahkan data guru: ' . $e->getMessage())
                             ->withInput();
        }
    }

    public function show(Guru $guru)
    {
        $teachingAssignments = DB::table('kelas_mata_pelajaran as kmp')
            ->join('mata_pelajarans as mp', 'kmp.mata_pelajaran_id', '=', 'mp.id')
            ->join('kelas as k', 'kmp.kelas_id', '=', 'k.id')
            ->where('kmp.guru_id', $guru->id)
            ->select('mp.nama_mapel', 'mp.kode_mapel', 'k.nama_kelas', 'k.tahun_ajaran', 'k.id as kelas_id_for_link')
            ->orderBy('mp.nama_mapel', 'asc') // Urutkan berdasarkan nama mapel dulu
            ->orderBy('k.tahun_ajaran', 'desc') // Lalu tahun ajaran
            ->orderBy('k.nama_kelas', 'asc')     // Lalu nama kelas
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

    public function update(UpdateGuruRequest $request, Guru $guru)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $userId = $guru->user_id;

            if (!empty($validated['email'])) {
                if ($guru->user) {
                    $updateData = [
                        'name' => $validated['nama_guru'],
                        'email' => $validated['email'],
                    ];
                    if (!empty($validated['password'])) {
                        $updateData['password'] = Hash::make($validated['password']);
                    }
                    $guru->user->update($updateData);
                } else {
                    $user = User::create([
                        'name' => $validated['nama_guru'],
                        'email' => $validated['email'],
                        'password' => !empty($validated['password']) ? Hash::make($validated['password']) : Hash::make('password'),
                        'email_verified_at' => now(),
                    ]);
                    $user->assignRole('guru');
                    $userId = $user->id;
                }
            }

            $guru->update([
                'nama_guru' => $validated['nama_guru'],
                'nip' => $validated['nip'] ?? null,
                'user_id' => $userId,
            ]);

            DB::commit();

            return redirect()->route('admin.guru.index')
                             ->with('success', 'Data guru berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Gagal memperbarui data guru: ' . $e->getMessage())
                             ->withInput();
        }
    }


    public function destroy(Guru $guru)
    {
        DB::beginTransaction();
        try {
             if ($guru->user) {
                 $guru->user->delete();
             }
             DB::table('kelas_mata_pelajaran')->where('guru_id', $guru->id)->delete();
             $guru->delete();
             DB::commit();

            return redirect()->route('admin.guru.index')
                             ->with('success', 'Data guru berhasil dihapus.');
        } catch (\Exception $e) {
             DB::rollBack();
             if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                 return redirect()->route('admin.guru.index')
                                  ->with('error', 'Gagal menghapus guru. Pastikan guru tidak terdaftar sebagai wali kelas atau memiliki relasi data lain.');
             }
             return redirect()->route('admin.guru.index')
                              ->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }
}