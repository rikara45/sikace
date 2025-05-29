<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Mata Pelajaran Baru') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                     @include('layouts.partials.alert-messages')
                    <form method="POST" action="{{ route('admin.matapelajaran.store') }}">
                        @csrf
                         <div class="mt-4">
                            <x-input-label for="kode_mapel" :value="__('Kode Mata Pelajaran (Opsional)')" />
                            <x-text-input id="kode_mapel" class="block mt-1 w-full" type="text" name="kode_mapel" :value="old('kode_mapel')" autofocus />
                            <x-input-error :messages="$errors->get('kode_mapel')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="nama_mapel" :value="__('Nama Mata Pelajaran')" />
                            <x-text-input id="nama_mapel" class="block mt-1 w-full" type="text" name="nama_mapel" :value="old('nama_mapel')" required />
                            <x-input-error :messages="$errors->get('nama_mapel')" class="mt-2" />
                        </div>
                         <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.matapelajaran.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button> {{ __('Simpan Mapel') }} </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>