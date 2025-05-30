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

                         <br>
                         <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="font-semibold text-blue-800 mb-2">Akun Login Siswa (Otomatis dibuat)</div>
                                <p class="text-blue-700 text-sm">Setelah data berhasil disimpan, sistem akan otomatis membuat akun untuk setiap siswa dengan:</p>
                                <ul class="text-blue-700 text-sm mt-2 ml-4 list-disc">
                                    <li><strong>Username:</strong> NIS Siswa</li>
                                    <li><strong>Password awal:</strong> NIS Siswa</li>
                                </ul>
                            </div>


                        {{-- <div class="mt-4">
                            <x-input-label for="email" :value="__('Email (Untuk Login)')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" autocomplete="off" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password Baru (Kosongkan jika tidak diubah)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div> --}}


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.siswa.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
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