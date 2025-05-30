<x-app-layout>
    {{-- Slot Header --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Data Siswa: ') . $siswa->nama_siswa }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Tampilkan Pesan Error Validasi atau Session --}}
                    @include('layouts.partials.alert-messages')

                    {{-- Form Edit Siswa --}}
                    <form method="POST" action="{{ route('admin.siswa.update', $siswa) }}">
                        @csrf
                        @method('PUT') {{-- Method PUT untuk update --}}

                        <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Dasar Siswa</h3>

                        <div class="mt-4">
                            <x-input-label for="nis" :value="__('NIS (Nomor Induk Siswa)')" />
                            <x-text-input id="nis" class="block mt-1 w-full" type="text" name="nis" :value="old('nis', $siswa->nis)" required autofocus />
                            <x-input-error :messages="$errors->get('nis')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="nisn" :value="__('NISN (Opsional)')" />
                            <x-text-input id="nisn" class="block mt-1 w-full" type="text" name="nisn" :value="old('nisn', $siswa->nisn)" />
                            <x-input-error :messages="$errors->get('nisn')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="nama_siswa" :value="__('Nama Lengkap Siswa')" />
                            <x-text-input id="nama_siswa" class="block mt-1 w-full" type="text" name="nama_siswa" :value="old('nama_siswa', $siswa->nama_siswa)" required />
                            <x-input-error :messages="$errors->get('nama_siswa')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="kelas_id" :value="__('Kelas')" />
                            <select id="kelas_id" name="kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}"
                                        @if (old('kelas_id', $siswa->kelas_id) == $k->id) selected @endif
                                    >
                                        {{ $k->nama_kelas }} ({{ $k->tahun_ajaran }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="jenis_kelamin" :value="__('Jenis Kelamin (Opsional)')" />
                            <select id="jenis_kelamin" name="jenis_kelamin" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L" @if(old('jenis_kelamin', $siswa->jenis_kelamin) == 'L') selected @endif>Laki-laki</option>
                                <option value="P" @if(old('jenis_kelamin', $siswa->jenis_kelamin) == 'P') selected @endif>Perempuan</option>
                            </select>
                            <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                        </div>

                        {{-- Add the new status field here --}}
                        <div class="mt-4">
                            <x-input-label for="status" :value="__('Status Siswa')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach ($allStatus as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $siswa->status) == $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <hr class="my-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Akun Login Siswa</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Username login siswa adalah NIS. Password awal juga NIS.
                            Admin dapat mengubah email (yang digunakan sistem secara internal) atau mereset password di sini.
                        </p>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email Internal (otomatis dibuat: NIS@internal.siswa)')" />
                            <x-text-input id="email" class="block mt-1 w-full bg-gray-100" type="email" name="email" :value="old('email', $siswa->user?->email)" readonly disabled />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                             <p class="text-xs text-gray-500 mt-1">Email ini digunakan sistem dan tidak dapat diubah dari sini. Username login tetap NIS.</p>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Reset Password Baru (Kosongkan jika tidak ingin mereset)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.siswa.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                {{ __('Batal') }}
                            </a>

                            <x-primary-button>
                                {{ __('Update Data Siswa') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>