<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Rekap Nilai Kelas: {{ $kelas->nama_kelas }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4 flex-wrap">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Kelas: {{ $kelas->nama_kelas }}</h3>
                            <p class="text-sm text-gray-600">Tahun Ajaran: {{ $tahunAjaran }} | Semester: {{ $semester }}</p>
                        </div>
                         {{-- Form Filter Mata Pelajaran --}}
                        <form method="GET" action="{{ route('guru.nilai.kelas', $kelas->id) }}" class="flex items-end space-x-2 mt-2 md:mt-0">
                            <div>
                                <x-input-label for="matapelajaran_id" :value="__('Pilih Mata Pelajaran')" class="text-xs"/>
                                <select name="matapelajaran_id" id="matapelajaran_id" class="block mt-1 w-full border-gray-300 ... rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                    @foreach ($mapelsDiajar as $mapel)
                                        <option value="{{ $mapel->id }}" @selected($filterMapelId == $mapel->id)>
                                            {{ $mapel->nama_mapel }} {{ $mapel->kode_mapel ? '('.$mapel->kode_mapel.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Tombol Export (Akan ditambahkan nanti) --}}
                            @if($filterMapelId)
                            {{-- <a href="{{ route('laporan.nilai.kelas.export', ['kelas' => $kelas->id, 'tahun_ajaran' => $tahunAjaran, 'semester' => $semester, 'mapel' => $filterMapelId]) }}" --}}
                            <a href="#"
                               class="inline-flex items-center px-4 py-2 bg-green-600 ... text-xs text-white ... mb-1 ml-2">
                                Export Excel
                            </a>
                            @endif
                        </form>
                    </div>

                    @include('layouts.partials.alert-messages')

                     @if($mapelAktif)
                        <h4 class="text-md font-semibold mb-4">Menampilkan Nilai: {{ $mapelAktif->nama_mapel }}</h4>
                        <div class="overflow-x-auto">
                             <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border">NIS</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border">Nama Siswa</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Tugas</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">UTS</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">UAS</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Nilai Akhir</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Predikat</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($kelas->siswas as $index => $siswa)
                                        @php
                                            // Ambil nilai siswa dari $nilaiKelas yang sudah di-keyBy
                                            $nilai = $nilaiKelas->get($siswa->id);
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 text-center border">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 border">{{ $siswa->nis }}</td>
                                            <td class="px-6 py-4 border">{{ $siswa->nama_siswa }}</td>
                                            <td class="px-4 py-2 text-center border">{{ $nilai?->nilai_tugas ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border">{{ $nilai?->nilai_uts ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border">{{ $nilai?->nilai_uas ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border font-semibold">{{ !is_null($nilai?->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                            <td class="px-4 py-2 text-center border">{{ $nilai?->predikat ?? '-' }}</td>
                                        </tr>
                                    @empty
                                         <tr><td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada siswa di kelas ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                         </div>
                     @else
                         <p class="text-center text-gray-500">Silakan pilih mata pelajaran untuk melihat rekap nilai.</p>
                     @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>