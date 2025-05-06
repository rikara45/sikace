<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Jika perlu permission spesifik

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat Roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'guru']);
        Role::create(['name' => 'siswa']);

        // Contoh jika ingin membuat permission spesifik (opsional)
        // Permission::create(['name' => 'manage users']);
        // Permission::create(['name' => 'input nilai']);
        // Permission::create(['name' => 'view nilai']);

        // Berikan permission ke role (opsional)
        // $roleAdmin = Role::findByName('admin');
        // $roleAdmin->givePermissionTo(Permission::all());

         // $roleGuru = Role::findByName('guru');
         // $roleGuru->givePermissionTo(['input nilai', 'view nilai']); // Contoh

        // $roleSiswa = Role::findByName('siswa');
        // $roleSiswa->givePermissionTo('view nilai'); // Contoh
    }
}