<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // Tambahkan kolom status setelah kolom jenis_kelamin atau sesuaikan posisi
            $table->string('status', 20)->default('aktif')->after('jenis_kelamin');
            // Indeks untuk performa query berdasarkan status
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};