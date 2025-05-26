<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bobot_penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajarans')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade'); // Bobot spesifik per kelas
            $table->string('tahun_ajaran', 9);
            // Persentase bobot, simpan sebagai integer (0-100) atau decimal
            $table->unsignedTinyInteger('bobot_tugas')->default(30); // Contoh default
            $table->unsignedTinyInteger('bobot_uts')->default(30);   // Contoh default
            $table->unsignedTinyInteger('bobot_uas')->default(40);    // Contoh default
            $table->timestamps();

            // Pastikan kombinasi unik untuk mencegah duplikasi bobot
            $table->unique(['guru_id', 'mata_pelajaran_id', 'kelas_id', 'tahun_ajaran'], 'bobot_unik_constraint');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bobot_penilaians');
    }
};
