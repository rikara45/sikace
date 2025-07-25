{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIKACE') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

        {{-- CSS untuk mencegah FOUC (Flash of Unstyled Content) --}}
        <style>
            [x-cloak] { display: none !important; }
            .sidebar-mobile-hidden {
                transform: translateX(-100%);
            }
            .sidebar-desktop-collapsed {
                width: 4rem;
            }
            .sidebar-desktop-expanded {
                width: 16rem;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100" 
             x-data="sidebarManager()" 
             x-init="init()"
             x-cloak>

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
                    ></div>

                    {{-- Sidebar --}}
                    <aside
                        class="bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 ease-in-out z-40"
                        :class="getSidebarClasses()"
                    >
                        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-700 flex-shrink-0">
                            <span class="text-xl font-semibold text-white" x-show="shouldShowText()">Admin Panel</span>
                            <span class="text-xl font-semibold text-white" x-show="!shouldShowText()">AD</span>
                            <button @click="sidebarOpen = false" class="text-white hover:text-gray-300" x-show="isMobile" aria-label="Tutup sidebar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <nav class="flex-1 px-2 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Dashboard') }}</span>
                            </x-nav-link>

                            {{-- Pembatas: Data Master --}}
                            <div class="px-4 py-2 text-xs text-slate-400 uppercase tracking-wider font-semibold mt-4 mb-2" x-show="shouldShowText()">
                                Data Master
                            </div>
                            <x-nav-link :href="route('admin.guru.index')" :active="request()->routeIs('admin.guru.*')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-chalkboard-teacher"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Manajemen Guru') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.siswa.index')" :active="request()->routeIs('admin.siswa.*')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-users"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Manajemen Siswa') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.matapelajaran.index')" :active="request()->routeIs('admin.matapelajaran.*')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-book"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Manajemen Mapel') }}</span>
                            </x-nav-link>

                            {{-- Pembatas: Pengelolaan --}}
                            <div class="px-4 py-2 text-xs text-slate-400 uppercase tracking-wider font-semibold mt-4 mb-2" x-show="shouldShowText()">
                                Pengelolaan
                            </div>
                            <x-nav-link :href="route('admin.kelas.index')" :active="request()->routeIs('admin.kelas.*')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-door-open"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Manajemen Kelas') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('admin.kenaikan.form')" :active="request()->routeIs('admin.kenaikan.*')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-graduation-cap"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Kenaikan Kelas') }}</span>
                            </x-nav-link>

                            {{-- Setting --}}
                            <div class="px-4 py-2 text-xs text-slate-400 uppercase tracking-wider font-semibold mt-4 mb-2" x-show="shouldShowText()">
                                Pengaturan
                            </div>
                                <x-nav-link :href="route('admin.settings.index')" :active="request()->routeIs('admin.settings.*')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-cog"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Setting Tahun Ajaran') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-user-cog"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Profil Saya') }}</span>
                            </x-nav-link>
                        </nav>
                        <div class="px-2 py-4 border-t border-slate-700 flex-shrink-0">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center transition-colors">
                                    <i class="fas fa-sign-out-alt" :class="{'mr-2': shouldShowText()}"></i> 
                                    <span x-show="shouldShowText()">Logout</span>
                                </button>
                            </form>
                        </div>
                    </aside>

                    {{-- Main Content --}}
                    <div class="flex flex-col flex-1 overflow-hidden">
                        @if (isset($header))
                            <header class="bg-white shadow flex-shrink-0 z-20">
                                <div class="max-w-full mx-auto py-3 px-4 sm:px-6 lg:px-8 flex items-center">
                                    <button @click="toggleSidebar()" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md p-2 mr-4" aria-label="Toggle sidebar">
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
                    ></div>

                    {{-- Sidebar --}}
                    <aside
                        class="bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 ease-in-out z-40"
                        :class="getSidebarClasses()"
                    >
                        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-700 flex-shrink-0">
                            <span class="text-xl font-semibold text-white" x-show="shouldShowText()">Guru Panel</span>
                            <span class="text-xl font-semibold text-white" x-show="!shouldShowText()">GU</span>
                            <button @click="sidebarOpen = false" class="text-white hover:text-gray-300" x-show="isMobile" aria-label="Tutup sidebar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <nav class="flex-1 px-2 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('guru.dashboard')" :active="request()->routeIs('guru.dashboard')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Dashboard') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('guru.nilai.input')" :active="request()->routeIs('guru.nilai.input')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-edit"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Input & Pengaturan Nilai') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('guru.rekap-nilai.index')" :active="request()->routeIs('guru.rekap-nilai.index')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-list-alt"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Rekap Nilai Siswa') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-user-cog"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Profil Saya') }}</span>
                            </x-nav-link>
                        </nav>
                        <div class="px-2 py-4 border-t border-slate-700 flex-shrink-0">
                            <form method="POST" action="{{ route('logout') }}"> 
                                @csrf 
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center transition-colors"> 
                                    <i class="fas fa-sign-out-alt" :class="{'mr-2': shouldShowText()}"></i> 
                                    <span x-show="shouldShowText()">Logout</span>
                                </button> 
                            </form>
                        </div>
                    </aside>
                    
                    {{-- Main Content --}}
                    <div class="flex flex-col flex-1 overflow-hidden">
                        @if (isset($header))
                            <header class="bg-white shadow flex-shrink-0 z-20">
                                <div class="max-w-full mx-auto py-3 px-4 sm:px-6 lg:px-8 flex items-center">
                                    <button @click="toggleSidebar()" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md p-2 mr-4" aria-label="Toggle sidebar">
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
                    ></div>

                    {{-- Sidebar --}}
                    <aside
                        class="bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 ease-in-out z-40"
                        :class="getSidebarClasses()"
                    >
                        <div class="h-16 flex items-center justify-between px-4 border-b border-slate-700 flex-shrink-0">
                            <span class="text-xl font-semibold text-white" x-show="shouldShowText()">Siswa Panel</span>
                            <span class="text-xl font-semibold text-white" x-show="!shouldShowText()">SI</span>
                            <button @click="sidebarOpen = false" class="text-white hover:text-gray-300" x-show="isMobile" aria-label="Tutup sidebar">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <nav class="flex-1 px-2 py-6 space-y-2 overflow-y-auto">
                            <x-nav-link :href="route('siswa.dashboard')" :active="request()->routeIs('siswa.dashboard')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-tachometer-alt"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Dashboard') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('siswa.nilai.index')" :active="request()->routeIs('siswa.nilai.*')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-clipboard-list"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Lihat Nilai') }}</span>
                            </x-nav-link>
                            <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" :sidebar="true">
                                <span class="inline-block w-5 text-center flex-shrink-0"><i class="fas fa-user-cog"></i></span>
                                <span class="ml-3 truncate" x-show="shouldShowText()">{{ __('Profil Saya') }}</span>
                            </x-nav-link>
                        </nav>
                        <div class="px-2 py-4 border-t border-slate-700 flex-shrink-0">
                            <form method="POST" action="{{ route('logout') }}"> 
                                @csrf 
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded flex items-center justify-center transition-colors"> 
                                    <i class="fas fa-sign-out-alt" :class="{'mr-2': shouldShowText()}"></i> 
                                    <span x-show="shouldShowText()">Logout</span>
                                </button> 
                            </form>
                        </div>
                    </aside>
                    
                    {{-- Main Content --}}
                    <div class="flex flex-col flex-1 overflow-hidden">
                        @if (isset($header))
                            <header class="bg-white shadow flex-shrink-0 z-20">
                                <div class="max-w-full mx-auto py-3 px-4 sm:px-6 lg:px-8 flex items-center">
                                     <button @click="toggleSidebar()" class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-md p-2 mr-4" aria-label="Toggle sidebar">
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

        {{-- Alpine.js Component untuk Sidebar Management --}}
        <script>
            function sidebarManager() {
                return {
                    sidebarOpen: false,
                    isMobile: false,
                    
                    init() {
                        // Set initial state berdasarkan ukuran layar
                        this.checkScreenSize();
                        this.setInitialSidebarState();
                        
                        // Listen untuk perubahan ukuran layar
                        window.addEventListener('resize', () => {
                            this.handleResize();
                        });
                        
                        // Prevent FOUC dengan menghapus x-cloak setelah init
                        this.$nextTick(() => {
                            document.querySelector('[x-cloak]')?.removeAttribute('x-cloak');
                        });
                    },
                    
                    checkScreenSize() {
                        this.isMobile = window.innerWidth < 1024;
                    },
                    
                    setInitialSidebarState() {
                        if (this.isMobile) {
                            this.sidebarOpen = false;
                        } else {
                            // Untuk desktop, cek localStorage atau set default
                            const savedState = localStorage.getItem('sidebarOpen');
                            this.sidebarOpen = savedState !== null ? JSON.parse(savedState) : true;
                        }
                    },
                    
                    handleResize() {
                        const wasMobile = this.isMobile;
                        this.checkScreenSize();
                        
                        if (wasMobile !== this.isMobile) {
                            if (this.isMobile) {
                                // Beralih ke mobile
                                this.sidebarOpen = false;
                            } else {
                                // Beralih ke desktop
                                const savedState = localStorage.getItem('sidebarOpen');
                                this.sidebarOpen = savedState !== null ? JSON.parse(savedState) : true;
                            }
                        }
                    },
                    
                    toggleSidebar() {
                        this.sidebarOpen = !this.sidebarOpen;
                        
                        // Simpan state untuk desktop
                        if (!this.isMobile) {
                            localStorage.setItem('sidebarOpen', JSON.stringify(this.sidebarOpen));
                        }
                    },
                    
                    getSidebarClasses() {
                        const classes = [];
                        
                        if (this.isMobile) {
                            classes.push('fixed', 'inset-y-0', 'left-0', 'w-64', 'transform');
                            if (this.sidebarOpen) {
                                classes.push('translate-x-0');
                            } else {
                                classes.push('-translate-x-full');
                            }
                        } else {
                            if (this.sidebarOpen) {
                                classes.push('w-64');
                            } else {
                                classes.push('w-16');
                            }
                        }
                        
                        return classes.join(' ');
                    },
                    
                    shouldShowText() {
                        return this.sidebarOpen || this.isMobile;
                    }
                }
            }
        </script>

        @stack('scripts')
    </body>
</html>