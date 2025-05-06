<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Tombol Tambah --}}
                    <div class="mb-4">
                        <a href="{{ route('admin.guru.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 ...">
                            {{ __('Tambah Guru') }}
                        </a>
                    </div>

                    {{-- Pesan Sukses/Error --}}
                    @include('layouts.partials.alert-messages') {{-- Asumsi Anda buat partials untuk alert --}}

                     {{-- Form Pencarian --}}
                     <form method="GET" action="{{ route('admin.guru.index') }}" class="mb-4">
                         <div class="flex">
                             <x-text-input id="search" class="block mt-1 w-full mr-2" type="text" name="search" :value="request('search')" placeholder="Cari Nama, NIP, atau Email..." />
                             <x-primary-button>
                                 {{ __('Cari') }}
                             </x-primary-button>
                         </div>
                     </form>

                    {{-- Tabel Data Guru --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Guru</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Login</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($gurus as $index => $guru)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $gurus->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $guru->nip ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $guru->nama_guru }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $guru->user->email ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.guru.show', $guru) }}" class="text-blue-600 hover:text-blue-900 mr-2">Lihat</a>
                                            <a href="{{ route('admin.guru.edit', $guru) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                            <form action="{{ route('admin.guru.destroy', $guru) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini? Menghapus guru juga akan menghapus akun login terkait.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Data guru tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{ $gurus->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>