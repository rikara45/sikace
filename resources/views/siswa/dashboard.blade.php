<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Siswa Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Selamat datang, {{ $namaSiswa ?? Auth::user()->name }}!</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 text-sm">
                        <p><strong>NIS:</strong> {{ $nis ?? '-' }}</p>
                        <p><strong>NISN:</strong> {{ $nisn ?? '-' }}</p>
                        @if($kelasSaatIni)
                            <p><strong>Kelas Saat Ini:</strong> {{ $kelasSaatIni->nama_kelas }} ({{ $kelasSaatIni->tahun_ajaran }})</p>
                            <p><strong>Wali Kelas:</strong> {{ $waliKelasSaatIni?->nama_guru ?? '-' }}</p>
                        @else
                             <p><strong>Kelas Saat Ini:</strong> Informasi kelas belum tersedia.</p>
                        @endif
                        <p><strong>Tahun Ajaran Aktif:</strong> {{ $tahunAjaranAktif ?? '-' }}</p>
                        <p><strong>Semester Aktif:</strong> {{ $semesterAktif ? 'Semester ' . $semesterAktif : '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Ringkasan Nilai Terbaru ({{ $tahunAjaranAktif ?? '-' }} - Semester {{ $semesterAktif ?? '-' }})
                        </h3>
                        <a href="{{ route('siswa.nilai.index', ['tahun_ajaran' => $tahunAjaranAktif, 'semester' => $semesterAktif]) }}" class="text-sm text-purple-600 hover:text-purple-900 hover:underline">
                            Lihat Rapor Detail &rarr;
                        </a>
                    </div>

                    @if($nilaiTerbaru->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">KKM</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Predikat</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guru</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach($nilaiTerbaru as $nilai)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $nilai->mataPelajaran?->nama_mapel ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center">{{ $kkmMapelDashboard[$nilai->mata_pelajaran_id] ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center font-semibold">{{ !is_null($nilai->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center">{{ $nilai->predikat ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap">{{ $nilai->guru?->nama_guru ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">
                            Belum ada data nilai yang dapat ditampilkan untuk periode {{ $tahunAjaranAktif ?? '-' }} - Semester {{ $semesterAktif ?? '-' }}.
                            @if($tahunAjaranAktif && $semesterAktif)
                            <br>
                            <a href="{{ route('siswa.nilai.index') }}" class="text-purple-600 hover:underline">Coba lihat periode lain di Rapor Detail</a>.
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            {{-- <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Akademik Lainnya</h3>
                    <p class="text-sm text-gray-600">
                        Pantau pengumuman dan informasi penting lainnya di sini.
                    </p>
                    {{-- Contoh: Jadwal Ujian, Tugas Mendatang, dll. --}}
                {{-- </div>
            </div> --}}

        </div>
    </div>
</x-app-layout>