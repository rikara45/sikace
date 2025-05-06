{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        {{-- Pastikan Font Awesome sudah di-load --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" /> {{-- Contoh CDN --}}


        @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- Sesuaikan jika Anda tidak menggunakan Vite --}}
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">

            @hasrole('admin')
                <div class="flex min-h-screen">
                    <aside class="w-64 bg-gradient-to-b from-blue-700 to-blue-900 text-black flex flex-col flex-shrink-0">
                        <div class="h-16 flex items-center justify-center text-xl font-semibold border-b border-blue-600 text-black">
                            Admin Panel
                        </div>
                        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-tachometer-alt"></i></span>
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.matapelajaran.index')" :active="request()->routeIs('admin.matapelajaran.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-book"></i></span>
                                {{ __('Manajemen Mapel') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.guru.index')" :active="request()->routeIs('admin.guru.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-chalkboard-teacher"></i></span>
                                {{ __('Manajemen Guru') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.siswa.index')" :active="request()->routeIs('admin.siswa.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-users"></i></span>
                                {{ __('Manajemen Siswa') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.kelas.index')" :active="request()->routeIs('admin.kelas.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-door-open"></i></span>
                                {{ __('Manajemen Kelas') }}
                            </x-nav-link>
                        </nav>
                        <div class="px-4 py-4 mt-auto border-t border-blue-600">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </aside>
                    <div class="flex-1 flex flex-col">
                        @if (isset($header))
                            <header class="bg-white shadow">
                                <div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                    {{ $header }}
                                </div>
                            </header>
                        @endif
                        <main class="flex-1 p-6 lg:p-8">
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            @elseif (auth()->check() && auth()->user()->hasRole('guru'))
                <div class="flex min-h-screen">
                    <aside class="w-64 bg-gradient-to-b from-blue-700 to-blue-900 text-black flex flex-col flex-shrink-0">
                        <div class="h-16 flex items-center justify-center text-xl font-semibold border-b border-blue-600 text-black">
                            Guru Panel
                        </div>
                        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('guru.dashboard')" :active="request()->routeIs('guru.dashboard')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-tachometer-alt"></i></span>
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            <x-nav-link :href="route('guru.nilai.create')" :active="request()->routeIs('guru.nilai.create')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-pen"></i></span>
                                {{ __('Input Nilai') }}
                            </x-nav-link>
                            <x-nav-link :href="route('guru.nilai.kelas', ['kelas_id' => 1])" :active="request()->routeIs('guru.nilai.kelas')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-list"></i></span>
                                {{ __('Lihat Nilai Kelas') }}
                            </x-nav-link>
                        </nav>
                        <div class="px-4 py-4 mt-auto border-t border-blue-600">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </aside>
                    <div class="flex-1 flex flex-col">
                        @if (isset($header))
                            <header class="bg-white shadow">
                                <div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                    {{ $header }}
                                </div>
                            </header>
                        @endif
                        <main class="flex-1 p-6 lg:p-8">
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            @elseif (auth()->check() && auth()->user()->hasRole('siswa'))
                <div class="flex min-h-screen">
                    <aside class="w-64 bg-gradient-to-b from-blue-700 to-blue-900 text-black flex flex-col flex-shrink-0">
                        <div class="h-16 flex items-center justify-center text-xl font-semibold border-b border-blue-600 text-black">
                            Siswa Panel
                        </div>
                        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('siswa.dashboard')" :active="request()->routeIs('siswa.dashboard')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-tachometer-alt"></i></span>
                                {{ __('Dashboard') }}
                            </x-nav-link>
                            {{-- <x-nav-link :href="route('siswa.nilai')" :active="request()->routeIs('siswa.nilai')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-clipboard-list"></i></span>
                                {{ __('Lihat Nilai') }}
                            </x-nav-link>
                            <x-nav-link :href="route('siswa.profil')" :active="request()->routeIs('siswa.profil')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-black">
                                <span class="inline-block mr-3 w-5 text-center"><i class="fas fa-user"></i></span>
                                {{ __('Profil') }}
                            </x-nav-link> --}}
                        </nav>
                        <div class="px-4 py-4 mt-auto border-t border-blue-600">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </aside>
                    <div class="flex-1 flex flex-col">
                        @if (isset($header))
                            <header class="bg-white shadow">
                                <div class="max-w-full mx-auto py-6 px-4 sm:px-6 lg:px-8">
                                    {{ $header }}
                                </div>
                            </header>
                        @endif
                        <main class="flex-1 p-6 lg:p-8">
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            @else
                {{-- Layout untuk Non-Admin dan Non-Guru --}}
                @include('layouts.navigation')
                @if (isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif
                <main>
                    {{ $slot }}
                </main>
            @endhasrole

        </div>
    </body>
</html>