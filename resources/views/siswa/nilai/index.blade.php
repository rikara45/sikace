<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rapor Nilai Akademik') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Informasi Siswa & Filter --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                     <h3 class="text-lg font-medium text-gray-900 mb-2">Informasi Siswa</h3>
                     <table class="table-auto w-full text-sm mb-6">
                          <tbody>
                            <tr> <td class="px-2 py-1 font-semibold text-gray-700">Nama Siswa:</td> <td class="px-2 py-1">{{ $siswa->nama_siswa }}</td> </tr>
                            <tr class="bg-gray-50"> <td class="px-2 py-1 font-semibold text-gray-700">NIS / NISN:</td> <td class="px-2 py-1">{{ $siswa->nis }} {{ $siswa->nisn ? '/ '.$siswa->nisn : '' }}</td> </tr>
                            @if($kelasPeriode)
                            <tr> <td class="px-2 py-1 font-semibold text-gray-700">Kelas:</td> <td class="px-2 py-1">{{ $kelasPeriode->nama_kelas }}</td> </tr>
                            <tr class="bg-gray-50"> <td class="px-2 py-1 font-semibold text-gray-700">Wali Kelas:</td> <td class="px-2 py-1">{{ $waliKelasPeriode?->nama_guru ?? '-' }}</td> </tr>
                            @endif
                          </tbody>
                     </table>
                     <hr class="my-4">
                     <h3 class="text-lg font-medium text-gray-900 mb-2">Lihat Rapor</h3>
                    <form method="GET" action="{{ route('siswa.nilai.index') }}" class="flex flex-wrap items-end space-x-0 sm:space-x-4 space-y-2 sm:space-y-0">
                        <div class="w-full sm:w-auto">
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran')" class="text-sm"/>
                            <select id="tahun_ajaran" name="tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                @if(!isset($availableFilters) || $availableFilters->isEmpty())
                                    <option value="">Belum ada data</option>
                                @else
                                    @foreach ($availableFilters->pluck('tahun_ajaran')->unique()->sortDesc() as $tahun)
                                        <option value="{{ $tahun }}" @selected($filterTahunAjaran == $tahun)>{{ $tahun }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                         <div class="w-full sm:w-auto">
                            <x-input-label for="semester" :value="__('Semester')" class="text-sm"/>
                             <select id="semester" name="semester" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                 @if(!isset($availableFilters) || $availableFilters->isEmpty() || (isset($filterTahunAjaran) && $availableFilters->where('tahun_ajaran', $filterTahunAjaran)->isEmpty()))
                                     <option value="">Belum ada data</option>
                                 @else
                                    @foreach ($availableFilters->where('tahun_ajaran', $filterTahunAjaran)->pluck('semester')->unique()->sort() as $sem)
                                        <option value="{{ $sem }}" @selected($filterSemester == $sem)>Semester {{ $sem }}</option>
                                    @endforeach
                                 @endif
                             </select>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabel Nilai (Rapor) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Hasil Belajar Semester {{ $filterSemester ?? '-'}} Tahun Ajaran {{ $filterTahunAjaran ?? '-'}}</h3>
                     @if (isset($nilais) && $nilais->count() > 0)
                         <div class="overflow-x-auto border border-gray-200 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200 border-collapse border border-gray-300">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300 align-middle">No</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300 align-middle">Mata Pelajaran</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300 align-middle">Nilai Akhir</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300 align-middle">Predikat</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300 align-middle">Guru Mapel</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300 align-middle">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($nilais as $index => $nilai)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 text-center border border-gray-300">{{ $index + 1 }}</td>
                                            <td class="px-6 py-2 whitespace-nowrap text-sm text-gray-900 border border-gray-300 text-center">
                                                {{ $nilai->mataPelajaran?->nama_mapel ?? 'Mapel Dihapus' }}
                                                {{ $nilai->mataPelajaran?->kode_mapel ? '('.$nilai->mataPelajaran->kode_mapel.')' : ''}}
                                            </td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center border border-gray-300 font-semibold">{{ !is_null($nilai->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center border border-gray-300">{{ $nilai->predikat ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800 border border-gray-300 text-center">{{ $nilai->guru?->nama_guru ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center border border-gray-300">
                                                <a href="{{ route('siswa.nilai.mapel', [
                                                        'matapelajaran_id' => $nilai->mataPelajaran->id,
                                                        'tahun_ajaran' => $filterTahunAjaran,
                                                        'semester' => $filterSemester
                                                    ]) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 hover:underline text-xs">
                                                    Lihat Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                         </div>
                         
                         @if(isset($filterTahunAjaran) && $filterTahunAjaran && isset($filterSemester) && $filterSemester)
                         <div class="flex justify-end mt-4">
                             <a href="{{ route('laporan.rapor.siswa.cetak', ['siswa' => $siswa->id, 'tahun_ajaran' => $filterTahunAjaran, 'semester' => $filterSemester]) }}" target="_blank"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-file-pdf mr-2"></i>Cetak PDF
                                </a>
                         </div>
                         @endif
                     @else
                         <p class="text-center text-gray-500 py-4">Belum ada data nilai untuk periode yang dipilih.</p>
                     @endif
                 </div>
            </div>
        </div>
    </div>
</x-app-layout>