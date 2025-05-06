<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Data Siswa Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Tampilkan Error Global --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                     @if (session('error'))
                         <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                             {{ session('error') }}
                         </div>
                     @endif


                    <form method="POST" action="{{ route('admin.siswa.store') }}">
                        @csrf

                        <div class="mt-4">
                            <x-input-label for="nis" :value="__('NIS (Nomor Induk Siswa)')" />
                            <x-text-input id="nis" class="block mt-1 w-full" type="text" name="nis" :value="old('nis')" required autofocus />
                            <x-input-error :messages="$errors->get('nis')" class="mt-2" />
                        </div>

                         <div class="mt-4">
                             <x-input-label for="nisn" :value="__('NISN (Opsional)')" />
                             <x-text-input id="nisn" class="block mt-1 w-full" type="text" name="nisn" :value="old('nisn')" />
                             <x-input-error :messages="$errors->get('nisn')" class="mt-2" />
                         </div>

                        <div class="mt-4">
                            <x-input-label for="nama_siswa" :value="__('Nama Lengkap Siswa')" />
                            <x-text-input id="nama_siswa" class="block mt-1 w-full" type="text" name="nama_siswa" :value="old('nama_siswa')" required />
                            <x-input-error :messages="$errors->get('nama_siswa')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="kelas_id" :value="__('Kelas')" />
                            <select id="kelas_id" name="kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelas as $k)
                                    <option value="{{ $k->id }}" {{ old('kelas_id') == $k->id ? 'selected' : '' }}>
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
                                 <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                 <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                             </select>
                             <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                         </div>

                         <hr class="my-6">
                         <h3 class="text-lg font-medium text-gray-900 mb-2">Akun Login Siswa (Opsional)</h3>


                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email (Untuk Login)')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password (Kosongkan untuk default)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.siswa.index') }}" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4">
                                {{ __('Batal') }}
                            </a>

                            <x-primary-button>
                                {{ __('Simpan Data Siswa') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>