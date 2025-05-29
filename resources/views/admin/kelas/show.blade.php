<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Kelas: ') . $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Detail Kelas --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-900">
                     <div class="mb-4">
                        <a href="{{ route('admin.kelas.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-arrow-left mr-2"></i> {{ __('Kembali ke Daftar Kelas') }}
                        </a>
                    </div>

                     <table class="table-auto w-full mb-6">
                          <tbody>
                             <tr> <td class="px-4 py-2 font-semibold text-gray-700" >Nama Kelas</td> <td class="px-4 py-2">{{ $kelas->nama_kelas }}</td> </tr>
                             <tr class="bg-gray-50"> <td class="px-4 py-2 font-semibold text-gray-700" >Tahun Ajaran</td> <td class="px-4 py-2">{{ $kelas->tahun_ajaran }}</td> </tr>
                             <tr> <td class="px-4 py-2 font-semibold text-gray-700" >Wali Kelas</td> <td class="px-4 py-2">{{ $kelas->waliKelas?->nama_guru ?? '-' }}</td> </tr>
                          </tbody>
                      </table>

                      <div class="flex justify-end space-x-2 mt-4">
                           <a href="{{ route('admin.kelas.edit', $kelas) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                               Edit
                           </a>
                           <form action="{{ route('admin.kelas.destroy', $kelas) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus kelas ini?');">
                               @csrf @method('DELETE')
                               <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">Hapus</button>
                           </form>
                      </div>
                 </div>
            </div>

            {{-- BAGIAN JADWAL MAPEL DI KELAS --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Jadwal Mata Pelajaran di Kelas Ini</h3>
                    @include('layouts.partials.alert-messages')

                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guru Pengampu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($assignedSubjects as $index => $mapel)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $mapel->nama_mapel }} {{ $mapel->kode_mapel ? '('.$mapel->kode_mapel.')' : '' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $mapel->guruPengampu?->nama_guru ?? 'Guru tidak ditemukan' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form action="{{ route('admin.kelas.removeAssignment', ['kelas' => $kelas->id, 'pivotId' => $mapel->pivot->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada mata pelajaran yang ditugaskan ke kelas ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <hr class="my-4">
                    <h4 class="text-md font-semibold mb-2">Tambahkan Mata Pelajaran ke Kelas</h4>
                    <form method="POST" action="{{ route('admin.kelas.assignSubject', $kelas) }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                         @csrf
                         <div>
                             <x-input-label for="mata_pelajaran_id" :value="__('Mata Pelajaran')" />
                             <select id="mata_pelajaran_id" name="mata_pelajaran_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                 <option value="">-- Pilih Mapel --</option>
                                 @foreach ($availableSubjects as $mapel)
                                     <option value="{{ $mapel->id }}" {{ old('mata_pelajaran_id') == $mapel->id ? 'selected' : '' }}>
                                         {{ $mapel->nama_mapel }} {{ $mapel->kode_mapel ? '('.$mapel->kode_mapel.')' : '' }}
                                     </option>
                                 @endforeach
                             </select>
                             <x-input-error :messages="$errors->get('mata_pelajaran_id')" class="mt-2" />
                         </div>

                          <div>
                             <x-input-label for="guru_id" :value="__('Guru Pengampu')" />
                             <select id="guru_id" name="guru_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                 <option value="">-- Pilih Guru --</option>
                                 @foreach ($availableTeachers as $guru)
                                     <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                         {{ $guru->nama_guru }}
                                     </option>
                                 @endforeach
                             </select>
                             <x-input-error :messages="$errors->get('guru_id')" class="mt-2" />
                         </div>

                         <div>
                             <x-primary-button type="submit">
                                 {{ __('Tambahkan ke Kelas') }}
                             </x-primary-button>
                         </div>
                    </form>
                </div>
            </div>

             <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-900">
                     <h3 class="text-lg font-semibold mb-4">Daftar Siswa</h3>
                      <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                             <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">L/P</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($kelas->siswas as $index => $siswa)
                                    <tr>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $index + 1 }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $siswa->nis }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $siswa->nama_siswa }}</td>
                                         <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : ($siswa->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                                    </tr>
                                @empty
                                     <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada siswa di kelas ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                      </div>
                 </div>
             </div>

        </div>
    </div>
</x-app-layout>