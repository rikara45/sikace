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
                            <tr> <td class="px-2 py-1 font-semibold text-gray-700">Nama Siswa</td> <td class="px-2 py-1">{{ $siswa->nama_siswa }}</td> </tr>
                            <tr class="bg-gray-50"> <td class="px-2 py-1 font-semibold text-gray-700">NIS / NISN</td> <td class="px-2 py-1">{{ $siswa->nis }} {{ $siswa->nisn ? '/ '.$siswa->nisn : '' }}</td> </tr>
                            @if($kelasPeriode) {{-- Tampilkan jika data kelas ada --}}
                            <tr> <td class="px-2 py-1 font-semibold text-gray-700">Kelas</td> <td class="px-2 py-1">{{ $kelasPeriode->nama_kelas }}</td> </tr>
                            <tr class="bg-gray-50"> <td class="px-2 py-1 font-semibold text-gray-700">Wali Kelas</td> <td class="px-2 py-1">{{ $waliKelasPeriode?->nama_guru ?? '-' }}</td> </tr>
                            @endif
                          </tbody>
                     </table>

                     <hr class="my-4">

                     <h3 class="text-lg font-medium text-gray-900 mb-2">Lihat Rapor</h3>
                     {{-- Form Filter --}}
                    <form method="GET" action="{{ route('siswa.nilai.index') }}" class="flex flex-wrap items-end space-x-4">
                        <div>
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran')" />
                            <select id="tahun_ajaran" name="tahun_ajaran" class="block mt-1 w-full border-gray-300 ... rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                @foreach ($availableFilters->pluck('tahun_ajaran')->unique()->sortDesc() as $tahun)
                                    <option value="{{ $tahun }}" @selected($filterTahunAjaran == $tahun)>{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div>
                            <x-input-label for="semester" :value="__('Semester')" />
                             <select id="semester" name="semester" class="block mt-1 w-full border-gray-300 ... rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                 @foreach ($availableFilters->where('tahun_ajaran', $filterTahunAjaran)->pluck('semester')->unique()->sort() as $sem)
                                     <option value="{{ $sem }}" @selected($filterSemester == $sem)>Semester {{ $sem }}</option>
                                 @endforeach
                             </select>
                        </div>
                         {{-- Tombol Cetak Rapor (Akan ditambahkan nanti) --}}
                         @if($filterTahunAjaran && $filterSemester)
                         <a href="{{ route('laporan.rapor.cetak', ['siswa' => $siswa->id, 'tahun_ajaran' => $filterTahunAjaran, 'semester' => $filterSemester]) }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 mb-1">
                             Cetak Rapor (PDF)
                         </a>
                         @endif
                    </form>
                </div>
            </div>

            {{-- Tabel Nilai (Rapor) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Hasil Belajar Semester {{ $filterSemester }} Tahun Ajaran {{ $filterTahunAjaran }}</h3>
                     @if ($nilais->count() > 0)
                         <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th rowspan="2" class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">No</th>
                                        <th rowspan="2" class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border">Mata Pelajaran</th>
                                        <th colspan="3" class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Nilai Pengetahuan</th>
                                        {{-- Tambah kolom Keterampilan jika ada --}}
                                    </tr>
                                     <tr>
                                         {{-- <th class="px-4 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Tugas</th>
                                         <th class="px-4 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">UTS</th>
                                         <th class="px-4 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">UAS</th> --}}
                                         <th class="px-4 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Angka</th>
                                         <th class="px-4 py-2 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Predikat</th>
                                         <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border">Guru Mapel</th>
                                     </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($nilais as $index => $nilai)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center border">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border">{{ $nilai->mataPelajaran?->nama_mapel ?? 'Mapel Dihapus' }}</td>
                                            {{-- Tampilkan nilai komponen jika perlu --}}
                                            {{-- <td class="px-4 py-2 whitespace-nowrap text-sm text-center border">{{ number_format($nilai->nilai_tugas, 2) ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center border">{{ number_format($nilai->nilai_uts, 2) ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center border">{{ number_format($nilai->nilai_uas, 2) ?? '-' }}</td> --}}
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center border font-semibold">{{ !is_null($nilai->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-center border">{{ $nilai->predikat ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-800 border">{{ $nilai->guru?->nama_guru ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                     {{-- Nanti bisa tambahkan rata-rata atau rangking jika perlu --}}
                                </tbody>
                            </table>
                         </div>
                     @else
                         <p class="text-center text-gray-500">Belum ada data nilai untuk periode yang dipilih.</p>
                     @endif
                 </div>
            </div>
            {{-- Bisa tambahkan bagian lain seperti Absensi, Catatan Wali Kelas, dll. --}}
        </div>
    </div>
</x-app-layout>