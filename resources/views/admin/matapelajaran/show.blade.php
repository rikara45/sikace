<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Mata Pelajaran: ') . $mataPelajaran->nama_mapel }}
        </h2>
    </x-slot>
     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                 <div class="p-6 text-gray-900">
                     <div class="mb-4">
                        <a href="{{ route('admin.matapelajaran.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Kembali ke Daftar Mapel</a>
                    </div>
                    <table class="table-auto w-full mb-6">
                         <tbody>
                            <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700" >Kode Mapel</td>
                                <td class="px-4 py-2">{{ $mataPelajaran->kode_mapel ?? '-' }}</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 font-semibold text-gray-700" >Nama Mata Pelajaran</td>
                                <td class="px-4 py-2">{{ $mataPelajaran->nama_mapel }}</td>
                            </tr>
                            {{-- Tampilkan Guru Pengampu --}}
                            <tr>
                                 <td class="px-4 py-2 font-semibold text-gray-700 align-top" >Guru Pengampu</td>
                                 <td class="px-4 py-2">
                                      @forelse ($mataPelajaran->gurus as $guru)
                                         <span class="inline-block bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">{{ $guru->nama_guru }}</span>
                                     @empty
                                         -
                                     @endforelse
                                     {{-- Nanti bisa tambahkan link untuk mengelola guru pengampu mapel ini --}}
                                 </td>
                            </tr>
                         </tbody>
                     </table>
                     <div class="mt-6 flex justify-end space-x-2">
                          <a href="{{ route('admin.matapelajaran.edit', $mataPelajaran) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Edit</a>
                          <form action="{{ route('admin.matapelajaran.destroy', $mataPelajaran) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus mapel ini?');">
                              @csrf @method('DELETE')
                              <x-danger-button type="submit"> Hapus </x-danger-button>
                          </form>
                     </div>
                </div>
            </div>
             {{-- Nanti bisa tambahkan daftar kelas dimana mapel ini diajarkan --}}
         </div>
     </div>
 </x-app-layout>