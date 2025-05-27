<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Nilai: {{ $mataPelajaran->nama_mapel }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Informasi Siswa & Filter (Sama seperti sebelumnya) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('siswa.nilai.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Kembali ke Rapor Utama</a>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        {{ $siswa->nama_siswa }} ({{ $siswa->nis }})
                    </h3>
                    <p class="text-md font-semibold text-indigo-700 mb-4">Mata Pelajaran: {{ $mataPelajaran->nama_mapel }} {{ $mataPelajaran->kode_mapel ? '('.$mataPelajaran->kode_mapel.')' : '' }}</p>

                    @if(isset($availableFilters) && $availableFilters->count() > 0)
                    <form method="GET" action="{{ route('siswa.nilai.mapel', $mataPelajaran->id) }}" class="flex flex-wrap items-end space-x-0 sm:space-x-4 space-y-2 sm:space-y-0 mb-6">
                        {{-- Dropdown Filter Tahun Ajaran --}}
                        <div class="w-full sm:w-auto">
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran')" class="text-sm"/>
                            <select id="tahun_ajaran" name="tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                @foreach ($availableFilters->pluck('tahun_ajaran')->unique()->sortDesc() as $tahun)
                                    <option value="{{ $tahun }}" @selected(isset($filterTahunAjaran) && $filterTahunAjaran == $tahun)>{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                         {{-- Dropdown Filter Semester --}}
                         <div class="w-full sm:w-auto">
                            <x-input-label for="semester" :value="__('Semester')" class="text-sm"/>
                             <select id="semester" name="semester" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                 @foreach ($availableFilters->where('tahun_ajaran', $filterTahunAjaran ?? '')->pluck('semester')->unique()->sort() as $sem)
                                     <option value="{{ $sem }}" @selected(isset($filterSemester) && $filterSemester == $sem)>Semester {{ $sem }}</option>
                                 @endforeach
                             </select>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Detail Nilai --}}
            @if($nilaiDetail)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold mb-3">Rincian Nilai - Semester {{ $nilaiDetail->semester }} Tahun {{ $nilaiDetail->tahun_ajaran }}</h3>

                        {{-- INFORMASI UMUM NILAI (DI ATAS TABEL RINCIAN) --}}
                        <div class="mb-6 text-sm space-y-1 p-4 bg-gray-50 rounded-md border">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Tahun Ajaran:</span>
                                <span class="text-gray-900">{{ $nilaiDetail->tahun_ajaran }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Semester:</span>
                                <span class="text-gray-900">Semester {{ $nilaiDetail->semester }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Kelas (saat penilaian):</span>
                                <span class="text-gray-900">{{ $nilaiDetail->kelas?->nama_kelas ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Guru Pengampu:</span>
                                <span class="text-gray-900">{{ $nilaiDetail->guru?->nama_guru ?? '-' }}</span>
                            </div>
                            {{-- >> BARIS BARU UNTUK KKM << --}}
                            <div class="flex justify-between pt-2 mt-2 border-t border-gray-200">
                                <span class="font-medium text-gray-700">KKM Mata Pelajaran:</span>
                                <span class="text-gray-900 font-semibold">{{ $kkmValue ?? 'N/A' }}</span>
                            </div>
                            {{-- >> AKHIR BARIS BARU KKM << --}}
                        </div>
                        {{-- AKHIR INFORMASI UMUM NILAI --}}


                        {{-- TABEL RINCIAN KOMPONEN NILAI (Sama seperti sebelumnya) --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full w-full table-auto border-collapse border border-gray-300 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-gray-600 w-2/5 border-b">Komponen Penilaian</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-600 border-b">Nilai / Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Nilai Tugas --}}
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-700 align-top">Nilai Tugas</td>
                                        <td class="px-4 py-3 text-gray-900">
                                            @if($nilaiDetail->nilai_tugas && is_array($nilaiDetail->nilai_tugas) && count(array_filter($nilaiDetail->nilai_tugas, 'is_numeric')) > 0)
                                                <ul class="list-none space-y-1 mb-2">
                                                @foreach($nilaiDetail->nilai_tugas as $idx => $tugas)
                                                    @if(is_numeric($tugas))
                                                    <li>Tugas {{ $idx + 1 }}: <span class="font-medium">{{ number_format((float)$tugas, 2) }}</span></li>
                                                    @endif
                                                @endforeach
                                                </ul>
                                                <p class="pt-2 border-t font-semibold">Rata-rata Tugas: <span class="font-bold">{{ !is_null($rataRataTugas) ? number_format($rataRataTugas, 2) : '-' }}</span></p>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>

                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-700">Nilai UTS</td>
                                        <td class="px-4 py-3 text-gray-900 font-medium">{{ !is_null($nilaiDetail->nilai_uts) ? number_format($nilaiDetail->nilai_uts, 2) : '-' }}</td>
                                    </tr>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-700">Nilai UAS</td>
                                        <td class="px-4 py-3 text-gray-900 font-medium">{{ !is_null($nilaiDetail->nilai_uas) ? number_format($nilaiDetail->nilai_uas, 2) : '-' }}</td>
                                    </tr>
                                    <tr class="border-b bg-green-100 hover:bg-green-200">
                                        <td class="px-4 py-3 font-bold text-gray-700">Nilai Akhir</td>
                                        <td class="px-4 py-3 text-gray-900 font-bold text-lg">{{ !is_null($nilaiDetail->nilai_akhir) ? number_format($nilaiDetail->nilai_akhir, 2) : '-' }}</td>
                                    </tr>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 font-bold text-gray-700">Predikat</td>
                                        <td class="px-4 py-3 text-gray-900 font-semibold text-lg">{{ $nilaiDetail->predikat ?? '-' }}</td>
                                    </tr>
                                    @if($nilaiDetail->catatan)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-700 align-top">Catatan Guru</td>
                                        <td class="px-4 py-3 text-gray-800 italic leading-relaxed">{{ $nilaiDetail->catatan }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                     </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p>Belum ada data nilai untuk mata pelajaran ini pada periode yang dipilih.</p>
                        <p class="mt-2 text-sm">Silakan pilih Tahun Ajaran atau Semester lain jika tersedia.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>