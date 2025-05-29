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
                    <div class="flex justify-between items-center mb-4 flex-wrap">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2 md:mb-0">
                            Ringkasan Nilai Terbaru ({{ $tahunAjaranAktif ?? '-' }} - Semester {{ $semesterAktif ?? '-' }})
                        </h3>
                        <a href="{{ route('siswa.nilai.index', ['tahun_ajaran' => $tahunAjaranAktif, 'semester' => $semesterAktif]) }}" 
                           class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Lihat Rapor Detail <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>

                    @if($nilaiTerbaru->count() > 0)
                        <div class="overflow-x-auto border border-gray-200 rounded-md">
                            <table class="min-w-full divide-y divide-gray-200 border-collapse border border-gray-300">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Mata Pelajaran</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">KKM</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Nilai Akhir</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Predikat</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Guru</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 text-sm">
                                    @foreach($nilaiTerbaru as $nilai)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap border border-gray-300 text-center">{{ $nilai->mataPelajaran?->nama_mapel ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center border border-gray-300">{{ $kkmMapelDashboard[$nilai->mata_pelajaran_id] ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center font-semibold border border-gray-300">{{ !is_null($nilai->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-center border border-gray-300">{{ $nilai->predikat ?? '-' }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap border border-gray-300 text-center">{{ $nilai->guru?->nama_guru ?? '-' }}</td>
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
        </div>
    </div>
</x-app-layout>