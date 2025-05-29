<?php

namespace App\Policies;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SiswaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Siswa  $siswa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Siswa $siswa)
    {
        // Admin bisa lihat semua rapor siswa
        if ($user->hasRole('admin')) {
            return true;
        }

        // Siswa hanya bisa lihat rapornya sendiri
        if ($user->hasRole('siswa') && $user->siswa && $user->siswa->id === $siswa->id) {
            return true;
        }

        // Guru mungkin bisa melihat siswa di kelas walinya atau yang diajar (logika bisa diperluas)

        return false;
    }

    // Tambahkan method lain jika perlu (create, update, delete)
}