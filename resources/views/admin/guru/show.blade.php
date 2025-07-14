<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Data Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-start">
                        <h3 class="text-2xl font-bold text-gray-800">{{ $guru->nama_guru }}</h3>
                        <a href="{{ route('admin.guru.edit', $guru->id) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Edit Data
                        </a>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm text-gray-500">Nomor Induk Pegawai (NIP)</p>
                        <p class="text-lg font-semibold">{{ $guru->nip }}</p>
                    </div>

                    {{-- Perubahan di sini: Blok Info Login yang Baru --}}
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h4 class="text-xl font-bold text-gray-800 mb-4">Informasi Akun Login</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 bg-gray-50 p-4 rounded-lg">
                            <div>
                                <p class="text-sm text-gray-500">Login via NIP</p>
                                <p class="font-semibold text-gray-800">{{ $guru->nip }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Login via Username</p>
                                <p class="font-semibold text-gray-800">
                                    @if ($guru->user?->username)
                                        {{ $guru->user->username }}
                                    @else
                                        <span class="text-gray-500 italic">Tidak diatur</span>
                                    @endif
                                </p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-sm text-gray-500">Password Awal</p>
                                <p class="font-semibold text-gray-800">Password awal sama dengan NIP. Guru dapat mengubahnya setelah login.</p>
                            </div>
                        </div>
                    </div>
                    {{-- Akhir Perubahan --}}

                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h4 class="text-xl font-bold text-gray-800 mb-4">Mata Pelajaran yang Diampu</h4>
                        @if ($guru->mataPelajaransDiampu->count() > 0)
                            <ul class="list-disc list-inside space-y-2">
                                @foreach ($guru->mataPelajaransDiampu as $mapel)
                                    <li class="text-gray-700">{{ $mapel->nama_mapel }} ({{ $mapel->kelompok_mapel }})</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">Guru ini belum diatur untuk mengampu mata pelajaran apapun.</p>
                        @endif
                    </div>

                    <div class="mt-8 text-right">
                        <a href="{{ route('admin.guru.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                            &larr; Kembali ke Daftar Guru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>