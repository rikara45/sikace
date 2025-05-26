<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detail Nilai: {{ $mataPelajaran->nama_mapel }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Informasi Siswa & Filter --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('siswa.nilai.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Kembali ke Rapor Utama</a>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 mb-1">
                        {{ $siswa->nama_siswa }} ({{ $siswa->nis }})
                    </h3>
                    <p class="text-md font-semibold text-indigo-700 mb-4">Mata Pelajaran: {{ $mataPelajaran->nama_mapel }} {{ $mataPelajaran->kode_mapel ? '('.$mataPelajaran->kode_mapel.')' : '' }}</p>

                    {{-- Form Filter --}}
                    @if($availableFilters->count() > 0)
                    <form method="GET" action="{{ route('siswa.nilai.mapel', $mataPelajaran->id) }}" class="flex flex-wrap items-end space-x-0 sm:space-x-4 space-y-2 sm:space-y-0 mb-6">
                        <div class="w-full sm:w-auto">
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran')" class="text-sm"/>
                            <select id="tahun_ajaran" name="tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                @foreach ($availableFilters->pluck('tahun_ajaran')->unique()->sortDesc() as $tahun)
                                    <option value="{{ $tahun }}" @selected($filterTahunAjaran == $tahun)>{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="w-full sm:w-auto">
                            <x-input-label for="semester" :value="__('Semester')" class="text-sm"/>
                             <select id="semester" name="semester" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                 @foreach ($availableFilters->where('tahun_ajaran', $filterTahunAjaran)->pluck('semester')->unique()->sort() as $sem)
                                     <option value="{{ $sem }}" @selected($filterSemester == $sem)>Semester {{ $sem }}</option>
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
                        <h3 class="text-lg font-semibold mb-2">Rincian Nilai - Semester {{ $nilaiDetail->semester }} Tahun {{ $nilaiDetail->tahun_ajaran }}</h3>
                        <p class="text-sm text-gray-600 mb-1">Guru Pengampu: {{ $nilaiDetail->guru?->nama_guru ?? '-' }}</p>
                        <p class="text-sm text-gray-600 mb-4">Kelas saat penilaian: {{ $nilaiDetail->kelas?->nama_kelas ?? '-' }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Kolom Nilai Komponen --}}
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">Komponen Penilaian:</h4>
                                <dl class="space-y-2 text-sm">
                                    <div class="grid grid-cols-2 gap-2 p-2 bg-gray-50 rounded">
                                        <dt class="font-medium text-gray-600">Nilai Tugas:</dt>
                                        <dd class="text-gray-900">
                                            @if($nilaiDetail->nilai_tugas && count($nilaiDetail->nilai_tugas) > 0)
                                                <ul class="list-disc list-inside ml-4">
                                                @foreach($nilaiDetail->nilai_tugas as $idx => $tugas)
                                                    <li>Tugas {{ $idx + 1 }}: {{ number_format($tugas,2) }}</li>
                                                @endforeach
                                                </ul>
                                                <p class="mt-1 font-semibold">Rata-rata Tugas: {{ number_format($rataRataTugas, 2) }}</p>
                                            @else
                                                -
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 p-2 rounded">
                                        <dt class="font-medium text-gray-600">Nilai UTS:</dt>
                                        <dd class="text-gray-900">{{ !is_null($nilaiDetail->nilai_uts) ? number_format($nilaiDetail->nilai_uts,2) : '-' }}</dd>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 p-2 bg-gray-50 rounded">
                                        <dt class="font-medium text-gray-600">Nilai UAS:</dt>
                                        <dd class="text-gray-900">{{ !is_null($nilaiDetail->nilai_uas) ? number_format($nilaiDetail->nilai_uas,2) : '-' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Kolom Hasil Akhir --}}
                            <div>
                                <h4 class="font-medium text-gray-800 mb-2">Hasil Akhir:</h4>
                                <dl class="space-y-2 text-sm">
                                    <div class="grid grid-cols-2 gap-2 p-2 bg-green-50 rounded">
                                        <dt class="font-medium text-gray-600">Nilai Akhir:</dt>
                                        <dd class="text-gray-900 font-bold text-lg">{{ !is_null($nilaiDetail->nilai_akhir) ? number_format($nilaiDetail->nilai_akhir,2) : '-' }}</dd>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 p-2 rounded">
                                        <dt class="font-medium text-gray-600">Predikat:</dt>
                                        <dd class="text-gray-900 font-semibold text-lg">{{ $nilaiDetail->predikat ?? '-' }}</dd>
                                    </div>
                                     @if($nilaiDetail->catatan)
                                     <div class="p-2 mt-2 bg-yellow-50 rounded">
                                        <dt class="font-medium text-gray-600 mb-1">Catatan dari Guru:</dt>
                                        <dd class="text-gray-800 pl-2 border-l-2 border-yellow-400">{{ $nilaiDetail->catatan }}</dd>
                                    </div>
                                     @endif
                                </dl>
                            </div>
                        </div>
                     </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p>Belum ada data nilai untuk mata pelajaran ini pada periode yang dipilih.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>