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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100" x-data="{ 
            sidebarOpen: window.innerWidth >= 1024,
            isMobile: window.innerWidth < 1024 
        }" x-init="
            // Handle resize untuk responsive behavior
            window.addEventListener('resize', () => {
                isMobile = window.innerWidth < 1024;
                if (window.innerWidth >= 1024) {
                    sidebarOpen = true;
                } else {
                    sidebarOpen = false;
                }
            });
        ">

            @if (Auth::check() && Auth::user()->hasRole('admin'))
                {{-- Layout untuk Admin dengan Sidebar Collapsible --}}
                <div class="flex h-screen overflow-hidden">
                    {{-- Overlay untuk mobile --}}
                    <div 
                        x-show="sidebarOpen && isMobile" 
                        x-transition:enter="transition-opacity ease-linear duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity ease-linear duration-300"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="sidebarOpen = false"
                        class="fixed inset-0 bg-gray-600 bg-opacity-75 z-30"
                        style="display: none;"
                    ></div>

                    {{-- Sidebar --}}
                    <aside
                        class="bg-gradient-to-b from-blue-700 to-blue-900 text-gray-200 flex flex-col transition-all duration-300 ease-in-out z-40"
                        :class="{
                            'fixed inset-y-0 left-0 w-64 transform': isMobile,
                            'translate-x-0': sidebarOpen && isMobile,
                            '-translate-x-full': !sidebarOpen && isMobile,
                            'w-64': sidebarOpen && !isMobile,
                            'w-16': !sidebarOpen && !isMobile
                        }"
                    >
                        <div class="h-16 flex items-center justify-between px-4 border-b border-blue-600 flex-shrink-0">
                            <span class="text-xl font-semibold text-white" x-show="sidebarOpen || isMobile">Admin Panel</span>
                            <span class="text-xl font-semibold text-white" x-show="!sidebarOpen && !isMobile">A</span>
                            <button @click="sidebarOpen = false" class="text-white hover:text-gray-300" x-show="isMobile" aria-label="Tutup sidebar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <nav class="flex-1 px-2 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Dashboard') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.matapelajaran.index')" :active="request()->routeIs('admin.matapelajaran.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-book"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Manajemen Mapel') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.guru.index')" :active="request()->routeIs('admin.guru.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-chalkboard-teacher"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Manajemen Guru') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.siswa.index')" :active="request()->routeIs('admin.siswa.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-users"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Manajemen Siswa') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.kelas.index')" :active="request()->routeIs('admin.kelas.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-door-open"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Manajemen Kelas') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')" class="flex items-center px-4 py-2 rounded hover:bg-blue-800 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-cog"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Setting Tahun Ajaran') }}</span>
                            </x-nav-link>
                        </nav>
                        <div class="px-2 py-4 border-t border-blue-600 flex-shrink-0">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center transition-colors">
                                    <i class="fas fa-sign-out-alt" :class="{'mr-2': sidebarOpen || isMobile}"></i> 
                                    <span x-show="sidebarOpen || isMobile">Logout</span>
                                </button>
                            </form>
                        </div>
                    </aside>

                    {{-- Main Content --}}
                    <div class="flex flex-col flex-1 overflow-hidden">
                        @if (isset($header))
                            <header class="bg-white shadow flex-shrink-0 z-20">
                                <div class="max-w-full mx-auto py-3 px-4 sm:px-6 lg:px-8 flex items-center">
                                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md p-2 mr-4" aria-label="Toggle sidebar">
                                        <i class="fas fa-bars text-xl"></i>
                                    </button>
                                    <div class="flex-1">{{ $header }}</div>
                                </div>
                            </header>
                        @endif
                        <main class="flex-1 p-6 lg:p-8 overflow-y-auto bg-gray-50">{{ $slot }}</main>
                    </div>
                </div>

            @elseif (Auth::check() && Auth::user()->hasRole('guru'))
                {{-- Layout untuk Guru dengan Sidebar Collapsible --}}
                <div class="flex h-screen overflow-hidden">
                    {{-- Overlay untuk mobile --}}
                    <div 
                        x-show="sidebarOpen && isMobile" 
                        x-transition:enter="transition-opacity ease-linear duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity ease-linear duration-300"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="sidebarOpen = false"
                        class="fixed inset-0 bg-gray-600 bg-opacity-75 z-30"
                        style="display: none;"
                    ></div>

                    {{-- Sidebar --}}
                    <aside
                        class="bg-gradient-to-b from-teal-600 to-teal-800 text-gray-200 flex flex-col transition-all duration-300 ease-in-out z-40"
                        :class="{
                            'fixed inset-y-0 left-0 w-64 transform': isMobile,
                            'translate-x-0': sidebarOpen && isMobile,
                            '-translate-x-full': !sidebarOpen && isMobile,
                            'w-64': sidebarOpen && !isMobile,
                            'w-16': !sidebarOpen && !isMobile
                        }"
                    >
                        <div class="h-16 flex items-center justify-between px-4 border-b border-teal-500 flex-shrink-0">
                            <span class="text-xl font-semibold text-black" x-show="sidebarOpen || isMobile">Guru Panel</span>
                            <span class="text-xl font-semibold text-black" x-show="!sidebarOpen && !isMobile">G</span>
                            <button @click="sidebarOpen = false" class="text-white hover:text-gray-300" x-show="isMobile" aria-label="Tutup sidebar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <nav class="flex-1 px-2 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('guru.dashboard')" :active="request()->routeIs('guru.dashboard')" class="flex items-center px-4 py-2 rounded hover:bg-teal-700 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Dashboard') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('guru.nilai.input')" :active="request()->routeIs('guru.nilai.input')" class="flex items-center px-4 py-2 rounded hover:bg-teal-700 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-edit"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Input & Pengaturan Nilai') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('guru.rekap-nilai.index')" :active="request()->routeIs('guru.rekap-nilai.index')" class="flex items-center px-4 py-2 rounded hover:bg-teal-700 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-list-alt"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Rekap Nilai Siswa') }}</span>
                            </x-nav-link>
                        </nav>
                        <div class="px-2 py-4 border-t border-teal-500 flex-shrink-0">
                            <form method="POST" action="{{ route('logout') }}"> 
                                @csrf 
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center transition-colors"> 
                                    <i class="fas fa-sign-out-alt" :class="{'mr-2': sidebarOpen || isMobile}"></i> 
                                    <span x-show="sidebarOpen || isMobile">Logout</span>
                                </button> 
                            </form>
                        </div>
                    </aside>
                    
                    {{-- Main Content --}}
                    <div class="flex flex-col flex-1 overflow-hidden">
                        @if (isset($header))
                            <header class="bg-white shadow flex-shrink-0 z-20">
                                <div class="max-w-full mx-auto py-3 px-4 sm:px-6 lg:px-8 flex items-center">
                                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md p-2 mr-4" aria-label="Toggle sidebar">
                                        <i class="fas fa-bars text-xl"></i>
                                    </button>
                                    <div class="flex-1">{{ $header }}</div>
                                </div>
                            </header>
                        @endif
                        <main class="flex-1 p-6 lg:p-8 overflow-y-auto bg-gray-50">{{ $slot }}</main>
                    </div>
                </div>

            @elseif (Auth::check() && Auth::user()->hasRole('siswa'))
                {{-- Layout untuk Siswa dengan Sidebar Collapsible --}}
                <div class="flex h-screen overflow-hidden">
                    {{-- Overlay untuk mobile --}}
                    <div 
                        x-show="sidebarOpen && isMobile" 
                        x-transition:enter="transition-opacity ease-linear duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity ease-linear duration-300"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        @click="sidebarOpen = false"
                        class="fixed inset-0 bg-gray-600 bg-opacity-75 z-30"
                        style="display: none;"
                    ></div>

                    {{-- Sidebar --}}
                    <aside
                        class="bg-gradient-to-b from-purple-600 to-purple-800 text-gray-200 flex flex-col transition-all duration-300 ease-in-out z-40"
                        :class="{
                            'fixed inset-y-0 left-0 w-64 transform': isMobile,
                            'translate-x-0': sidebarOpen && isMobile,
                            '-translate-x-full': !sidebarOpen && isMobile,
                            'w-64': sidebarOpen && !isMobile,
                            'w-16': !sidebarOpen && !isMobile
                        }"
                    >
                        <div class="h-16 flex items-center justify-between px-4 border-b border-purple-500 flex-shrink-0">
                            <span class="text-xl font-semibold text-white" x-show="sidebarOpen || isMobile">Siswa Panel</span>
                            <span class="text-xl font-semibold text-white" x-show="!sidebarOpen && !isMobile">S</span>
                            <button @click="sidebarOpen = false" class="text-white hover:text-gray-300" x-show="isMobile" aria-label="Tutup sidebar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <nav class="flex-1 px-2 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('siswa.dashboard')" :active="request()->routeIs('siswa.dashboard')" class="flex items-center px-4 py-2 rounded hover:bg-purple-700 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Dashboard') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('siswa.nilai.index')" :active="request()->routeIs('siswa.nilai.*')" class="flex items-center px-4 py-2 rounded hover:bg-purple-700 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-clipboard-list"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Lihat Nilai') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" class="flex items-center px-4 py-2 rounded hover:bg-purple-700 text-gray-100 hover:text-white transition-colors">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-user-cog"></i></span>
                                <span class="ml-3 truncate" x-show="sidebarOpen || isMobile">{{ __('Profil Saya') }}</span>
                            </x-nav-link>
                        </nav>
                        <div class="px-2 py-4 border-t border-purple-500 flex-shrink-0">
                            <form method="POST" action="{{ route('logout') }}"> 
                                @csrf 
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center transition-colors"> 
                                    <i class="fas fa-sign-out-alt" :class="{'mr-2': sidebarOpen || isMobile}"></i> 
                                    <span x-show="sidebarOpen || isMobile">Logout</span>
                                </button> 
                            </form>
                        </div>
                    </aside>
                    
                    {{-- Main Content --}}
                    <div class="flex flex-col flex-1 overflow-hidden">
                        @if (isset($header))
                            <header class="bg-white shadow flex-shrink-0 z-20">
                                <div class="max-w-full mx-auto py-3 px-4 sm:px-6 lg:px-8 flex items-center">
                                     <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md p-2 mr-4" aria-label="Toggle sidebar">
                                        <i class="fas fa-bars text-xl"></i>
                                    </button>
                                    <div class="flex-1">{{ $header }}</div>
                                </div>
                            </header>
                        @endif
                        <main class="flex-1 p-6 lg:p-8 overflow-y-auto bg-gray-50">{{ $slot }}</main>
                    </div>
                </div>

            @else
                {{-- Layout Default untuk Pengguna yang Terautentikasi (misal, role lain) atau Guest jika tidak dihandle middleware --}}
                @auth
                    @include('layouts.navigation') {{-- Navigasi standar Breeze (top bar) --}}
                @endauth

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
            @endif

        </div>
        @stack('scripts')
    </body>
</html>