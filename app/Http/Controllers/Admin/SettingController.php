<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan; // Untuk clear cache jika perlu

class SettingController extends Controller
{
    public function index()
    {
        $tahunAjaranAktif = Setting::getValue('tahun_ajaran_aktif', '2025/2026');
        $semesterAktif = Setting::getValue('semester_aktif', '1');

        // Opsi untuk dropdown Tahun Ajaran (bisa dibuat lebih dinamis)
        // Contoh sederhana, Anda bisa mengambilnya dari tabel 'kelas' atau tabel 'tahun_ajarans' terpisah jika ada
        $availableTahunAjaran = [];
        $startYear = 2024; // Tahun awal
        for ($i = 0; $i < 5; $i++) { // Generate 5 tahun ke depan
            $nextYear = $startYear + 1 + $i;
            $availableTahunAjaran[] = ($startYear + $i) . '/' . $nextYear;
        }

        $availableSemester = [1, 2];

        return view('admin.settings.index', compact(
            'tahunAjaranAktif',
            'semesterAktif',
            'availableTahunAjaran',
            'availableSemester'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'tahun_ajaran_aktif' => ['required', 'string', 'max:9', 'regex:/^\d{4}\/\d{4}$/'],
            'semester_aktif' => ['required', 'integer', 'in:1,2'],
        ]);

        Setting::setValue('tahun_ajaran_aktif', 'Tahun Ajaran Aktif', $validated['tahun_ajaran_aktif']);
        Setting::setValue('semester_aktif', 'Semester Aktif', $validated['semester_aktif'], 'integer');

        // Opsional: Clear cache config agar perubahan langsung terasa
        // Artisan::call('config:clear');
        // Artisan::call('cache:clear');

        return redirect()->route('admin.settings.index')
                         ->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
