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
                            <x-input-label for="nip" :value="__('NIP (Nomor Induk Pegawai - Opsional)')" />
                            <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip')" autofocus />
                            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="nama_guru" :value="__('Nama Lengkap Guru')" />
                            <x-text-input id="nama_guru" class="block mt-1 w-full" type="text" name="nama_guru" :value="old('nama_guru')" required />
                            <x-input-error :messages="$errors->get('nama_guru')" class="mt-2" />
                        </div>

                        <hr class="my-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Akun Login Guru (Opsional)</h3>
                        <p class="text-sm text-gray-600 mb-4">Isi Email dan Password jika guru ini akan memiliki akun untuk login.</p>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email (Untuk Login)')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password (Wajib jika email diisi)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.guru.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
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