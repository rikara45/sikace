<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('mata_pelajaran_id');
            $table->unsignedBigInteger('guru_id'); // Guru pengampu mapel di kelas ini
            $table->string('tahun_ajaran', 9);
            $table->timestamps();

            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajarans')->onDelete('cascade');
             $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');


            // Unique constraint per tahun ajaran
            $table->unique(['kelas_id', 'mata_pelajaran_id', 'guru_id', 'tahun_ajaran'], 'kelas_mapel_guru_tahun_unique');
             $table->index('tahun_ajaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_mata_pelajaran');
    }
};
