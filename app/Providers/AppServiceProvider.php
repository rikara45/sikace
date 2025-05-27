<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Siswa; // Tambahkan use statement untuk model Siswa
use App\Policies\SiswaPolicy; // Tambahkan use statement untuk policy Siswa

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Siswa::class => SiswaPolicy::class, // Tambahkan ini
    ];
}
