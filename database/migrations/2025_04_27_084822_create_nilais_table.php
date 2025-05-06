<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('mata_pelajaran_id');
            $table->unsignedBigInteger('kelas_id'); // Kelas saat nilai diinput
            $table->unsignedBigInteger('guru_id'); // Guru yang menginput/mengajar mapel tsb
            $table->string('tahun_ajaran', 9);
            $table->integer('semester'); // 1 atau 2
            $table->decimal('nilai_tugas', 5, 2)->nullable(); // Nilai 0.00 - 100.00
            $table->decimal('nilai_uts', 5, 2)->nullable();   // Nilai Mid Semester
            $table->decimal('nilai_uas', 5, 2)->nullable();   // Nilai Akhir Semester
            $table->decimal('nilai_akhir', 5, 2)->nullable(); // Hasil kalkulasi
            $table->string('predikat', 2)->nullable(); // A, B, C, D, E
            $table->text('catatan')->nullable(); // Catatan dari guru
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswas')->onDelete('cascade');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajarans')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');

            // Index untuk performa query
            $table->index(['siswa_id', 'mata_pelajaran_id', 'tahun_ajaran', 'semester']);
            $table->index('tahun_ajaran');
            $table->index('semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};
