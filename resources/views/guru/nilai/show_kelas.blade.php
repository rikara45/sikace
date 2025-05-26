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
                    <div class="mb-4">
                         <a href="{{ route('guru.nilai.create') }}" class="text-sm text-blue-600 hover:text-blue-900">&larr; Kembali ke Pilih Kelas/Mapel untuk Input</a>
                    </div>

                    {{-- Form Filter Tahun Ajaran, Semester, dan Mata Pelajaran --}}
                    <form method="GET" action="{{ route('guru.nilai.kelas', $kelas->id) }}" class="mb-6 p-4 bg-gray-50 rounded-md border">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="filter_tahun_ajaran" :value="__('Tahun Ajaran')" class="text-xs"/>
                                <select name="filter_tahun_ajaran" id="filter_tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    @forelse ($availableTahunAjaranOptions as $tahun)
                                        <option value="{{ $tahun }}" @selected($filterTahunAjaran == $tahun)>{{ $tahun }}</option>
                                    @empty
                                        <option value="{{ $filterTahunAjaran }}">{{ $filterTahunAjaran }}</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <x-input-label for="filter_semester" :value="__('Semester')" class="text-xs"/>
                                <select name="filter_semester" id="filter_semester" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    @foreach ($availableSemesterOptions as $sem)
                                        <option value="{{ $sem }}" @selected($filterSemester == $sem)>Semester {{ $sem }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="matapelajaran_id" :value="__('Mata Pelajaran')" class="text-xs"/>
                                <select name="matapelajaran_id" id="matapelajaran_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    @if($mapelsDiajar->isEmpty())
                                        <option value="">Tidak ada mapel diajar</option>
                                    @else
                                        @foreach ($mapelsDiajar as $mapelOption)
                                            <option value="{{ $mapelOption->id }}" @selected($filterMapelId == $mapelOption->id)>
                                                {{ $mapelOption->nama_mapel }} {{ $mapelOption->kode_mapel ? '('.$mapelOption->kode_mapel.')' : '' }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="flex items-center space-x-2">
                                <x-primary-button type="submit">
                                    {{ __('Tampilkan Nilai') }}
                                </x-primary-button>
                                @if($filterMapelId && $mapelAktif && $nilaiKelas->count() > 0)
                                {{-- <a href="{{ route('laporan.nilai.kelas.export', ['kelas' => $kelas->id, 'tahun_ajaran' => $filterTahunAjaran, 'semester' => $filterSemester, 'mapel' => $filterMapelId]) }}" --}}
                                <a href="#"
                                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Export Excel
                                </a>
                                @endif
                            </div>
                        </div>
                    </form>
                    {{-- Akhir Form Filter --}}

                    @include('layouts.partials.alert-messages')

                     @if($mapelAktif)
                        <h4 class="text-md font-semibold mb-1">Menampilkan Nilai: {{ $mapelAktif->nama_mapel }}</h4>
                        <p class="text-sm text-gray-600">Kelas: {{ $kelas->nama_kelas }} | Tahun Ajaran: <span class="font-semibold">{{ $filterTahunAjaran }}</span> | Semester: <span class="font-semibold">{{ $filterSemester }}</span></p>

                        @if($bobotAktif)
                            <div class="my-3 text-xs text-gray-600 p-3 bg-indigo-50 rounded-md">
                                <span class="font-semibold">Bobot yang Digunakan untuk Periode Ini:</span>
                                Tugas: <span class="font-medium">{{ $bobotAktif->bobot_tugas }}%</span> |
                                UTS: <span class="font-medium">{{ $bobotAktif->bobot_uts }}%</span> |
                                UAS: <span class="font-medium">{{ $bobotAktif->bobot_uas }}%</span>
                            </div>
                        @else
                            <div class="my-3 text-xs text-red-500 p-3 bg-red-50 rounded-md">
                                Bobot penilaian belum diatur untuk mata pelajaran ini pada periode yang dipilih. Nilai akhir mungkin menggunakan default atau tidak akurat.
                            </div>
                        @endif

                        <div class="overflow-x-auto mt-4">
                             <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border">NIS</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border">Nama Siswa</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Rata2 Tugas</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">UTS</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">UAS</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Nilai Akhir</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Predikat</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border">Detail Tugas</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($kelas->siswas ?? [] as $index => $siswa)
                                        @php
                                            $nilai = $nilaiKelas->get($siswa->id);
                                            $rataRataTugas = $nilai ? \App\Models\Nilai::calculateRataRataTugas($nilai->nilai_tugas) : null;
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 text-center border">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 border">{{ $siswa->nis }}</td>
                                            <td class="px-6 py-4 border">{{ $siswa->nama_siswa }}</td>
                                            <td class="px-4 py-2 text-center border">{{ !is_null($rataRataTugas) ? number_format($rataRataTugas, 2) : '-' }}</td>
                                            <td class="px-4 py-2 text-center border">{{ $nilai?->nilai_uts ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border">{{ $nilai?->nilai_uas ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border font-semibold">{{ !is_null($nilai?->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                            <td class="px-4 py-2 text-center border">{{ $nilai?->predikat ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border">
                                                @if($nilai && !empty($nilai->nilai_tugas))
                                                <button type="button" class="text-blue-500 text-xs show-detail-tugas"
                                                        data-tugas='@json($nilai->nilai_tugas)'
                                                        data-siswa-nama="{{ $siswa->nama_siswa }}">
                                                    Lihat Rincian
                                                </button>
                                                @else
                                                -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                         <tr><td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada siswa di kelas ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                         </div>
                     @elseif($mapelsDiajar->isEmpty())
                        <p class="text-center text-gray-500 py-4">Anda tidak mengajar mata pelajaran apapun di kelas ini pada tahun ajaran <span class="font-semibold">{{ $filterTahunAjaran }}</span>.</p>
                     @else
                         <p class="text-center text-gray-500 py-4">Silakan pilih tahun ajaran, semester, dan mata pelajaran untuk melihat rekap nilai.</p>
                     @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk detail nilai tugas (sama seperti sebelumnya) --}}
    <div id="detailTugasModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="display: none;">
        <div class="relative p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalSiswaNama">Rincian Nilai Tugas</h3>
                <div class="mt-2 px-7 py-3 max-h-60 overflow-y-auto">
                    <ul id="listDetailTugas" class="list-disc list-inside text-left">
                        {{-- Item nilai tugas akan diisi oleh JS --}}
                    </ul>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="closeDetailTugasModal" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('detailTugasModal');
            if(modal) {
                const modalSiswaNama = document.getElementById('modalSiswaNama');
                const listDetailTugas = document.getElementById('listDetailTugas');
                const closeButton = document.getElementById('closeDetailTugasModal');

                document.querySelectorAll('.show-detail-tugas').forEach(button => {
                    button.addEventListener('click', function() {
                        const tugasDataJson = this.dataset.tugas;
                        const siswaNama = this.dataset.siswaNama;
                        try {
                            const tugasData = JSON.parse(tugasDataJson);
                            modalSiswaNama.textContent = `Rincian Nilai Tugas - ${siswaNama}`;
                            listDetailTugas.innerHTML = '';

                            if (tugasData && tugasData.length > 0) {
                                tugasData.forEach((nilai, index) => {
                                    const li = document.createElement('li');
                                    li.textContent = `Tugas ${index + 1}: ${parseFloat(nilai).toFixed(2)}`;
                                    listDetailTugas.appendChild(li);
                                });
                            } else {
                                const li = document.createElement('li');
                                li.textContent = 'Tidak ada rincian nilai tugas.';
                                listDetailTugas.appendChild(li);
                            }
                            modal.style.display = 'flex';
                        } catch (e) {
                            console.error("Error parsing tugas JSON for modal:", e);
                            alert('Gagal menampilkan detail tugas.');
                        }
                    });
                });

                if(closeButton) {
                    closeButton.addEventListener('click', function() {
                        modal.style.display = 'none';
                    });
                }

                modal.addEventListener('click', function(event) {
                    if (event.target === modal) {
                        modal.style.display = 'none';
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>