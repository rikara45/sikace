<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Rekapitulasi Nilai Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Data Nilai</h3>

                    @include('layouts.partials.alert-messages')

                    <form method="GET" action="{{ route('guru.rekap-nilai.index') }}" id="filterRekapNilaiForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                            {{-- 1. Filter Tahun Ajaran --}}
                            <div>
                                <x-input-label for="filter_tahun_ajaran" :value="__('1. Tahun Ajaran')" />
                                <select name="filter_tahun_ajaran" id="filter_tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach ($availableTahunAjaran as $tahun)
                                        <option value="{{ $tahun }}" @selected($selectedTahunAjaran == $tahun)>{{ $tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- 2. Filter Semester --}}
                            <div>
                                <x-input-label for="filter_semester" :value="__('2. Semester')" />
                                <select name="filter_semester" id="filter_semester" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" @if(!$selectedTahunAjaran) disabled @endif onchange="this.form.submit()">
                                    <option value="">-- Pilih Semester --</option>
                                    @if($selectedTahunAjaran)
                                        @foreach ($availableSemester as $sem)
                                            <option value="{{ $sem }}" @selected($selectedSemester == $sem)>Semester {{ $sem }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            {{-- 3. Filter Kelas --}}
                            <div>
                                <x-input-label for="filter_kelas_id" :value="__('3. Kelas')" />
                                <select name="filter_kelas_id" id="filter_kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" @if(!$selectedSemester) disabled @endif onchange="this.form.submit()">
                                    <option value="">-- Pilih Kelas --</option>
                                    @if($selectedSemester && isset($availableKelas) && $availableKelas->count() > 0)
                                        @foreach ($availableKelas as $kelasOption)
                                            <option value="{{ $kelasOption->id }}" @selected($selectedKelasId == $kelasOption->id)>{{ $kelasOption->nama_kelas }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            {{-- 4. Filter Mata Pelajaran --}}
                            <div>
                                <x-input-label for="filter_matapelajaran_id" :value="__('4. Mata Pelajaran')" />
                                <select name="filter_matapelajaran_id" id="filter_matapelajaran_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" @if(!$selectedKelasId) disabled @endif onchange="this.form.submit()">
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                     @if($selectedKelasId && isset($availableMapel) && $availableMapel->count() > 0)
                                        @foreach ($availableMapel as $mapelOption)
                                            <option value="{{ $mapelOption->id }}" @selected($selectedMapelId == $mapelOption->id)>{{ $mapelOption->nama_mapel }} {{ $mapelOption->kode_mapel ? '('.$mapelOption->kode_mapel.')' : '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </form>

                    @if ($showNilaiTable && $kelasModel && $mapelModel)
                        <hr class="my-8">
                        <div class="flex justify-between items-center mb-2">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">
                                    Rekap Nilai: {{ $mapelModel->nama_mapel }} - Kelas {{ $kelasModel->nama_kelas }}
                                </h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    Tahun Ajaran: <span class="font-semibold">{{ $selectedTahunAjaran }}</span> | Semester: <span class="font-semibold">{{ $selectedSemester }}</span>
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('laporan.rekap.nilai.kelas.cetak', ['kelas' => $kelasModel->id, 'tahun_ajaran' => $selectedTahunAjaran, 'semester' => $selectedSemester, 'mapel' => $selectedMapelId]) }}" target="_blank"
                                   class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <i class="fas fa-file-pdf mr-2"></i>Cetak PDF
                                </a>
                            </div>
                        </div>

                        @if($bobotAktif)
                            <div class="my-3 text-xs text-gray-600 p-3 bg-indigo-50 rounded-md border border-indigo-200">
                                <p class="font-semibold mb-1">Pengaturan Penilaian yang Berlaku:</p>
                                <p><strong>Bobot:</strong> Tugas: <span class="font-medium">{{ $bobotAktif->bobot_tugas }}%</span> | UTS: <span class="font-medium">{{ $bobotAktif->bobot_uts }}%</span> | UAS: <span class="font-medium">{{ $bobotAktif->bobot_uas }}%</span></p>
                                <p><strong>KKM:</strong> <span class="font-medium">{{ $bobotAktif->kkm }}</span> |
                                   <strong>Predikat:</strong>
                                   A &ge; <span class="font-medium">{{ $bobotAktif->batas_a }}</span> |
                                   B &ge; <span class="font-medium">{{ $bobotAktif->batas_b }}</span> |
                                   C &ge; <span class="font-medium">{{ $bobotAktif->batas_c }}</span>
                                   (D &lt; {{ $bobotAktif->kkm }})
                                </p>
                            </div>
                        @else
                            <div class="my-3 text-xs text-red-500 p-3 bg-red-50 rounded-md border border-red-200">
                                Pengaturan bobot dan KKM belum diatur untuk mata pelajaran ini pada periode yang dipilih. Predikat mungkin tidak akurat atau menggunakan default sistem.
                            </div>
                        @endif

                        <div class="overflow-x-auto border border-gray-200 rounded-md">
                             <table class="min-w-full divide-y divide-gray-200 border-collapse border border-gray-300">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">No</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">NIS</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Nama Siswa</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Rata-rata Tugas</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">UTS</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">UAS</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Nilai Akhir</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Predikat</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Detail Tugas</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($kelasModel->siswas ?? [] as $index => $siswa)
                                        @php
                                            $nilai = $nilaiData->get($siswa->id);
                                            $rataRataTugas = $nilai ? \App\Models\Nilai::calculateRataRataTugas($nilai->nilai_tugas) : null;
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 text-center border border-gray-300">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 border border-gray-300 text-center">{{ $siswa->nis }}</td>
                                            <td class="px-6 py-4 border border-gray-300 text-center">{{ $siswa->nama_siswa }}</td>
                                            <td class="px-4 py-2 text-center border border-gray-300">{{ !is_null($rataRataTugas) ? number_format($rataRataTugas, 2) : '-' }}</td>
                                            <td class="px-4 py-2 text-center border border-gray-300">{{ $nilai?->nilai_uts ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border border-gray-300">{{ $nilai?->nilai_uas ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border border-gray-300 font-semibold">{{ !is_null($nilai?->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                            <td class="px-4 py-2 text-center border border-gray-300">{{ $nilai?->predikat ?? '-' }}</td>
                                            <td class="px-4 py-2 text-center border border-gray-300">
                                                @if($nilai && !empty($nilai->nilai_tugas) && is_array($nilai->nilai_tugas))
                                                <button type="button" class="text-blue-500 text-xs hover:underline show-detail-tugas"
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
                                         <tr><td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500 border border-gray-300">Tidak ada siswa di kelas ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                         </div>
                    @elseif($selectedTahunAjaran && $selectedSemester && $selectedKelasId && $selectedMapelId)
                        <div class="mt-8 text-center text-gray-500">
                            Tidak ada data nilai ditemukan untuk filter yang dipilih.
                        </div>
                    @else
                        <div class="mt-8 text-center text-gray-500">
                            Silakan lengkapi semua filter di atas untuk menampilkan data nilai.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal untuk detail nilai tugas --}}
    <div id="detailTugasModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center" style="display: none; z-index: 50;">
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
        function submitFormOnChange() {
            document.getElementById('filterRekapNilaiForm').submit();
        }

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
                            if(modalSiswaNama) modalSiswaNama.textContent = `Rincian Nilai Tugas - ${siswaNama}`;
                            if(listDetailTugas) {
                                listDetailTugas.innerHTML = '';
                                if (tugasData && Array.isArray(tugasData) && tugasData.length > 0) {
                                    tugasData.forEach((nilai, index) => {
                                        const li = document.createElement('li');
                                        let nilaiTampil = '-';
                                        if (nilai !== null && !isNaN(parseFloat(nilai))) {
                                            nilaiTampil = parseFloat(nilai).toFixed(2);
                                        }
                                        li.textContent = `Tugas ${index + 1}: ${nilaiTampil}`;
                                        listDetailTugas.appendChild(li);
                                    });
                                } else {
                                    const li = document.createElement('li');
                                    li.textContent = 'Tidak ada rincian nilai tugas.';
                                    listDetailTugas.appendChild(li);
                                }
                            }
                            modal.style.display = 'flex';
                        } catch (e) {
                            console.error("Error parsing tugas JSON for modal atau error lain:", e);
                            alert('Gagal menampilkan detail tugas. Periksa console browser untuk detail.');
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