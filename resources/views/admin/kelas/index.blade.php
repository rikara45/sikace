{{-- Mirip index Guru/Siswa, sesuaikan kolom tabel --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Kelas') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.kelas.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 ...">
                            {{ __('Tambah Kelas') }}
                        </a>
                    </div>
                    @include('layouts.partials.alert-messages')
                     <form method="GET" action="{{ route('admin.kelas.index') }}" class="mb-4"> {{-- Form Search --}}
                          <div class="flex">
                             <x-text-input id="search" class="block mt-1 w-full mr-2" type="text" name="search" :value="request('search')" placeholder="Cari Nama Kelas, Tahun Ajaran, Wali Kelas..." />
                             <x-primary-button> {{ __('Cari') }} </x-primary-button>
                         </div>
                     </form>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun Ajaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($kelasList as $index => $kelas)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $kelasList->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $kelas->nama_kelas }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $kelas->tahun_ajaran }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $kelas->waliKelas?->nama_guru ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.kelas.show', $kelas) }}" class="text-blue-600 hover:text-blue-900 mr-2">Lihat</a>
                                            <a href="{{ route('admin.kelas.edit', $kelas) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                            <form action="{{ route('admin.kelas.destroy', $kelas) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Data kelas tidak ditemukan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $kelasList->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>