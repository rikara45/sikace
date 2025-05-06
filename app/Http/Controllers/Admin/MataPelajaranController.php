<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Http\Requests\Admin\StoreMataPelajaranRequest;
use App\Http\Requests\Admin\UpdateMataPelajaranRequest;
use Illuminate\Http\Request;

class MataPelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $mataPelajarans = MataPelajaran::when($search, function ($query, $search) {
                return $query->where('nama_mapel', 'like', "%{$search}%")
                             ->orWhere('kode_mapel', 'like', "%{$search}%");
            })
            ->orderBy('nama_mapel', 'asc')
            ->paginate(10);
        return view('admin.matapelajaran.index', compact('mataPelajarans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.matapelajaran.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMataPelajaranRequest $request)
    {
        MataPelajaran::create($request->validated());
        return redirect()->route('admin.matapelajaran.index')
                         ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    // Ingat parameter di route: 'matapelajaran' -> model binding jadi $mataPelajaran
    public function show(MataPelajaran $mataPelajaran)
    {
         // Load relasi guru atau kelas jika perlu
         $mataPelajaran->load('gurus'); // Contoh load guru yg mengampu mapel ini
        return view('admin.matapelajaran.show', compact('mataPelajaran'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MataPelajaran $mataPelajaran)
    {
        return view('admin.matapelajaran.edit', compact('mataPelajaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMataPelajaranRequest $request, MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->update($request->validated());
        return redirect()->route('admin.matapelajaran.index')
                         ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MataPelajaran $mataPelajaran)
    {
         try {
             // Cek ketergantungan nilai atau jadwal?
             // if ($mataPelajaran->nilais()->exists() || $mataPelajaran->kelas()->exists()) {
             //     return redirect()->route('admin.matapelajaran.index')
             //                      ->with('error', 'Mata pelajaran tidak dapat dihapus karena masih digunakan dalam data nilai atau jadwal kelas.');
             // }
            $mataPelajaran->delete();
            return redirect()->route('admin.matapelajaran.index')
                             ->with('success', 'Mata pelajaran berhasil dihapus.');
         } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.matapelajaran.index')
                             ->with('error', 'Gagal menghapus mata pelajaran. Pastikan tidak ada data terkait (nilai, jadwal). Error: ' . $e->getCode());
         } catch (\Exception $e) {
             return redirect()->route('admin.matapelajaran.index')
                              ->with('error', 'Gagal menghapus mata pelajaran: ' . $e->getMessage());
         }
    }
}