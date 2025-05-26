<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bobot_penilaians', function (Blueprint $table) {
            $table->unsignedTinyInteger('kkm')->default(70)->after('bobot_uas'); // KKM keseluruhan mapel
            $table->unsignedTinyInteger('batas_a')->default(85)->after('kkm'); // Batas bawah nilai A
            $table->unsignedTinyInteger('batas_b')->default(75)->after('batas_a'); // Batas bawah nilai B
            $table->unsignedTinyInteger('batas_c')->default(65)->after('batas_b'); // Batas bawah nilai C
            // Predikat D akan otomatis di bawah batas_c
        });
    }

    public function down(): void
    {
        Schema::table('bobot_penilaians', function (Blueprint $table) {
            $table->dropColumn(['kkm', 'batas_a', 'batas_b', 'batas_c']);
        });
    }
};