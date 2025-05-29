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
                        <a href="{{ route('admin.matapelajaran.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-arrow-left mr-2"></i> {{ __('Kembali ke Daftar Mapel') }}
                        </a>
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
                            {{-- Modifikasi Bagian Ini --}}
                            <tr>
                                 <td class="px-4 py-2 font-semibold text-gray-700 align-top" >Diajarkan di Kelas</td>
                                 <td class="px-4 py-2">
                                      @if($mataPelajaran->kelas && $mataPelajaran->kelas->count() > 0)
                                          <ul class="list-disc list-inside">
                                              @foreach ($mataPelajaran->kelas as $kelas)
                                                  <li>
                                                      <a href="{{ route('admin.kelas.show', $kelas->id) }}" class="text-blue-600 hover:underline">
                                                          {{ $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})
                                                      </a>
                                                      @php
                                                          $guruPengajarDiKelasIni = null;
                                                          if ($kelas->pivot && $kelas->pivot->guru_id) {
                                                              $guruPengajarDiKelasIni = \App\Models\Guru::find($kelas->pivot->guru_id);
                                                          }
                                                      @endphp
                                                      @if($guruPengajarDiKelasIni)
                                                          <span class="text-xs text-gray-500">- Diajar oleh: {{ $guruPengajarDiKelasIni->nama_guru }}</span>
                                                      @else
                                                          <span class="text-xs text-red-500">- Guru pengampu belum ditentukan</span>
                                                      @endif
                                                  </li>
                                              @endforeach
                                          </ul>
                                     @else
                                         <span class="text-gray-500">- Belum diajarkan di kelas manapun atau data jadwal belum diisi.</span>
                                     @endif
                                 </td>
                            </tr>
                            {{-- Akhir Modifikasi --}}
                         </tbody>
                     </table>
                     <div class="mt-6 flex justify-end space-x-2">
                          <a href="{{ route('admin.matapelajaran.edit', $mataPelajaran) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                              Edit
                          </a>
                          <form action="{{ route('admin.matapelajaran.destroy', $mataPelajaran) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus mapel ini?');">
                              @csrf @method('DELETE')
                              <x-danger-button type="submit"> Hapus </x-danger-button>
                          </form>
                     </div>
                </div>
            </div>
         </div>
     </div>
 </x-app-layout>