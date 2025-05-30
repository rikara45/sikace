<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Data Guru: ') . $guru->nama_guru }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @include('layouts.partials.alert-messages')

                    <form method="POST" action="{{ route('admin.guru.update', $guru) }}">
                        @csrf
                        @method('PUT')

                        <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Dasar Guru</h3>
                        <div class="mt-4">
                            <x-input-label for="nip" :value="__('NIP (Wajib Diisi)')" />
                            <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip', $guru->nip)" required autofocus />
                            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="nama_guru" :value="__('Nama Lengkap Guru')" />
                            <x-text-input id="nama_guru" class="block mt-1 w-full" type="text" name="nama_guru" :value="old('nama_guru', $guru->nama_guru)" required />
                            <x-input-error :messages="$errors->get('nama_guru')" class="mt-2" />
                        </div>

                        <hr class="my-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Akun Login Guru</h3>
                         <p class="text-sm text-gray-600 mb-4">Atur ulang password. Kosongkan password jika tidak ingin mengubahnya.</p>
                        <div class="mt-4">
                            <x-input-label for="username_display" :value="__('Username (Untuk Login)')" />
                            <x-text-input id="username_display" class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" name="username_display_disabled"
                                :value="$guru->nama_guru ? strtolower(str_replace(' ', '.', $guru->nama_guru)) : '-'"
                                readonly disabled tabindex="-1" />
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
                        </div>

                        <hr class="my-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Mata Pelajaran yang Diampu</h3>
                        <div class="mt-4 space-y-2">
                             <x-input-label :value="__('Pilih mata pelajaran yang diajar oleh guru ini:')" />
                             @if($semuaMapel->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-md max-h-60 overflow-y-auto">
                                    @foreach ($semuaMapel as $mapel)
                                        <label for="mapel_{{ $mapel->id }}" class="flex items-center">
                                            <input type="checkbox" id="mapel_{{ $mapel->id }}" name="mapel_diampu[]" value="{{ $mapel->id }}"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                   @if(in_array($mapel->id, old('mapel_diampu', $mapelDiampuIds))) checked @endif
                                            >
                                            <span class="ml-2 text-sm text-gray-600">{{ $mapel->nama_mapel }} {{ $mapel->kode_mapel ? '('.$mapel->kode_mapel.')' : '' }}</span>
                                        </label>
                                    @endforeach
                                </div>
                             @else
                                <p class="text-sm text-gray-500">Belum ada data mata pelajaran. Silakan tambahkan mata pelajaran terlebih dahulu.</p>
                             @endif
                             <x-input-error :messages="$errors->get('mapel_diampu')" class="mt-2" />
                        </div>
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.guru.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Data Guru') }}
                            </x-primary-button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>