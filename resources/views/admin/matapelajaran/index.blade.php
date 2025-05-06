{{-- Mirip index Kelas/Guru/Siswa, sesuaikan kolom tabel --}}
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
                        <a href="{{ route('admin.matapelajaran.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 ...">
                            {{ __('Tambah Mata Pelajaran') }}
                        </a>
                    </div>
                     @include('layouts.partials.alert-messages')
                     <form method="GET" action="{{ route('admin.matapelajaran.index') }}" class="mb-4"> {{-- Search --}}
                          <div class="flex">
                             <x-text-input id="search" class="block mt-1 w-full mr-2" type="text" name="search" :value="request('search')" placeholder="Cari Nama atau Kode Mapel..." />
                             <x-primary-button> {{ __('Cari') }} </x-primary-button>
                         </div>
                     </form>
                     <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Mapel</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Mata Pelajaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                 @forelse ($mataPelajarans as $index => $mapel)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $mataPelajarans->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mapel->kode_mapel ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mapel->nama_mapel }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.matapelajaran.show', $mapel) }}" class="text-blue-600 hover:text-blue-900 mr-2">Lihat</a>
                                            <a href="{{ route('admin.matapelajaran.edit', $mapel) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                            <form action="{{ route('admin.matapelajaran.destroy', $mapel) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus mata pelajaran ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Data mata pelajaran tidak ditemukan.</td></tr>
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