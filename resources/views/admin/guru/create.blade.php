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
                            {{-- Ubah label NIP --}}
                            <x-input-label for="nip" :value="__('NIP (Nomor Induk Pegawai - Wajib Diisi)')" />
                            <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip')" required autofocus />
                            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="nama_guru" :value="__('Nama Lengkap Guru')" />
                            <x-text-input id="nama_guru" class="block mt-1 w-full" type="text" name="nama_guru" :value="old('nama_guru')" required />
                            <x-input-error :messages="$errors->get('nama_guru')" class="mt-2" />
                        </div>

                        <BR></BR>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="font-semibold text-blue-800 mb-2">Akun Login Guru (Otomatis dibuat)</div>
                                <p class="text-blue-700 text-sm">Setelah data berhasil disimpan, sistem akan otomatis membuat akun untuk setiap guru dengan:</p>
                                <ul class="text-blue-700 text-sm mt-2 ml-4 list-disc">
                                    <li><strong>Username:</strong> Nama Lengkap Guru (contoh: Nama Guru "Doni Susanto" maka username untuk loginnya adalah "doni.susanto") atau bisa juga menggunakan NIP.</li>
                                    <li><strong>Password awal:</strong> NIP Guru</li>
                                </ul>
                            </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.guru.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
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