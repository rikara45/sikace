<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (Auth::user()->hasRole('siswa'))
                @php
                    $siswa = Auth::user()->siswa; 
                @endphp
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Akun Siswa</h3>
                        <p class="mt-1 text-sm text-gray-600 mb-6">
                            Untuk perubahan data, silakan hubungi administrasi.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <x-input-label :value="__('Nama Lengkap')" />
                                <x-text-input class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="Auth::user()->name" disabled readonly />
                            </div>
                            <div>
                                <x-input-label :value="__('Nomor Induk Siswa (NIS)')" />
                                <x-text-input class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="$siswa?->nis ?? 'Tidak tersedia'" disabled readonly />
                            </div>
                            @if($siswa?->nisn)
                            <div>
                                <x-input-label :value="__('NISN')" />
                                <x-text-input class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="$siswa->nisn" disabled readonly />
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            @elseif (Auth::user()->hasRole('guru'))
                @php
                    $guru = Auth::user()->guru;
                @endphp
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Akun Guru</h3>
                        <p class="mt-1 text-sm text-gray-600 mb-6">
                            Untuk perubahan data, silakan hubungi administrasi.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <x-input-label :value="__('Nama Lengkap')" />
                                <x-text-input class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="Auth::user()->name" disabled readonly />
                            </div>
                            <div>
                                <x-input-label :value="__('NIP')" />
                                <x-text-input class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="$guru?->nip ?? 'Tidak tersedia'" disabled readonly />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            @else 
                {{-- Untuk Admin --}}
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Akun Admin</h3>
                        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                            @csrf
                            @method('patch')
                            <div>
                                <x-input-label for="name" :value="__('Nama Lengkap')" />
                                <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', Auth::user()->name)" required autofocus autocomplete="name" />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', Auth::user()->email)" required autocomplete="email" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <x-primary-button>{{ __('Simpan Perubahan') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
