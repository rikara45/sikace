<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->nullable(); // Link ke tabel users jika siswa bisa login
            $table->string('nis', 20)->unique(); // Nomor Induk Siswa (wajib unik)
            $table->string('nisn', 20)->unique()->nullable(); // Nomor Induk Siswa Nasional (opsional)
            $table->string('nama_siswa', 100);
            $table->unsignedBigInteger('kelas_id')->nullable(); // Foreign key ke tabel kelas
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            // Tambahkan kolom lain jika perlu (alamat, tgl lahir, dll)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('set null'); // Jika kelas dihapus, set null
            $table->index('nama_siswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
