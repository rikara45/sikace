<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SiswaController as AdminSiswaController;
use App\Http\Controllers\Admin\GuruController as AdminGuruController;
use App\Http\Controllers\Admin\KelasController as AdminKelasController;
use App\Http\Controllers\Admin\MataPelajaranController as AdminMataPelajaranController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\NilaiController as GuruNilaiController;
use App\Http\Controllers\Guru\PengaturanController as GuruPengaturanController;
use App\Http\Controllers\Siswa\DashboardController as SiswaDashboardController;
use App\Http\Controllers\Siswa\NilaiController as SiswaNilaiController;
use App\Http\Controllers\LaporanController; // Tambahkan ini di atas

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Rute untuk Halaman Awal (Guest / Belum Login)
Route::get('/', function () {
    // Arahkan ke login jika belum login, atau ke dashboard jika sudah
    if (Auth::check()) {
         $user = Auth::user();

         // Tambahkan cek tipe User sebelum panggil hasRole
         if ($user instanceof User) {
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('guru')) {
                return redirect()->route('guru.dashboard');
            }
            if ($user->hasRole('siswa')) {
                return redirect()->route('siswa.dashboard');
            }
         }
    }
    // Jika tidak lolos Auth::check() atau tidak ada role cocok di atas
    return view('auth.login');
});

// --- RUTE UNTUK SEMUA USER YANG SUDAH LOGIN ---
Route::middleware('auth')->group(function () {
    // Profile (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Redirect berdasarkan role setelah login (bisa juga dihandle di LoginController atau Middleware)
    Route::get('/dashboard-redirect', function(){
        $user = Auth::user();
        if ($user instanceof User) { // Periksa apakah $user adalah instance dari App\Models\User
            if ($user->hasRole('admin')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('guru')) {
                return redirect()->route('guru.dashboard');
            }
            if ($user->hasRole('siswa')) {
                return redirect()->route('siswa.dashboard');
            }
        }
        // Fallback jika role tidak terdefinisi atau user tidak punya role
         Auth::logout();
         return redirect('/login')->withErrors('Akses tidak valid.');
    })->name('dashboard.redirect');

    // Rute untuk cetak PDF
    // Rute untuk Siswa atau Admin yang melihat rapor siswa
    Route::get('/laporan/rapor-siswa/{siswa}/cetak', [LaporanController::class, 'cetakRaporSiswa'])
        ->name('laporan.rapor.siswa.cetak')
        ->middleware('can:view,siswa'); // Tambahkan policy jika perlu

    // Rute untuk Guru yang mencetak rekap nilai kelas
    Route::get('/laporan/rekap-nilai-kelas/{kelas}/cetak', [LaporanController::class, 'cetakRekapNilaiKelas'])
        ->name('laporan.rekap.nilai.kelas.cetak')
        ->middleware('role:guru'); // Hanya guru

});

// --- RUTE KHUSUS ADMIN ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Manajemen Data (Siswa, Guru, Kelas, Mapel)
    Route::resource('siswa', AdminSiswaController::class);
    // Tambahkan rute untuk import siswa
    Route::get('import', [AdminSiswaController::class, 'showImportForm'])->name('siswa.showImportForm');
    Route::post('import', [AdminSiswaController::class, 'import'])->name('siswa.import');

    Route::resource('guru', AdminGuruController::class);
    Route::resource('kelas', AdminKelasController::class)->parameters([
        'kelas' => 'kelas'
    ]);
    Route::resource('matapelajaran', AdminMataPelajaranController::class)->parameters([
        'matapelajaran' => 'mataPelajaran'
    ]);

    // >> RUTE BARU UNTUK RELASI KELAS-MAPEL-GURU <<
    Route::post('kelas/{kelas}/assign-subject', [AdminKelasController::class, 'assignSubject'])->name('kelas.assignSubject');
    // Gunakan {pivotId} sebagai parameter untuk identifikasi baris pivot yang akan dihapus
    Route::delete('kelas/{kelas}/remove-assignment/{pivotId}', [AdminKelasController::class, 'removeAssignment'])->name('kelas.removeAssignment');

    // Tambahkan rute setting di sini
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Rute untuk Fitur Kenaikan Kelas
    Route::get('/kenaikan-kelas', [\App\Http\Controllers\Admin\KenaikanKelasController::class, 'showForm'])->name('kenaikan.form');
    Route::post('/kenaikan-kelas/proses', [\App\Http\Controllers\Admin\KenaikanKelasController::class, 'processPromotion'])->name('kenaikan.proses');

     // Rute lain untuk admin...
});

// --- RUTE KHUSUS GURU ---
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('dashboard');

    // Halaman utama untuk filter, pengaturan bobot/KKM, dan input nilai
    Route::get('/nilai/input', [GuruNilaiController::class, 'showFormInputNilaiGabungan'])->name('nilai.input'); // Ini akan jadi halaman utama

    // Action untuk menyimpan bobot dan KKM (bisa jadi satu atau tetap dua)
    Route::post('/nilai/simpan-bobot', [GuruNilaiController::class, 'simpanBobot'])->name('nilai.simpanBobot');
    Route::post('/nilai/simpan-kkm', [GuruNilaiController::class, 'simpanKkm'])->name('nilai.simpanKkm'); // Atau gabung ke simpanBobot jika logikanya sama

    // Action untuk menyimpan nilai siswa
    Route::post('/nilai/simpan-nilai-siswa', [GuruNilaiController::class, 'storeNilai'])->name('nilai.store');

    // Rekap Nilai Siswa (tetap sama)
    Route::get('/rekap-nilai', [GuruNilaiController::class, 'showRekapNilaiForm'])->name('rekap-nilai.index');
});

// --- RUTE KHUSUS SISWA ---
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

    // Lihat Nilai
    Route::get('/nilai', [SiswaNilaiController::class, 'index'])->name('nilai.index'); // Tampilkan rapor / ringkasan nilai
    Route::get('/nilai/{matapelajaran_id}', [SiswaNilaiController::class, 'showMapel'])->name('nilai.mapel'); // Lihat detail nilai per mapel
    // Rute lain untuk siswa (misal: lihat jadwal ujian)

});

// Rute Autentikasi Bawaan Breeze (login, register, forgot password, dll)
// Pastikan file routes/auth.php di-include di RouteServiceProvider
require __DIR__.'/auth.php';