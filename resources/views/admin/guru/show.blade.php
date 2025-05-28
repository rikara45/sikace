<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Guru: ') . $guru->nama_guru }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.guru.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Kembali ke Daftar Guru</a>
                    </div>

                    <table class="table-auto w-full mb-6">
                        <tbody>
                            <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700" >NIP</td>
                                <td class="px-4 py-2">{{ $guru->nip ?? '-' }}</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 font-semibold text-gray-700" >Nama Guru</td>
                                <td class="px-4 py-2">{{ $guru->nama_guru }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700" >Email Login</td>
                                <td class="px-4 py-2">{{ $guru->user?->email ?? '-' }}</td>
                            </tr>
                             <tr class="bg-gray-50">
                                <td class="px-4 py-2 font-semibold text-gray-700" >Wali Kelas</td>
                                 <td class="px-4 py-2">{{ $guru->kelasWali?->nama_kelas ?? '-' }}</td>
                            </tr>
                             <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700 align-top" >Mata Pelajaran Diampu</td>
                                 <td class="px-4 py-2">
                                     @forelse ($guru->mataPelajaransDiampu as $mapel)
                                         <span class="inline-block bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">{{ $mapel->nama_mapel }}</span>
                                     @empty
                                         -
                                     @endforelse
                                 </td>
                            </tr>
                        </tbody>
                    </table>

                     <div class="mt-6 flex justify-end space-x-2">
                         <a href="{{ route('admin.guru.edit', $guru) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                             Edit
                         </a>
                         <form action="{{ route('admin.guru.destroy', $guru) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini? Menghapus guru juga akan menghapus akun login terkait.');">
                             @csrf
                             @method('DELETE')
                             <x-danger-button type="submit">
                                 Hapus
                             </x-danger-button>
                         </form>
                     </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>