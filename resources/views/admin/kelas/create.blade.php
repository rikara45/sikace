<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Kelas Baru') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @include('layouts.partials.alert-messages')
                    <form method="POST" action="{{ route('admin.kelas.store') }}">
                        @csrf
                        <div class="mt-4">
                            <x-input-label for="nama_kelas" :value="__('Nama Kelas (Contoh: X IPA 1)')" />
                            <x-text-input id="nama_kelas" class="block mt-1 w-full" type="text" name="nama_kelas" :value="old('nama_kelas')" required autofocus />
                            <x-input-error :messages="$errors->get('nama_kelas')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran (Format: YYYY/YYYY)')" />
                            <x-text-input id="tahun_ajaran" class="block mt-1 w-full" type="text" name="tahun_ajaran" :value="old('tahun_ajaran')" placeholder="Contoh: 2024/2025" required />
                            <x-input-error :messages="$errors->get('tahun_ajaran')" class="mt-2" />
                        </div>
                         <div class="mt-4">
                            <x-input-label for="wali_kelas_id" :value="__('Wali Kelas (Opsional)')" />
                            <select id="wali_kelas_id" name="wali_kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Tidak Ada Wali Kelas --</option>
                                @foreach ($gurus as $guru)
                                    <option value="{{ $guru->id }}" {{ old('wali_kelas_id') == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama_guru }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('wali_kelas_id')" class="mt-2" />
                        </div>
                        <div class="flex items-center justify-end mt-6">
                            <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.kelas.index') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"> {{ __('Batal') }} </a>
                            <x-primary-button> {{ __('Simpan Kelas') }} </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>