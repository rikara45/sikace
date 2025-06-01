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

                    @include('layouts.partials.alert-messages')

                    <form method="POST" action="{{ route('admin.kelas.update', $kelas) }}">
                        @csrf
                        @method('PUT')

                        <div class="mt-4">
                            <x-input-label for="nama_kelas" :value="__('Nama Kelas (Contoh: X IPA 1)')" />
                            <x-text-input id="nama_kelas" class="block mt-1 w-full" type="text" name="nama_kelas" :value="old('nama_kelas', $kelas->nama_kelas)" required autofocus />
                            <x-input-error :messages="$errors->get('nama_kelas')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran')" />
                            <select id="tahun_ajaran" name="tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach ($tahunAjaranOptions as $tahun)
                                    <option value="{{ $tahun }}" @selected(old('tahun_ajaran', $kelas->tahun_ajaran) == $tahun)>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tahun_ajaran')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="wali_kelas_id" :value="__('Wali Kelas (Opsional)')" />
                            <select id="wali_kelas_id" name="wali_kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Pilih Wali Kelas --</option>
                                @foreach ($gurus as $guru)
                                    <option value="{{ $guru->id }}"
                                        @if (old('wali_kelas_id', $kelas->wali_kelas_id) == $guru->id) selected @endif
                                    >
                                        {{ $guru->nama_guru }} {{ $guru->nip ? '('.$guru->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('wali_kelas_id')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.kelas.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                {{ __('Batal') }}
                            </a>
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