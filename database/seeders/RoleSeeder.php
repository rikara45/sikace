<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Jika Anda juga membuat permission

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
        // Gunakan firstOrCreate untuk menghindari error jika role sudah ada
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'guru', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']);

        // Contoh jika Anda juga membuat permission dan mengassignnya
        // $adminRole = Role::where('name', 'admin')->first();
        // $guruRole = Role::where('name', 'guru')->first();
        // $siswaRole = Role::where('name', 'siswa')->first();

        // $manageUsersPermission = Permission::firstOrCreate(['name' => 'manage users', 'guard_name' => 'web']);
        // $inputNilaiPermission = Permission::firstOrCreate(['name' => 'input nilai', 'guard_name' => 'web']);
        // $viewNilaiPermission = Permission::firstOrCreate(['name' => 'view nilai', 'guard_name' => 'web']);

        // if ($adminRole && $manageUsersPermission) {
        //     $adminRole->givePermissionTo($manageUsersPermission);
        // }
        // if ($guruRole && $inputNilaiPermission) {
        //    $guruRole->givePermissionTo($inputNilaiPermission);
        // }
        // ... dan seterusnya ...
    }
}