<x-app-layout>
    {{-- Slot Header untuk Judul Halaman --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Kelas: ') . $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Include partials untuk menampilkan pesan sukses/error --}}
                    @include('layouts.partials.alert-messages')

                    {{-- Form Edit Kelas --}}
                    {{-- Method POST, tapi di-spoof menjadi PUT dengan @method('PUT') --}}
                    {{-- Action mengarah ke route 'admin.kelas.update', mengirim objek $kelas --}}
                    <form method="POST" action="{{ route('admin.kelas.update', $kelas) }}">
                        @csrf {{-- Token CSRF --}}
                        @method('PUT') {{-- Spoofing method PUT untuk update --}}

                        <div class="mt-4">
                            <x-input-label for="nama_kelas" :value="__('Nama Kelas (Contoh: X IPA 1)')" />
                            <x-text-input id="nama_kelas" class="block mt-1 w-full" type="text" name="nama_kelas" :value="old('nama_kelas', $kelas->nama_kelas)" required autofocus />
                            {{-- Komponen untuk menampilkan error validasi --}}
                            <x-input-error :messages="$errors->get('nama_kelas')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran (Format: YYYY/YYYY)')" />
                            <x-text-input id="tahun_ajaran" class="block mt-1 w-full" type="text" name="tahun_ajaran" :value="old('tahun_ajaran', $kelas->tahun_ajaran)" placeholder="Contoh: 2024/2025" required />
                            <x-input-error :messages="$errors->get('tahun_ajaran')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="wali_kelas_id" :value="__('Wali Kelas (Opsional)')" />
                            <select id="wali_kelas_id" name="wali_kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Tidak Ada Wali Kelas --</option>
                                {{-- Loop data guru untuk opsi dropdown --}}
                                @foreach ($gurus as $guru)
                                    <option value="{{ $guru->id }}"
                                        {{-- Tandai 'selected' jika ID guru cocok dengan old input atau data $kelas saat ini --}}
                                        @if (old('wali_kelas_id', $kelas->wali_kelas_id) == $guru->id) selected @endif
                                    >
                                        {{ $guru->nama_guru }} {{ $guru->nip ? '('.$guru->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('wali_kelas_id')" class="mt-2" />
                        </div>

                        {{-- Tombol Aksi --}}
                        <div class="flex items-center justify-end mt-6">
                            {{-- Tombol Batal (link kembali ke index) --}}
                            <a href="{{ route('admin.kelas.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"> {{ __('Batal') }} </a>
                            {{-- Tombol Simpan/Update --}}
                            <x-primary-button>
                                {{ __('Update Kelas') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>