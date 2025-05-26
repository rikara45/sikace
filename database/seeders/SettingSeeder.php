<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setValue('tahun_ajaran_aktif', 'Tahun Ajaran Aktif', '2025/2026', 'string');
        Setting::setValue('semester_aktif', 'Semester Aktif', '1', 'integer');
    }
}