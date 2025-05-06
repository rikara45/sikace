<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Tombol Tambah --}}
                    <div class="mb-4">
                        <a href="{{ route('admin.siswa.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-green-600 ...">
                            {{ __('Tambah Siswa') }}
                        </a>
                    </div>

                     {{-- Pesan Sukses/Error --}}
                     @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                     @endif
                     @if (session('error'))
                         <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                             {{ session('error') }}
                         </div>
                     @endif

                     {{-- Form Pencarian (Opsional) --}}
                     <form method="GET" action="{{ route('admin.siswa.index') }}" class="mb-4">
                         <div class="flex">
                             <x-text-input id="search" class="block mt-1 w-full mr-2" type="text" name="search" :value="request('search')" placeholder="Cari Nama atau NIS..." />
                             <x-primary-button>
                                 {{ __('Cari') }}
                             </x-primary-button>
                         </div>
                     </form>

                    {{-- Tabel Data Siswa --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                     <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($siswas as $index => $siswa)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $siswas->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $siswa->nis }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $siswa->nama_siswa }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $siswa->kelas->nama_kelas ?? 'Belum ada kelas' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.siswa.show', $siswa) }}" class="text-blue-600 hover:text-blue-900 mr-2">Lihat</a>
                                            <a href="{{ route('admin.siswa.edit', $siswa) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>
                                            <form action="{{ route('admin.siswa.destroy', $siswa) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Data siswa tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{ $siswas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>