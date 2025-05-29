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
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Notifikasi & Pengingat</h3>

                    @if(!empty($pengaturanBelumLengkap))
                        <div class="mb-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                            <p class="font-bold mb-2">Pengaturan Bobot/KKM Belum Lengkap</p>
                            <p class="text-sm mb-1">Berikut adalah kelas & mata pelajaran yang pengaturan bobot atau KKM-nya mungkin belum Anda set untuk TA {{ $tahunAjaranAktif }}:</p>
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach($pengaturanBelumLengkap as $item)
                                    <li>
                                        <a href="{{ route('guru.nilai.input', ['filter_tahun_ajaran' => $item->tahun_ajaran, 'filter_semester' => $semesterAktif, 'filter_kelas_id' => $item->kelas_id, 'filter_matapelajaran_id' => $item->mapel_id]) }}" class="hover:underline">
                                            Kelas {{ $item->nama_kelas }} - {{ $item->nama_mapel }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                             <p class="text-xs mt-2">Silakan periksa dan lengkapi melalui menu <a href="{{ route('guru.nilai.input') }}" class="font-semibold hover:underline">Input & Pengaturan Nilai</a>.</p>
                        </div>
                    @endif

                    @if(!empty($nilaiBelumLengkap))
                        <div class="mb-4 p-4 bg-orange-100 border-l-4 border-orange-500 text-orange-700">
                            <p class="font-bold mb-2">Nilai Siswa Belum Lengkap</p>
                            <p class="text-sm mb-1">Terdapat siswa yang nilainya belum lengkap untuk TA {{ $tahunAjaranAktif }} - Semester {{ $semesterAktif }} pada:</p>
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach($nilaiBelumLengkap as $item)
                                     <li>
                                        <a href="{{ route('guru.nilai.input', ['filter_tahun_ajaran' => $tahunAjaranAktif, 'filter_semester' => $semesterAktif, 'filter_kelas_id' => $item->kelas_id_raw, 'filter_matapelajaran_id' => $item->mapel_id_raw]) }}" class="hover:underline">
                                           Kelas {{ $item->nama_kelas }} - {{ $item->nama_mapel }} (Baru {{ $item->siswa_dinilai }} dari {{ $item->total_siswa }} siswa)
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <p class="text-xs mt-2">Silakan lengkapi nilai melalui menu <a href="{{ route('guru.nilai.input') }}" class="font-semibold hover:underline">Input & Pengaturan Nilai</a>.</p>
                        </div>
                    @endif

                    @if(empty($pengaturanBelumLengkap) && empty($nilaiBelumLengkap))
                        <p class="text-sm text-gray-500">Tidak ada notifikasi penting saat ini.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>