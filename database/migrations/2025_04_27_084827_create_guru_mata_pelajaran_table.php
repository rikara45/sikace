<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_id');
            $table->unsignedBigInteger('mata_pelajaran_id');
            // Tambahkan info lain jika perlu, misal tahun ajaran relasi ini berlaku
            // $table->string('tahun_ajaran', 9)->nullable();
            $table->timestamps();

            $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajarans')->onDelete('cascade');

            // Unique constraint agar pasangan guru & mapel tidak duplikat
            $table->unique(['guru_id', 'mata_pelajaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_mata_pelajaran');
    }
};
