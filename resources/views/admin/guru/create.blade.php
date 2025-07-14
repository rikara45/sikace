<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Data Guru Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @include('layouts.partials.alert-messages')

                    <form method="POST" action="{{ route('admin.guru.store') }}">
                        @csrf

                        <div class="mt-4">
                            <x-input-label for="nip" :value="__('NIP (Nomor Induk Pegawai - Wajib)')" />
                            <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip')" required autofocus />
                            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="nama_guru" :value="__('Nama Lengkap Guru')" />
                            <x-text-input id="nama_guru" class="block mt-1 w-full" type="text" name="nama_guru" :value="old('nama_guru')" required />
                            <x-input-error :messages="$errors->get('nama_guru')" class="mt-2" />
                        </div>

                        {{-- Field Username Baru --}}
                        <div class="mt-4">
                            <x-input-label for="username" :value="__('Username (Opsional)')" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Jika diisi, guru dapat login menggunakan username ini selain menggunakan NIP. Hanya boleh berisi huruf, angka, strip, dan garis bawah.</p>
                        </div>
                        {{-- End Field Username Baru --}}

                        <br>

                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="font-semibold text-blue-800 mb-2">Informasi Akun Login Guru</div>
                            <ul class="text-blue-700 text-sm mt-2 ml-4 list-disc space-y-1">
                                <li>Akun login akan dibuat otomatis.</li>
                                <li>Guru dapat login menggunakan <strong>NIP</strong> atau <strong>Username</strong> (jika diisi).</li>
                                <li>Password awal adalah <strong>NIP</strong> dari guru yang bersangkutan.</li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.guru.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Batal') }}
                            </a>
                            
                            <x-primary-button>
                                {{ __('Simpan Data Guru') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>