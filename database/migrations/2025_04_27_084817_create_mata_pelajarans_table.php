<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_pelajarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel', 10)->unique()->nullable(); // Kode unik mata pelajaran (opsional)
            $table->string('nama_mapel', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mata_pelajarans');
    }
};
