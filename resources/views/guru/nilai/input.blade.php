<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input dan Pengaturan Nilai Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- 1. FORM FILTER KONTEKS --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pilih Konteks Penilaian</h3>
                    @include('layouts.partials.alert-messages')
                    <form method="GET" action="{{ route('guru.nilai.input') }}" id="filterNilaiForm" class="space-y-4">
                        {{-- Konten form filter --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="filter_tahun_ajaran" :value="__('1. Tahun Ajaran')" />
                                <select name="filter_tahun_ajaran" id="filter_tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                    <option value="">-- Pilih Tahun Ajaran --</option>
                                    @foreach ($availableTahunAjaran ?? [] as $tahun)
                                        <option value="{{ $tahun }}" @selected(isset($selectedTahunAjaran) && $selectedTahunAjaran == $tahun)>{{ $tahun }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="filter_semester" :value="__('2. Semester')" />
                                <select name="filter_semester" id="filter_semester" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" @if(!isset($selectedTahunAjaran) || !$selectedTahunAjaran) disabled @endif onchange="this.form.submit()">
                                    <option value="">-- Pilih Semester --</option>
                                    @if(isset($selectedTahunAjaran) && $selectedTahunAjaran)
                                        @foreach ($availableSemester ?? [] as $sem)
                                            <option value="{{ $sem }}" @selected(isset($selectedSemester) && $selectedSemester == $sem)>Semester {{ $sem }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div>
                                <x-input-label for="filter_kelas_id" :value="__('3. Kelas')" />
                                <select name="filter_kelas_id" id="filter_kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" @if(!isset($selectedSemester) || !$selectedSemester) disabled @endif onchange="this.form.submit()">
                                    <option value="">-- Pilih Kelas --</option>
                                    @if(isset($selectedSemester) && $selectedSemester && isset($availableKelas) && $availableKelas->count() > 0)
                                        @foreach ($availableKelas as $kelasOption)
                                            <option value="{{ $kelasOption->id }}" @selected(isset($selectedKelasId) && $selectedKelasId == $kelasOption->id)>{{ $kelasOption->nama_kelas }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div>
                                <x-input-label for="filter_matapelajaran_id" :value="__('4. Mata Pelajaran')" />
                                <select name="filter_matapelajaran_id" id="filter_matapelajaran_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" @if(!isset($selectedKelasId) || !$selectedKelasId) disabled @endif onchange="this.form.submit()">
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                     @if(isset($selectedKelasId) && $selectedKelasId && isset($availableMapel) && $availableMapel->count() > 0)
                                        @foreach ($availableMapel as $mapelOption)
                                            <option value="{{ $mapelOption->id }}" @selected(isset($selectedMapelId) && $selectedMapelId == $mapelOption->id)>{{ $mapelOption->nama_mapel }} {{ $mapelOption->kode_mapel ? '('.$mapelOption->kode_mapel.')' : '' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        @if(isset($selectedTahunAjaran) && $selectedTahunAjaran && isset($currentTahunAjaran) && isset($currentSemester) && ($selectedTahunAjaran != $currentTahunAjaran || (isset($selectedSemester) && $selectedSemester != $currentSemester)))
                            <p class="text-xs text-gray-500 mt-2">
                                Periode aktif sistem saat ini: {{ $currentTahunAjaran }} - Semester {{ $currentSemester }}.
                                <a href="{{ route('guru.nilai.input', ['filter_tahun_ajaran' => $currentTahunAjaran, 'filter_semester' => $currentSemester]) }}" class="text-blue-600 hover:underline">Gunakan Periode Aktif Sistem</a>.
                            </p>
                        @endif
                    </form>
                </div>
            </div>

            @if (isset($showInputSection) && $showInputSection && isset($kelas) && isset($mapel))
                <div x-data="{ isFullScreen: false }">

                    <div 
                        class="mt-6"
                        :class="{ 'fullscreen-container fixed inset-0 z-50 bg-white overflow-auto p-2 sm:p-4': isFullScreen }"
                    >
                        
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="form-bobot-section" x-show="!isFullScreen">
                            {{-- Konten form bobot --}}
                            <div class="p-6 text-gray-900">
                               <h3 class="text-lg font-medium text-gray-900 mb-1">Pengaturan Bobot Penilaian (%)</h3>
                                <p class="text-sm text-gray-500 mb-4">Total ketiga bobot harus 100%. Berlaku untuk: <span class="font-semibold">{{ $mapel->nama_mapel }}</span> di Kelas <span class="font-semibold">{{ $kelas->nama_kelas }}</span> ({{ $selectedTahunAjaran }} - Sem {{ $selectedSemester }})</p>
                                @if(session('success_bobot') || $errors->has('bobot_total'))
                                    <div class="mb-4">
                                        @if(session('success_bobot')) <div class="p-3 rounded bg-green-100 text-green-700 text-sm">{{ session('success_bobot') }}</div> @endif
                                        <x-input-error :messages="$errors->get('bobot_total')" class="mt-1 text-sm" />
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('guru.nilai.simpanBobot') }}" id="simpanBobotForm">
                                     @csrf
                                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                                    <input type="hidden" name="matapelajaran_id" value="{{ $selectedMapelId }}">
                                    <input type="hidden" name="tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                                    <input type="hidden" name="filter_tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                                    <input type="hidden" name="filter_semester" value="{{ $selectedSemester }}">
                                    <input type="hidden" name="filter_kelas_id" value="{{ $selectedKelasId }}">
                                    <input type="hidden" name="filter_matapelajaran_id" value="{{ $selectedMapelId }}">

                                    <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4 items-end">
                                        <div class="sm:col-span-1">
                                            <x-input-label for="bobot_tugas" :value="__('Tugas')" />
                                            <x-text-input id="bobot_tugas" type="number" name="bobot_tugas" :value="old('bobot_tugas', $bobot?->bobot_tugas ?? 30)" min="0" max="100" required class="block mt-1 w-full text-sm"/>
                                            <x-input-error :messages="$errors->get('bobot_tugas')" class="mt-1 text-sm" />
                                        </div>
                                        <div class="sm:col-span-1">
                                            <x-input-label for="bobot_uts" :value="__('UTS')" />
                                            <x-text-input id="bobot_uts" type="number" name="bobot_uts" :value="old('bobot_uts', $bobot?->bobot_uts ?? 30)" min="0" max="100" required class="block mt-1 w-full text-sm"/>
                                            <x-input-error :messages="$errors->get('bobot_uts')" class="mt-1 text-sm" />
                                        </div>
                                        <div class="sm:col-span-1">
                                            <x-input-label for="bobot_uas" :value="__('UAS')" />
                                            <x-text-input id="bobot_uas" type="number" name="bobot_uas" :value="old('bobot_uas', $bobot?->bobot_uas ?? 40)" min="0" max="100" required class="block mt-1 w-full text-sm"/>
                                            <x-input-error :messages="$errors->get('bobot_uas')" class="mt-1 text-sm" />
                                        </div>
                                        <div class="sm:col-span-3 md:col-span-1">
                                            <x-primary-button type="submit" class="w-full md:w-auto">
                                                {{ __('Simpan Bobot') }}
                                            </x-primary-button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6" id="form-kkm-section" x-show="!isFullScreen">
                            <div class="p-6 text-gray-900">
                                <h3 class="text-lg font-medium text-gray-900 mb-1">Pengaturan KKM</h3>
                                <p class="text-sm text-gray-500 mb-2">Rentang predikat A, B, C akan dihitung otomatis berdasarkan KKM ini. Predikat D adalah untuk nilai di bawah KKM.</p>

                                @if(session('success_kkm') || $errors->has('kkm') || $errors->has('predikat_batas'))
                                    <div class="mb-4">
                                        @if(session('success_kkm')) <div class="p-3 rounded bg-green-100 text-green-700 text-sm">{{ session('success_kkm') }}</div> @endif
                                        <x-input-error :messages="$errors->get('kkm')" class="mt-1 text-sm" />
                                        <x-input-error :messages="$errors->get('predikat_batas')" class="mt-1 text-sm text-red-600 font-semibold" />
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('guru.nilai.simpanKkm') }}" id="simpanKkmForm">
                                     @csrf
                                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                                    <input type="hidden" name="matapelajaran_id" value="{{ $selectedMapelId }}">
                                    <input type="hidden" name="tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                                    <input type="hidden" name="filter_tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                                    <input type="hidden" name="filter_semester" value="{{ $selectedSemester }}">
                                    <input type="hidden" name="filter_kelas_id" value="{{ $selectedKelasId }}">
                                    <input type="hidden" name="filter_matapelajaran_id" value="{{ $selectedMapelId }}">

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                        <div class="md:col-span-2">
                                            <x-input-label for="kkm" :value="__('KKM (0-100)')" />
                                            <x-text-input id="kkm" type="number" name="kkm" :value="old('kkm', $bobot?->kkm ?? 70)" min="0" max="100" required class="block mt-1 w-full text-sm"/>
                                        </div>
                                        <div class="md:col-span-1">
                                            <x-primary-button type="submit" class="w-full md:w-auto">
                                                {{ __('Simpan KKM & Hitung Predikat') }}
                                            </x-primary-button>
                                        </div>
                                    </div>
                                </form>
                                
                                {{-- Kembalikan tampilan rentang predikat --}}
                                @if($bobot && $bobot->kkm > 0)
                                <div class="mt-4 text-sm p-3 bg-blue-50 border border-blue-200 rounded-md">
                                    <p class="font-semibold">Rentang Predikat yang Tersimpan Saat Ini (berdasarkan KKM: {{ $bobot->kkm }}):</p>
                                    <p>D: Nilai &lt; {{ $bobot->kkm }}</p>
                                    <p>C: {{ $bobot->batas_c }} - {{ $bobot->batas_b > $bobot->batas_c ? $bobot->batas_b - 1 : ($bobot->batas_a > $bobot->batas_c ? $bobot->batas_a - 1 : 100) }}</p>
                                    <p>B: {{ $bobot->batas_b }} - {{ $bobot->batas_a > $bobot->batas_b ? $bobot->batas_a - 1 : 100 }}</p>
                                    <p>A: {{ $bobot->batas_a }} - 100</p>
                                </div>
                                @else
                                <div class="mt-4 text-sm p-3 bg-yellow-50 border border-yellow-300 rounded-md">
                                    <p>Pengaturan KKM belum disimpan untuk konteks ini. Rentang predikat akan dihitung setelah KKM disimpan.</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div 
                            class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6"
                            id="form-nilai-siswa-container" 
                        >
                            <div class="p-6 text-gray-900">
                                @if (session('success_nilai')) <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm">{{ session('success_nilai') }}</div>@endif
                                @if (session('error_nilai')) <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm">{{ session('error_nilai') }}</div>@endif
                                
                                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2 sm:mb-0">Daftar Siswa dan Input Nilai</h3>
                                    <button @click="isFullScreen = !isFullScreen" 
                                            class="inline-flex items-center px-3 py-1.5 sm:px-4 sm:py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest transition ease-in-out duration-150 self-start sm:self-auto ml-0 sm:ml-auto"
                                            :class="isFullScreen ? 'bg-gray-600 hover:bg-gray-700 active:bg-gray-800 focus:ring-gray-500' : 'bg-blue-600 hover:bg-blue-700 active:bg-blue-800 focus:ring-blue-500'">
                                        <i class="fas mr-1 sm:mr-2" :class="isFullScreen ? 'fa-compress-arrows-alt' : 'fa-expand-arrows-alt'"></i>
                                        <span x-text="isFullScreen ? 'Kembali ke mode normal' : 'Mode tampilan penuh'"></span>
                                    </button>
                                </div>
                                
                                <form method="POST" action="{{ route('guru.nilai.store') }}">
                                    @csrf
                                    {{-- Hidden inputs --}}
                                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                                    <input type="hidden" name="matapelajaran_id" value="{{ $selectedMapelId }}">
                                    <input type="hidden" name="tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                                    <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                                    <input type="hidden" name="filter_tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                                    <input type="hidden" name="filter_semester" value="{{ $selectedSemester }}">
                                    <input type="hidden" name="filter_kelas_id" value="{{ $selectedKelasId }}">
                                    <input type="hidden" name="filter_matapelajaran_id" value="{{ $selectedMapelId }}">

                                    <div class="overflow-x-auto border border-gray-200 rounded-md">
                                        <table class="min-w-full divide-y divide-gray-200 border-collapse border border-gray-300" id="tabelInputNilai" style="table-layout: fixed;">
                                            <thead class="bg-gray-100">
                                                <tr id="headerNilaiRow">
                                                    <th class="w-10 sm:w-12 px-1 sm:px-2 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider sticky left-0 bg-gray-100 z-20 border border-gray-300">No</th>
                                                    <th class="w-20 sm:w-24 px-2 sm:px-3 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">NIS</th>
                                                    <th class="w-36 sm:w-48 px-2 sm:px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Nama Siswa</th>
                                                    <th class="px-2 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider th-add-tugas border border-gray-300" style="min-width: 40px sm:min-width: 50px;">
                                                        <button type="button" id="addTugasColumnBtn" class="p-1 bg-green-500 text-white rounded-full hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300" title="Tambah Kolom Tugas"><i class="fas fa-plus text-xs"></i></button>
                                                    </th>
                                                    <th class="px-2 sm:px-3 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300" style="min-width: 70px sm:min-width: 80px;">UTS</th>
                                                    <th class="px-2 sm:px-3 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300" style="min-width: 70px sm:min-width: 80px;">UAS</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                 @if(isset($siswaList) && $siswaList->count() > 0)
                                                    @foreach ($siswaList as $index => $siswa)
                                                        @php
                                                            $nilaiSiswa = isset($existingGrades) ? $existingGrades->get($siswa->id) : null;
                                                        @endphp
                                                        <tr class="hover:bg-gray-50" data-siswa-id="{{ $siswa->id }}">
                                                            <td class="w-10 sm:w-12 px-1 sm:px-2 py-2 whitespace-nowrap text-sm text-gray-700 text-center sticky left-0 bg-white z-10 border border-gray-300">{{ $index + 1 }}</td>
                                                            <td class="w-20 sm:w-24 px-2 sm:px-3 py-2 whitespace-nowrap text-sm text-gray-900 text-center border border-gray-300">{{ $siswa->nis }}</td>
                                                            <td class="w-36 sm:w-48 px-2 sm:px-4 py-2 whitespace-nowrap text-sm text-gray-900 text-left border border-gray-300">{{ $siswa->nama_siswa }}</td>
                                                            <td data-placeholder-siswa-id="{{ $siswa->id }}" class="tugas-tambahan-placeholder border-r border-gray-300"></td>
                                                            <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-sm text-gray-500 text-center border border-gray-300">
                                                                 <input type="number" step="0.01" min="0" max="100"
                                                                       name="grades[{{ $siswa->id }}][nilai_uts]"
                                                                       value="{{ old('grades.'.$siswa->id.'.nilai_uts', $nilaiSiswa?->nilai_uts) }}"
                                                                       class="w-16 sm:w-20 p-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs sm:text-sm">
                                                                 <x-input-error :messages="$errors->get('grades.'.$siswa->id.'.nilai_uts')" class="mt-1 text-xs" />
                                                            </td>
                                                            <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-sm text-gray-500 text-center border border-gray-300">
                                                                 <input type="number" step="0.01" min="0" max="100"
                                                                       name="grades[{{ $siswa->id }}][nilai_uas]"
                                                                       value="{{ old('grades.'.$siswa->id.'.nilai_uas', $nilaiSiswa?->nilai_uas) }}"
                                                                       class="w-16 sm:w-20 p-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs sm:text-sm">
                                                                  <x-input-error :messages="$errors->get('grades.'.$siswa->id.'.nilai_uas')" class="mt-1 text-xs" />
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                     {{-- ... (pesan siswa tidak ditemukan) ... --}}
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <x-primary-button type="submit" class="w-full sm:w-auto">
                                            {{ __('Simpan Semua Nilai') }}
                                        </x-primary-button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div> {{-- End of Alpine.js x-data container --}}
                @else
                     {{-- ... (pesan untuk melengkapi filter) ... --}}
            @endif

        </div>
    </div>

    @push('scripts')
    {{-- ... (JavaScript Anda yang sudah ada) ... --}}
    <script>
        function submitFilterFormOnChange() {
            document.getElementById('filterNilaiForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const simpanBobotForm = document.getElementById('simpanBobotForm');
            if (simpanBobotForm) {
                simpanBobotForm.addEventListener('submit', function(event) {
                    if (!confirm("Jika Anda mengganti bobot nilai, Mohon untuk menyimpan ulang nilai.")) {
                        event.preventDefault();
                    }
                });
            }

            const simpanKkmForm = document.getElementById('simpanKkmForm');
            if (simpanKkmForm) {
                simpanKkmForm.addEventListener('submit', function(event) {
                    if (!confirm("Jika Anda mengganti KKM, Mohon untuk menyimpan ulang nilai.")) {
                        event.preventDefault();
                    }
                });
            }

            const addTugasBtn = document.getElementById('addTugasColumnBtn');
            const tabelNilai = document.getElementById('tabelInputNilai');
            const headerRow = tabelNilai.querySelector('thead tr#headerNilaiRow');
            const thButtonPenambah = headerRow.querySelector('th.th-add-tugas'); 
            let initialMaxTugasCount = parseInt("{{ $maxNilaiTugasCount ?? 0 }}");

            function createAndAppendTugasColumn(tugasKe, nilaiTugasUntukSiswa = {}) {
                const newHeader = document.createElement('th');
                newHeader.classList.add('px-2', 'sm:px-3', 'py-3', 'text-center', 'text-xs', 'font-medium', 'text-gray-600', 'uppercase', 'tracking-wider', 'whitespace-nowrap', 'th-tugas', 'border', 'border-gray-300');
                newHeader.dataset.tugasKe = tugasKe;
                newHeader.style.minWidth = '100px';
                
                const textNode = document.createTextNode(`Tugas ${tugasKe} `);
                newHeader.appendChild(textNode);
                headerRow.insertBefore(newHeader, thButtonPenambah);

                const bodyRows = tabelNilai.querySelectorAll('tbody tr');
                bodyRows.forEach(row => {
                    const siswaId = row.dataset.siswaId;
                    if (!siswaId || row.querySelectorAll('td').length <= 2) return;

                    const newCell = document.createElement('td');
                    newCell.classList.add('px-2', 'sm:px-3', 'py-2', 'whitespace-nowrap', 'text-sm', 'text-gray-500', 'text-center', 'td-tugas', 'border', 'border-gray-300');
                    newCell.dataset.tugasKe = tugasKe;
                    
                    const nilaiSiswaTugasIni = nilaiTugasUntukSiswa[siswaId] && nilaiTugasUntukSiswa[siswaId][tugasKe -1] !== undefined ? nilaiTugasUntukSiswa[siswaId][tugasKe -1] : null;

                    const newInput = document.createElement('input');
                    newInput.type = 'number';
                    newInput.step = '0.01';
                    newInput.min = '0';
                    newInput.max = '100';
                    newInput.name = `grades[${siswaId}][nilai_tugas][]`;
                    newInput.value = nilaiSiswaTugasIni;
                    newInput.classList.add('w-16', 'sm:w-20', 'p-1', 'border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'rounded-md', 'shadow-sm', 'text-xs', 'sm:text-sm', 'nilai-tugas-input');
                    newCell.appendChild(newInput);

                    const placeholderCell = row.querySelector('td.tugas-tambahan-placeholder');
                     if (placeholderCell) {
                         row.insertBefore(newCell, placeholderCell);
                     } else { 
                         const utsCell = Array.from(row.children).find(td => td.querySelector('input[name*="[nilai_uts]"]'));
                         if (utsCell) row.insertBefore(newCell, utsCell);
                     }
                });
            }
            
            function updateRemoveButtons() {
                const allTugasHeaders = headerRow.querySelectorAll('th.th-tugas');
                allTugasHeaders.forEach((th, index) => {
                    let removeBtn = th.querySelector('.remove-tugas-column');
                    if (removeBtn) removeBtn.remove();

                    if (index === allTugasHeaders.length - 1 && allTugasHeaders.length > 1) {
                        removeBtn = document.createElement('button');
                        removeBtn.type = 'button';
                        removeBtn.classList.add('ml-1', 'p-1', 'bg-red-500', 'text-white', 'rounded-full', 'hover:bg-red-600', 'focus:outline-none', 'focus:ring-2', 'focus:ring-red-300', 'text-xs', 'remove-tugas-column');
                        const tugasKe = parseInt(th.dataset.tugasKe);
                        removeBtn.title = `Hapus Kolom Tugas ${tugasKe}`;
                        removeBtn.innerHTML = '<i class="fas fa-times text-xs" style="font-size: 0.6rem; line-height: 1;"></i>';
                        
                        removeBtn.addEventListener('click', function(event) {
                            event.preventDefault();
                            if (confirm("Yakin ingin menghapus kolom Tugas " + tugasKe + "? Semua nilai pada kolom ini akan hilang jika belum disimpan.")) {
                                th.remove(); 
                                const bodyRows = tabelNilai.querySelectorAll('tbody tr');
                                bodyRows.forEach(row => {
                                    const cellToRemove = row.querySelector(`td[data-tugas-ke="${tugasKe}"]`);
                                    if (cellToRemove) cellToRemove.remove();
                                });
                                updateRemoveButtons(); 
                            }
                        });
                        th.appendChild(removeBtn);
                    }
                });
            }

            if (tabelNilai) {
                let nilaiTugasAwalPerSiswa = {};
                if (window.existingGradesData) {
                    for (const siswaId in window.existingGradesData) {
                        if (window.existingGradesData[siswaId] && window.existingGradesData[siswaId].nilai_tugas) {
                            nilaiTugasAwalPerSiswa[siswaId] = window.existingGradesData[siswaId].nilai_tugas;
                        }
                    }
                }

                if (initialMaxTugasCount > 0) {
                    for (let i = 1; i <= initialMaxTugasCount; i++) {
                        createAndAppendTugasColumn(i, nilaiTugasAwalPerSiswa);
                    }
                } else { 
                    createAndAppendTugasColumn(1, nilaiTugasAwalPerSiswa);
                }
                updateRemoveButtons();
            }


            if (addTugasBtn && tabelNilai) {
                addTugasBtn.addEventListener('click', function(event) {
                    event.preventDefault();
                    const currentTugasHeaders = headerRow.querySelectorAll('th.th-tugas');
                    const nextTugasKe = currentTugasHeaders.length + 1;
                    createAndAppendTugasColumn(nextTugasKe);
                    updateRemoveButtons();
                });
            }
        });
    </script>
    @if(isset($existingGrades))
    <script>
        window.existingGradesData = @json($existingGrades->mapWithKeys(function ($item, $key) {
            return [$key => ['nilai_tugas' => $item->nilai_tugas ?? []]];
        }));
    </script>
    @else
    <script>
        window.existingGradesData = {};
    </script>
    @endif
    @endpush
</x-app-layout>