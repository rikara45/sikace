<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Contoh: 'tahun_ajaran_aktif', 'semester_aktif'
            $table->string('value')->nullable();
            $table->string('label')->nullable(); // Deskripsi setting
            $table->string('type')->default('string'); // Tipe data (string, integer, boolean)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
