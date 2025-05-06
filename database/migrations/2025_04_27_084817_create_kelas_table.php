<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas', 50); // Contoh: 'X IPA 1', 'XI IPS 2'
            $table->unsignedBigInteger('wali_kelas_id')->nullable(); // Foreign key ke tabel guru (wali kelas)
            $table->string('tahun_ajaran', 9); // Contoh: '2024/2025'
            $table->timestamps();

            // Optional: Foreign key constraint ke tabel guru (jika tabel guru sudah ada)
            // $table->foreign('wali_kelas_id')->references('id')->on('gurus')->onDelete('set null');
             $table->index('tahun_ajaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
