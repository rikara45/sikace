{{-- resources/views/admin/kenaikan_kelas/form.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Proses Kenaikan Kelas & Kelulusan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @include('layouts.partials.alert-messages')

                    <p class="mb-2 text-sm text-gray-600">
                        Tahun Ajaran Tujuan (Saat Ini Aktif): <span class="font-semibold">{{ $tahunAjaranSaatIni ?? 'Belum Diatur' }}</span>
                    </p>
                    <p class="mb-4 text-sm text-gray-600">
                        Tahun Ajaran Sumber (Sebelumnya): <span class="font-semibold">{{ $tahunAjaranSebelumnya ?? 'Tidak Terdeteksi (Pastikan TA Aktif benar)' }}</span>
                    </p>

                    {{-- Form Filter Kelas Asal --}}
                    <form method="GET" action="{{ route('admin.kenaikan.form') }}" class="mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                            <div>
                                <x-input-label for="kelas_asal_id" :value="__('Pilih Kelas Asal (dari TA: '.$tahunAjaranSebelumnya.')')" />
                                <select name="kelas_asal_id" id="kelas_asal_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                    <option value="">-- Pilih Kelas Asal --</option>
                                    @foreach ($kelasAsalOptions as $kelas)
                                        <option value="{{ $kelas->id }}" @selected(isset($selectedKelasAsalId) && $selectedKelasAsalId == $kelas->id)>
                                            {{ $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    @if(isset($selectedKelasAsalId) && $siswaDiKelasAsal->count() > 0)
                        <form method="POST" action="{{ route('admin.kenaikan.proses') }}">
                            @csrf
                            <input type="hidden" name="tahun_ajaran_tujuan" value="{{ $tahunAjaranSaatIni }}">
                            <input type="hidden" name="kelas_asal_id_processed" value="{{ $selectedKelasAsalId }}">

                            {{-- Fitur Kelas Tujuan Default --}}
                            <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-md">
                                <p class="text-sm text-indigo-700 font-semibold mb-2">Pilih Kelas Tujuan Default untuk Kenaikan Kelas:</p>
                                <div class="flex gap-4 items-center">
                                    <div class="flex-1">
                                        <select name="default_kelas_tujuan_id" id="default_kelas_tujuan_id" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                            <option value="">-- Pilih Kelas Tujuan Default --</option>
                                            @foreach ($kelasTujuanOptions as $kelas)
                                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <x-primary-button id="btnApplyDefaultKelas" type="button" class="whitespace-nowrap">
                                        Terapkan ke Siswa Naik Kelas
                                    </x-primary-button>
                                </div>
                            </div>

                            <div class="overflow-x-auto border border-gray-200 rounded-md">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">No</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">NIS</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Nama Siswa</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Aksi</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Kelas Tujuan (TA: {{ $tahunAjaranSaatIni }})</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($siswaDiKelasAsal as $index => $siswa)
                                            <tr class="siswa-row">
                                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ $index + 1 }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ $siswa->nis }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm">{{ $siswa->nama_siswa }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                                    <input type="hidden" name="promotions[{{ $siswa->id }}][siswa_id]" value="{{ $siswa->id }}">
                                                    <select name="promotions[{{ $siswa->id }}][aksi]" class="block w-full border-gray-300 rounded-md shadow-sm text-xs aksi-select" data-siswa-id="{{ $siswa->id }}">
                                                        <option value="naik">Naik Kelas</option>
                                                        <option value="tinggal">Tinggal Kelas</option>
                                                        <option value="lulus">Lulus</option>
                                                    </select>
                                                </td>
                                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                                    <select name="promotions[{{ $siswa->id }}][kelas_tujuan_id]" class="block w-full border-gray-300 rounded-md shadow-sm text-xs kelas-tujuan-select" data-siswa-id="{{ $siswa->id }}">
                                                        <option value="">-- Pilih Kelas Tujuan --</option>
                                                        @foreach ($kelasTujuanOptions as $kelas)
                                                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-6 flex justify-end">
                                <x-primary-button type="submit" onclick="return confirm('Apakah Anda yakin ingin memproses data ini? Pastikan semua pilihan sudah benar.')">
                                    {{ __('Proses Kenaikan/Kelulusan') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @elseif(isset($selectedKelasAsalId))
                        <p class="text-center text-gray-500 mt-4">Tidak ada siswa aktif ditemukan di kelas asal yang dipilih atau kelas asal belum dipilih.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const defaultKelasTujuanSelect = document.getElementById('default_kelas_tujuan_id');
            const btnApplyDefaultKelas = document.getElementById('btnApplyDefaultKelas');

            function handleAksiChange(selectElement) {
                const siswaId = selectElement.dataset.siswaId;
                const kelasTujuanSelect = document.querySelector(`.kelas-tujuan-select[data-siswa-id="${siswaId}"]`);
                if (selectElement.value === 'lulus') {
                    kelasTujuanSelect.value = '';
                    kelasTujuanSelect.disabled = true;
                } else { // Ini mencakup 'naik' DAN 'tinggal'
                    kelasTujuanSelect.disabled = false;
                }
            }

            document.querySelectorAll('.aksi-select').forEach(selectElement => {
                selectElement.addEventListener('change', function() {
                    handleAksiChange(this);
                });
                // Initial state
                handleAksiChange(selectElement);
            });

            if (btnApplyDefaultKelas && defaultKelasTujuanSelect) {
                btnApplyDefaultKelas.addEventListener('click', function() {
                    const selectedDefaultKelasId = defaultKelasTujuanSelect.value;
                    if (!selectedDefaultKelasId) {
                        alert('Silakan pilih Kelas Tujuan Default terlebih dahulu.');
                        return;
                    }

                    document.querySelectorAll('tr.siswa-row').forEach(row => {
                        const aksiSelect = row.querySelector('.aksi-select');
                        const kelasTujuanSiswaSelect = row.querySelector('.kelas-tujuan-select');

                        if (aksiSelect.value === 'naik') { // HANYA berlaku untuk 'naik'
                            kelasTujuanSiswaSelect.value = selectedDefaultKelasId;
                            kelasTujuanSiswaSelect.disabled = false;
                        } else if (aksiSelect.value === 'lulus') {
                            kelasTujuanSiswaSelect.value = '';
                            kelasTujuanSiswaSelect.disabled = true;
                        } else { // Untuk 'tinggal' atau kondisi lain
                            // Jangan ubah kelas tujuan siswa jika aksinya bukan 'naik'
                            // Pastikan tetap aktif jika bukan 'lulus'
                            kelasTujuanSiswaSelect.disabled = aksiSelect.value === 'lulus';
                        }
                    });
                    alert('Kelas tujuan default telah diterapkan untuk siswa yang "Naik Kelas".');
                });
            }
        });
    </script>
    @endpush
</x-app-layout>