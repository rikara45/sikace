<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Mata Pelajaran') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.matapelajaran.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-plus mr-2"></i> {{ __('Tambah Mata Pelajaran') }}
                        </a>
                    </div>
                     @include('layouts.partials.alert-messages')
                     <form method="GET" action="{{ route('admin.matapelajaran.index') }}" class="mb-4">
                          <div class="flex">
                             <x-text-input id="search" class="block mt-1 w-full mr-2" type="text" name="search" :value="request('search')" placeholder="Cari Nama atau Kode Mapel..." />
                             <x-primary-button> {{ __('Cari') }} </x-primary-button>
                         </div>
                     </form>
                     <div class="overflow-x-auto border border-gray-200 rounded-md">
                        <table class="min-w-full divide-y divide-gray-200 border-collapse border border-gray-300">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">No</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Kode Mapel</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Nama Mata Pelajaran</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                 @forelse ($mataPelajarans as $index => $mapel)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 border border-gray-300 text-center">{{ $mataPelajarans->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-300 text-center">{{ $mapel->kode_mapel ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-300 text-center">{{ $mapel->nama_mapel }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium border border-gray-300 text-center">
                                            <div class="flex flex-wrap gap-2 justify-center">
                                                <a href="{{ route('admin.matapelajaran.show', $mapel) }}"
                                                   class="inline-flex items-center px-3 py-1 border border-blue-600 text-blue-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-blue-600 hover:text-white transition-colors duration-150">
                                                    <i class="fas fa-eye mr-1"></i> Lihat
                                                </a>
                                                <a href="{{ route('admin.matapelajaran.edit', $mapel) }}"
                                                   class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-indigo-600 hover:text-white transition-colors duration-150">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.matapelajaran.destroy', $mapel) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus mata pelajaran ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1 border border-red-600 text-red-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-red-600 hover:text-white transition-colors duration-150">
                                                        <i class="fas fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 border border-gray-300">Data mata pelajaran tidak ditemukan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                     <div class="mt-4">{{ $mataPelajarans->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>