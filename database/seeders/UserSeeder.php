<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Guru;   // Pastikan model Guru di-import jika Anda membuat data Guru
use App\Models\Siswa; // Pastikan model Siswa di-import jika Anda membuat data Siswa
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Buat User Admin ---
        // Cari user dengan email ini, jika tidak ada, baru buat
        $admin = User::firstOrCreate(
            ['email' => 'rio@sikace.com'], // Kriteria untuk mencari
            [ // Data yang akan digunakan jika user baru dibuat
                'name' => 'Admin User Rio', // Ganti nama jika perlu
                'password' => Hash::make('password'), // Ganti dengan password yang kuat
                'email_verified_at' => now(),
            ]
        );
        // Assign role 'admin' (pastikan RoleSeeder sudah dijalankan sebelumnya atau panggil di DatabaseSeeder)
        // Jika $admin baru dibuat atau sudah ada, kita tetap assign role (assignRole akan handle duplikasi)
        $admin->assignRole('admin');


        // --- Buat Contoh User Guru (Opsional) ---
        $guruUser = User::firstOrCreate(
            ['email' => 'habib@sikace.test'],
            [
                'name' => 'Muhammad Habib',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $guruUser->assignRole('guru');

        // Buat data Guru terkait jika user baru dibuat atau jika belum ada data guru untuk user ini
        Guru::firstOrCreate(
            ['user_id' => $guruUser->id],
            [
                'nama_guru' => $guruUser->name,
                'nip' => 'G1234567890' // Contoh NIP unik
            ]
        );


        // --- Buat Contoh User Siswa (Opsional) ---
        $nisContoh = 'S1001'; // Pastikan unik
        $namaSiswa = 'Ani Siswa';
        $siswaUser = User::firstOrCreate(
            ['email' => $nisContoh . '@internal.siswa'], // Kriteria pencarian (pseudo-email)
            [ // Data jika baru
                'name' => $namaSiswa,
                'password' => Hash::make($nisContoh), // Password adalah NIS
                'email_verified_at' => now(),
            ]
        );
        $siswaUser->assignRole('siswa');

        Siswa::firstOrCreate(
            ['nis' => $nisContoh], // Kriteria pencarian siswa berdasarkan NIS
            [ // Data jika baru
                 'user_id' => $siswaUser->id,
                 'nama_siswa' => $namaSiswa,
                 // 'kelas_id' => 1 // Pastikan kelas dengan ID 1 sudah ada
            ]
        );

        // Anda bisa menambahkan user lain dengan cara yang sama
    }
}