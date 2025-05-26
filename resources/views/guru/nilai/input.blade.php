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

            {{-- Bagian Pengaturan dan Input Nilai hanya tampil jika SEMUA filter sudah dipilih --}}
            @if (isset($showInputSection) && $showInputSection && isset($kelas) && isset($mapel))
                {{-- 2. FORM PENGATURAN BOBOT --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="form-bobot-section">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-1">Pengaturan Bobot Penilaian (%)</h3>
                        <p class="text-sm text-gray-500 mb-4">Total ketiga bobot harus 100%. Berlaku untuk: <span class="font-semibold">{{ $mapel->nama_mapel }}</span> di Kelas <span class="font-semibold">{{ $kelas->nama_kelas }}</span> ({{ $selectedTahunAjaran }} - Sem {{ $selectedSemester }})</p>
                        @if(session('success_bobot') || $errors->has('bobot_total'))
                            <div class="mb-4">
                                @if(session('success_bobot')) <div class="p-3 rounded bg-green-100 text-green-700 text-sm">{{ session('success_bobot') }}</div> @endif
                                <x-input-error :messages="$errors->get('bobot_total')" class="mt-1 text-sm" />
                            </div>
                        @endif

                        <form method="POST" action="{{ route('guru.nilai.simpanBobot') }}">
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
                                    <x-primary-button type="submit">
                                        {{ __('Simpan Bobot') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- 3. FORM PENGATURAN KKM & PREDIKAT --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="form-kkm-section">
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

                        <form method="POST" action="{{ route('guru.nilai.simpanKkm') }}">
                            @csrf
                            <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                            <input type="hidden" name="matapelajaran_id" value="{{ $selectedMapelId }}">
                            <input type="hidden" name="tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                            {{-- Hidden input untuk membawa filter saat redirect --}}
                            <input type="hidden" name="filter_tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                            <input type="hidden" name="filter_semester" value="{{ $selectedSemester }}">
                            <input type="hidden" name="filter_kelas_id" value="{{ $selectedKelasId }}">
                            <input type="hidden" name="filter_matapelajaran_id" value="{{ $selectedMapelId }}">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                                <div>
                                    <x-input-label for="kkm" :value="__('KKM (0-100)')" />
                                    {{-- Asumsikan $bobot adalah objek BobotPenilaian yang dikirim controller --}}
                                    <x-text-input id="kkm" type="number" name="kkm" :value="old('kkm', $bobot?->kkm ?? 70)" min="0" max="100" required class="block mt-1 w-full md:w-1/2 text-sm"/>
                                </div>
                                <div>
                                    <x-primary-button type="submit">
                                        {{ __('Simpan KKM & Hitung Predikat') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </form>

                        {{-- Tampilkan rentang predikat yang tersimpan (hasil kalkulasi dari KKM terakhir) --}}
                        @if($bobot && $bobot->kkm > 0) {{-- Tampilkan jika KKM sudah diset --}}
                        <div class="mt-4 text-sm p-3 bg-blue-50 border border-blue-200 rounded-md">
                            <p class="font-semibold">Rentang Predikat yang Tersimpan Saat Ini (berdasarkan KKM: {{ $bobot->kkm }}):</p>
                            <p>D: Nilai &lt; {{ $bobot->kkm }}</p>
                            {{-- Menampilkan rentang berdasarkan batas bawah yang disimpan --}}
                            <p>C: {{ $bobot->batas_c }} - {{ $bobot->batas_b > $bobot->batas_c ? $bobot->batas_b - 1 : $bobot->batas_c }}</p>
                            <p>B: {{ $bobot->batas_b }} - {{ $bobot->batas_a > $bobot->batas_b ? $bobot->batas_a - 1 : $bobot->batas_b }}</p>
                            <p>A: {{ $bobot->batas_a }} - 100</p>
                        </div>
                        @else
                        <div class="mt-4 text-sm p-3 bg-yellow-50 border border-yellow-300 rounded-md">
                            <p>Pengaturan KKM belum disimpan untuk konteks ini. Rentang predikat akan dihitung setelah KKM disimpan.</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- 4. FORM INPUT NILAI SISWA (SAMA SEPERTI SEBELUMNYA) --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" id="form-nilai-siswa-section">
                    <div class="p-6 text-gray-900">
                        @if (session('success_nilai')) <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm">{{ session('success_nilai') }}</div>@endif
                        @if (session('error_nilai')) <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm">{{ session('error_nilai') }}</div>@endif
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Daftar Siswa dan Input Nilai</h3>
                        
                        <form method="POST" action="{{ route('guru.nilai.store') }}">
                            @csrf
                            {{-- Hidden inputs (sama seperti sebelumnya) --}}
                            <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                            <input type="hidden" name="matapelajaran_id" value="{{ $selectedMapelId }}">
                            <input type="hidden" name="tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                            <input type="hidden" name="semester" value="{{ $selectedSemester }}">
                            <input type="hidden" name="filter_tahun_ajaran" value="{{ $selectedTahunAjaran }}">
                            <input type="hidden" name="filter_semester" value="{{ $selectedSemester }}">
                            <input type="hidden" name="filter_kelas_id" value="{{ $selectedKelasId }}">
                            <input type="hidden" name="filter_matapelajaran_id" value="{{ $selectedMapelId }}">

                            {{-- DIV INI YANG HARUS PUNYA overflow-x-auto --}}
                            <div class="overflow-x-auto border border-gray-200 rounded-md" style="max-width: 100vw;">
                                <table class="min-w-full divide-y divide-gray-200" id="tabelInputNilai" style="table-layout: fixed;">
                                    <thead class="bg-gray-100">
                                        <tr id="headerNilaiRow">
                                            <th class="w-12 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-0 bg-gray-100 z-20 border-r">No</th>
                                            <th class="w-24 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-12 bg-gray-100 z-20 border-r">NIS</th>
                                            <th class="w-48 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky left-[calc(3rem+6rem)] bg-gray-100 z-20">Nama Siswa</th>
                                            {{-- Kolom Tugas --}}
                                            @for ($i = 1; $i <= ($maxNilaiTugasCount ?? 1); $i++)
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap th-tugas" data-tugas-ke="{{ $i }}" style="min-width: 100px;">
                                                Tugas {{ $i }}
                                                @if ($i > 1)
                                                    <button type="button" class="ml-1 text-red-500 hover:text-red-700 remove-tugas-column" title="Hapus Kolom Tugas {{ $i }}">&times;</button>
                                                @endif
                                            </th>
                                            @endfor
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider th-add-tugas" style="min-width: 50px;">
                                                <button type="button" id="addTugasColumnBtn" class="text-blue-500 hover:text-blue-700 px-1" title="Tambah Kolom Tugas"><i class="fas fa-plus-circle"></i></button>
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 80px;">UTS</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" style="min-width: 80px;">UAS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @if(isset($siswaList) && $siswaList->count() > 0)
                                            @foreach ($siswaList as $index => $siswa)
                                                @php
                                                    $nilaiSiswa = isset($existingGrades) ? $existingGrades->get($siswa->id) : null;
                                                    $nilaiTugasSiswaArray = $nilaiSiswa?->nilai_tugas ?? [];
                                                @endphp
                                                <tr class="hover:bg-gray-50" data-siswa-id="{{ $siswa->id }}">
                                                    <td class="w-12 px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center sticky left-0 bg-white z-10 border-r">{{ $index + 1 }}</td>
                                                    <td class="w-24 px-4 py-4 whitespace-nowrap text-sm text-gray-900 sticky left-12 bg-white z-10 border-r">{{ $siswa->nis }}</td>
                                                    <td class="w-48 px-6 py-4 whitespace-nowrap text-sm text-gray-900 sticky left-[calc(3rem+6rem)] bg-white z-10">{{ $siswa->nama_siswa }}</td>
                                                    
                                                    @for ($i = 0; $i < ($maxNilaiTugasCount ?? 1); $i++)
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center td-tugas" data-tugas-ke="{{ $i + 1 }}">
                                                        <input type="number" step="0.01" min="0" max="100"
                                                               name="grades[{{ $siswa->id }}][nilai_tugas][]"
                                                               value="{{ old('grades.'.$siswa->id.'.nilai_tugas.'.$i, $nilaiTugasSiswaArray[$i] ?? null) }}"
                                                               class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm nilai-tugas-input">
                                                    </td>
                                                    @endfor
                                                    <td data-placeholder-siswa-id="{{ $siswa->id }}" class="tugas-tambahan-placeholder"></td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                         <input type="number" step="0.01" min="0" max="100"
                                                               name="grades[{{ $siswa->id }}][nilai_uts]"
                                                               value="{{ old('grades.'.$siswa->id.'.nilai_uts', $nilaiSiswa?->nilai_uts) }}"
                                                               class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                         <x-input-error :messages="$errors->get('grades.'.$siswa->id.'.nilai_uts')" class="mt-1 text-xs" />
                                                    </td>
                                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                                         <input type="number" step="0.01" min="0" max="100"
                                                               name="grades[{{ $siswa->id }}][nilai_uas]"
                                                               value="{{ old('grades.'.$siswa->id.'.nilai_uas', $nilaiSiswa?->nilai_uas) }}"
                                                               class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                          <x-input-error :messages="$errors->get('grades.'.$siswa->id.'.nilai_uas')" class="mt-1 text-xs" />
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td colspan="{{ 6 + ($maxNilaiTugasCount ?? 1) }}" class="px-6 py-4 text-center text-sm text-gray-500">Siswa tidak ditemukan atau filter belum lengkap.</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- ... Tombol Simpan ... --}}
                            <div class="flex items-center justify-end mt-6">
                                <x-primary-button type="submit">
                                    {{ __('Simpan Semua Nilai') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <p class="text-gray-500">Silakan lengkapi semua filter (Tahun Ajaran, Semester, Kelas, dan Mata Pelajaran) di atas untuk melanjutkan.</p>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @push('scripts')
    <script>
        function submitFilterFormOnChange() {
            document.getElementById('filterNilaiForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const addTugasBtn = document.getElementById('addTugasColumnBtn');
            const tabelNilai = document.getElementById('tabelInputNilai');
            // Ambil jumlah kolom tugas awal dari PHP, default ke 1 jika tidak ada
            let tugasColumnCount = parseInt("{{ $maxNilaiTugasCount ?? 1 }}");

            // Fungsi untuk menambahkan event listener ke tombol hapus kolom
            function addRemoveColumnListener(button) {
                button.addEventListener('click', function() {
                    const tugasKe = parseInt(this.closest('th').dataset.tugasKe);
                    if (tugasKe <= 1) return; // Jangan hapus kolom Tugas 1

                    // Hapus header
                    this.closest('th').remove();

                    // Hapus sel di setiap baris body
                    const bodyRows = tabelNilai.querySelectorAll('tbody tr');
                    bodyRows.forEach(row => {
                        const cellToRemove = row.querySelector(`td[data-tugas-ke="${tugasKe}"]`);
                        if (cellToRemove) {
                            cellToRemove.remove();
                        }
                    });
                    // Tidak perlu decrement tugasColumnCount karena penomoran akan di-handle saat add
                    // Namun, jika kita ingin label tetap urut, kita perlu re-label
                    relabelTugasHeaders();
                });
            }

            // Fungsi untuk me-relabel header kolom tugas
            function relabelTugasHeaders() {
                const headerCells = tabelNilai.querySelectorAll('thead tr#headerNilaiRow th.th-tugas');
                headerCells.forEach((th, index) => {
                    const currentTugasKe = index + 1;
                    th.dataset.tugasKe = currentTugasKe;
                    let textNode = Array.from(th.childNodes).find(node => node.nodeType === Node.TEXT_NODE);
                    if(textNode) textNode.textContent = `Tugas ${currentTugasKe} `; // Spasi untuk tombol hapus

                    const removeBtn = th.querySelector('.remove-tugas-column');
                    if(currentTugasKe > 1 && !removeBtn){ // Tambah tombol hapus jika belum ada & bukan kolom pertama
                        const newRemoveBtn = document.createElement('button');
                        newRemoveBtn.type = 'button';
                        newRemoveBtn.classList.add('ml-1', 'text-red-500', 'hover:text-red-700', 'remove-tugas-column');
                        newRemoveBtn.title = `Hapus Kolom Tugas ${currentTugasKe}`;
                        newRemoveBtn.innerHTML = '&times;';
                        addRemoveColumnListener(newRemoveBtn);
                        th.appendChild(newRemoveBtn);
                    } else if (currentTugasKe <= 1 && removeBtn) { // Hapus tombol dari kolom pertama
                        removeBtn.remove();
                    }
                });
                // Update global counter berdasarkan jumlah header yg ada
                tugasColumnCount = headerCells.length;
            }


            if (addTugasBtn && tabelNilai) {
                addTugasBtn.addEventListener('click', function() {
                    tugasColumnCount++; // Ini akan jadi nomor untuk kolom baru
                    const headerRow = tabelNilai.querySelector('thead tr#headerNilaiRow');
                    const thButtonPenambah = headerRow.querySelector('th.th-add-tugas'); // Kolom tempat tombol +

                    // Buat header baru
                    const newHeader = document.createElement('th');
                    newHeader.classList.add('px-4', 'py-3', 'text-center', 'text-xs', 'font-medium', 'text-gray-500', 'uppercase', 'tracking-wider', 'whitespace-nowrap', 'th-tugas');
                    newHeader.dataset.tugasKe = tugasColumnCount; // Simpan nomor tugas
                    newHeader.textContent = `Tugas ${tugasColumnCount} `; // Spasi untuk tombol hapus
                    newHeader.style.minWidth = '100px';

                    const removeHeaderButton = document.createElement('button');
                    removeHeaderButton.type = 'button';
                    removeHeaderButton.classList.add('ml-1', 'text-red-500', 'hover:text-red-700', 'remove-tugas-column');
                    removeHeaderButton.title = `Hapus Kolom Tugas ${tugasColumnCount}`;
                    removeHeaderButton.innerHTML = '&times;';
                    addRemoveColumnListener(removeHeaderButton); // Tambahkan event listener ke tombol baru
                    newHeader.appendChild(removeHeaderButton);

                    // Sisipkan header baru sebelum kolom tombol "+"
                    headerRow.insertBefore(newHeader, thButtonPenambah);

                    // Tambah sel input baru untuk setiap baris siswa
                    const bodyRows = tabelNilai.querySelectorAll('tbody tr');
                    bodyRows.forEach(row => {
                        // Dapatkan siswaId dari atribut data-siswa-id di <tr>
                        const siswaId = row.dataset.siswaId;
                        if (!siswaId) return; // Lewati jika baris tidak punya siswaId (misal baris tidak punya siswaId (misal baris "Tidak ada siswa"))

                        if (row.querySelectorAll('td').length > 2) { // Hanya tambah jika bukan baris "Tidak ada siswa"
                            const newCell = document.createElement('td');
                            newCell.classList.add('px-4', 'py-4', 'whitespace-nowrap', 'text-sm', 'text-gray-500', 'text-center', 'td-tugas');
                            newCell.dataset.tugasKe = tugasColumnCount; // Simpan nomor tugas

                            const newInput = document.createElement('input');
                            newInput.type = 'number';
                            newInput.step = '0.01';
                            newInput.min = '0';
                            newInput.max = '100';
                            newInput.name = `grades[${siswaId}][nilai_tugas][]`;
                            newInput.classList.add('w-20', 'border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500', 'rounded-md', 'shadow-sm', 'text-sm', 'nilai-tugas-input');
                            newInput.placeholder = `Tgs ${tugasColumnCount}`;
                            newCell.appendChild(newInput);

                            // Sisipkan sel baru sebelum sel placeholder/UTS
                            const placeholderCell = row.querySelector('td.tugas-tambahan-placeholder');
                            const utsCell = Array.from(row.children).find(td => td.querySelector('input[name*="[nilai_uts]"]'));

                            if (placeholderCell) {
                                row.insertBefore(newCell, placeholderCell); // Sisipkan sebelum placeholder
                            } else if (utsCell) {
                                row.insertBefore(newCell, utsCell); // Fallback sisipkan sebelum UTS
                            }
                        }
                    });
                    relabelTugasHeaders(); // Panggil untuk memastikan penomoran header benar
                });
            }

            // Inisialisasi tombol hapus yang sudah ada dari server
            tabelNilai.querySelectorAll('thead th.th-tugas .remove-tugas-column').forEach(addRemoveColumnListener);
            relabelTugasHeaders(); // Panggil untuk inisialisasi label dan tombol hapus
        });
    </script>
    @endpush
</x-app-layout>