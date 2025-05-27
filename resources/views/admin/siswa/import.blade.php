<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Impor Data Siswa dari CSV') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-4">
                        <a href="{{ route('admin.siswa.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Kembali ke Daftar Siswa</a>
                    </div>

                    @include('layouts.partials.alert-messages')

                    @if (session('import_validation_errors'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <p class="font-bold">Kesalahan Validasi Data CSV:</p>
                            <ul class="list-disc list-inside text-sm">
                                @foreach(session('import_validation_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                     @if (session('import_errors'))
                        <div class="mb-4 p-4 bg-yellow-100 border border-yellow-300 text-yellow-700 rounded">
                            <p class="font-bold">Peringatan Selama Impor:</p>
                            <div class="text-sm">{!! session('import_errors') !!}</div>
                        </div>
                    @endif


                    <form method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mt-4">
                            <x-input-label for="kelas_id" :value="__('Pilih Kelas untuk Siswa yang Diimpor')" />
                            <select id="kelas_id" name="kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach ($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="csv_file" :value="__('Pilih File CSV')" />
                            <input id="csv_file" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:bg-indigo-600 file:text-white file:text-sm file:font-semibold file:border-0 file:py-2 file:px-4 hover:file:bg-indigo-700" type="file" name="csv_file" required accept=".csv,.txt">
                            <x-input-error :messages="$errors->get('csv_file')" class="mt-2" />
                            <p class="mt-1 text-xs text-gray-500">
                                Format kolom CSV: No. Urut, No. Induk, Nama Peserta Didik, L/P. <br>
                                Pastikan menggunakan heading: `no_urut`, `no_induk`, `nama_peserta_didik`, `lp`.
                            </p>
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Impor Siswa') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <div class="mt-8 p-4 border border-gray-200 rounded-md bg-gray-50">
                        <h4 class="font-semibold text-md mb-2">Panduan Format File CSV:</h4>
                        <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                            <li>File harus berformat CSV (Comma Separated Values).</li>
                            <li>Baris pertama (header) harus sesuai dengan nama kolom berikut (case-insensitive):
                                <ul class="list-circle list-inside ml-4 mt-1">
                                    <li><code>no_urut</code> (Boleh dikosongkan atau diisi angka)</li>
                                    <li><code>no_induk</code> (Wajib diisi, akan menjadi NIS)</li>
                                    <li><code>nama_peserta_didik</code> (Wajib diisi)</li>
                                    <li><code>lp</code> (Diisi 'L' untuk Laki-laki atau 'P' untuk Perempuan, case-insensitive, opsional)</li>
                                </ul>
                            </li>
                            <li>Contoh isi baris data: <code>1,S001,Budi Santoso,L</code></li>
                            <li>Pastikan tidak ada NIS yang duplikat di dalam file CSV maupun dengan data yang sudah ada di sistem.</li>
                            <li>Siswa yang berhasil diimpor akan otomatis mendapatkan akun login dengan NIS sebagai username dan password awal.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>