<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Guru Dashboard') }}
            </h2>
            </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Selamat datang, {{ $namaGuru }}!</h3>
                    <p class="text-sm text-gray-600">
                        Tahun Ajaran Aktif: <span class="font-medium">{{ $tahunAjaranAktif ?? 'Belum diatur' }}</span> |
                        Semester Aktif: <span class="font-medium">{{ $semesterAktif ? 'Semester ' . $semesterAktif : 'Belum diatur' }}</span>
                    </p>
                </div>

                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-md font-semibold text-gray-700 mb-2">Mata Pelajaran Diampu (TA: {{ $tahunAjaranAktif ?? '-' }})</h4>
                            @if($mapelDiampu->count() > 0)
                                <ul class="list-disc list-inside text-sm space-y-1">
                                    @foreach($mapelDiampu as $mapel)
                                        <li>{{ $mapel->nama_mapel }} {{ $mapel->kode_mapel ? '('.$mapel->kode_mapel.')' : '' }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">Anda tidak mengampu mata pelajaran apapun di tahun ajaran ini.</p>
                            @endif
                        </div>
                        <br>
                        <div>
                            <h4 class="text-md font-semibold text-gray-700 mb-2">Kelas Diajar (TA: {{ $tahunAjaranAktif ?? '-' }})</h4>
                            @if($kelasDiajar->count() > 0)
                                <ul class="list-disc list-inside text-sm space-y-1">
                                    @foreach($kelasDiajar as $kelas)
                                        <li>
                                            {{ $kelas->nama_kelas }}
                                            @if($kelas->mataPelajarans->where('pivot.guru_id', Auth::user()->guru->id)->where('pivot.tahun_ajaran', $tahunAjaranAktif)->count() > 0)
                                                <span class="text-xs text-gray-500">
                                                    (Mapel:
                                                    @foreach($kelas->mataPelajarans->where('pivot.guru_id', Auth::user()->guru->id)->where('pivot.tahun_ajaran', $tahunAjaranAktif) as $mapelDiKelas)
                                                        {{ $mapelDiKelas->nama_mapel }}{{ !$loop->last ? ', ' : '' }}
                                                    @endforeach
                                                    )
                                                </span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">Anda tidak mengajar di kelas manapun pada tahun ajaran ini.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4"> {{-- Tambahkan space-y-4 --}}
                    <h3 class="text-lg font-semibold text-gray-800">Notifikasi & Pengingat</h3>

                    @if(!empty($pengaturanBelumLengkap))
                        <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-md">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 pt-0.5">
                                    <i class="fas fa-cogs fa-lg text-yellow-500"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-md font-semibold text-yellow-700 mb-1">Pengaturan Bobot/KKM Belum Lengkap</h4>
                                    <p class="text-sm text-yellow-600 mb-1">Berikut adalah kelas & mata pelajaran yang pengaturan bobot atau KKM-nya mungkin belum Anda set untuk TA {{ $tahunAjaranAktif }}:</p>
                                    <ul class="list-disc list-inside text-sm text-yellow-600 space-y-1">
                                        @foreach($pengaturanBelumLengkap as $item)
                                            <li>
                                                <a href="{{ route('guru.nilai.input', ['filter_tahun_ajaran' => $item->tahun_ajaran, 'filter_semester' => $semesterAktif, 'filter_kelas_id' => $item->kelas_id, 'filter_matapelajaran_id' => $item->mapel_id]) }}" class="hover:underline hover:text-yellow-800">
                                                    Kelas {{ $item->nama_kelas }} - {{ $item->nama_mapel }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                     <p class="text-xs text-yellow-600 mt-2">Silakan periksa dan lengkapi melalui menu <a href="{{ route('guru.nilai.input') }}" class="font-semibold hover:underline hover:text-yellow-800">Input & Pengaturan Nilai</a>.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!empty($nilaiBelumLengkap))
                        <div class="p-4 bg-orange-50 border-l-4 border-orange-400 rounded-r-md">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 pt-0.5">
                                    <i class="fas fa-edit fa-lg text-orange-500"></i>
                                </div>
                                <div class="ml-3">
                                    <h4 class="text-md font-semibold text-orange-700 mb-1">Nilai Siswa Belum Lengkap</h4>
                                    <p class="text-sm text-orange-600 mb-1">Terdapat siswa yang nilainya belum lengkap untuk TA {{ $tahunAjaranAktif }} - Semester {{ $semesterAktif }} pada:</p>
                                    <ul class="list-disc list-inside text-sm text-orange-600 space-y-1">
                                        @foreach($nilaiBelumLengkap as $item)
                                             <li>
                                                <a href="{{ route('guru.nilai.input', ['filter_tahun_ajaran' => $tahunAjaranAktif, 'filter_semester' => $semesterAktif, 'filter_kelas_id' => $item->kelas_id_raw, 'filter_matapelajaran_id' => $item->mapel_id_raw]) }}" class="hover:underline hover:text-orange-800">
                                                   Kelas {{ $item->nama_kelas }} - {{ $item->nama_mapel }} (Baru {{ $item->siswa_dinilai }} dari {{ $item->total_siswa }} siswa)
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <p class="text-xs text-orange-600 mt-2">Silakan lengkapi nilai melalui menu <a href="{{ route('guru.nilai.input') }}" class="font-semibold hover:underline hover:text-orange-800">Input & Pengaturan Nilai</a>.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(empty($pengaturanBelumLengkap) && empty($nilaiBelumLengkap))
                        <div class="p-4 bg-green-50 border-l-4 border-green-400 rounded-r-md">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 pt-0.5">
                                    <i class="fas fa-check-circle fa-lg text-green-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-700">Tidak ada notifikasi penting saat ini.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>