<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //  Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'rio@sikace.com', 
            'password' => Hash::make('password'), 
            'email_verified_at' => now(), 
        ]);
        $admin->assignRole('admin'); 

        // Guru
        $guru = User::create([
            'name' => 'Budi Guru',
            'email' => 'guru@enilai.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $guru->assignRole('guru');
        \App\Models\Guru::create([
            'user_id' => $guru->id,
            'nama_guru' => $guru->name,
            'nip' => '123456789' 
        ]);

        // Siswa
        $siswa = User::create([
            'name' => 'Ani Siswa',
            'email' => 'siswa@enilai.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $siswa->assignRole('siswa');
        \App\Models\Siswa::create([
             'user_id' => $siswa->id,
             'nama_siswa' => $siswa->name,
             'nis' => '1001', 
        
        ]);

    }
}