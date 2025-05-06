<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->nullable(); // Link ke tabel users jika guru bisa login
            $table->string('nip', 20)->unique()->nullable(); // Nomor Induk Pegawai (opsional, bisa unik)
            $table->string('nama_guru', 100);
            // Tambahkan kolom lain jika perlu (alamat, telp, dll)
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

         // Tambahkan foreign key constraint untuk wali_kelas_id di tabel kelas
         Schema::table('kelas', function (Blueprint $table) {
            $table->foreign('wali_kelas_id')->references('id')->on('gurus')->onDelete('set null');
        });
    }

    public function down(): void
    {
         // Hapus foreign key constraint sebelum drop tabel gurus
        Schema::table('kelas', function (Blueprint $table) {
             if (Schema::hasColumn('kelas', 'wali_kelas_id')) { // Check if column exists
                $table->dropForeign(['wali_kelas_id']);
             }
        });
        Schema::dropIfExists('gurus');
    }
};
