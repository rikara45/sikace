<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Nilai Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @include('layouts.partials.alert-messages')

                    {{-- Form Filter Tahun Ajaran & Semester --}}
                    <form method="GET" action="{{ route('guru.nilai.create') }}" class="mb-6 p-4 bg-gray-50 rounded-md border">
                        <h3 class="text-md font-medium text-gray-900 mb-2">Pilih Periode Penilaian</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div>
                                <x-input-label for="filter_tahun_ajaran" :value="__('Tahun Ajaran')" />
                                <select name="filter_tahun_ajaran" id="filter_tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    @if(isset($availableTahunAjaran) && count($availableTahunAjaran) > 0)
                                        @foreach ($availableTahunAjaran as $tahun)
                                            <option value="{{ $tahun }}" @selected($filterTahunAjaran == $tahun)>{{ $tahun }}</option>
                                        @endforeach
                                    @else
                                        <option value="{{ $currentTahunAjaran }}">{{ $currentTahunAjaran }} (Default Aktif)</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <x-input-label for="filter_semester" :value="__('Semester')" />
                                <select name="filter_semester" id="filter_semester" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    @foreach ($availableSemester ?? [1, 2] as $sem)
                                        <option value="{{ $sem }}" @selected($filterSemester == $sem)>Semester {{ $sem }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-primary-button type="submit">
                                    {{ __('Tampilkan Kelas') }}
                                </x-primary-button>
                            </div>
                        </div>
                        @if($filterTahunAjaran != $currentTahunAjaran || $filterSemester != $currentSemester)
                            <p class="text-xs text-gray-500 mt-2">
                                Periode aktif sistem: {{ $currentTahunAjaran }} - Semester {{ $currentSemester }}.
                                <a href="{{ route('guru.nilai.create', ['filter_tahun_ajaran' => $currentTahunAjaran, 'filter_semester' => $currentSemester]) }}" class="text-blue-600 hover:underline">Gunakan Periode Aktif</a>.
                            </p>
                        @endif
                    </form>
                    {{-- Akhir Form Filter --}}

                    <h3 class="text-lg font-medium text-gray-900 mb-2">Pilih Kelas dan Mata Pelajaran</h3>
                    <p class="text-sm text-gray-600 mb-4">Menampilkan kelas untuk periode: <span class="font-semibold">{{ $filterTahunAjaran }} - Semester {{ $filterSemester }}</span></p>

                    {{-- Form Pemilihan Kelas & Mapel --}}
                    <form method="GET" action="#" id="selectGradeForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="kelas_id_select" :value="__('Kelas yang Diajar')" />
                                <select id="kelas_id_select" name="kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @forelse ($assignedClasses as $kelasData)
                                        <option value="{{ $kelasData['kelas_id'] }}" data-subjects='@json($kelasData["subjects"])'>
                                            {{ $kelasData['nama_kelas'] }}
                                        </option>
                                    @empty
                                        <option value="" disabled>Tidak ada kelas ditugaskan untuk periode ini.</option>
                                    @endforelse
                                </select>
                                <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="mapel_id_select" :value="__('Mata Pelajaran di Kelas Terpilih')" />
                                <select id="mapel_id_select" name="matapelajaran_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required disabled>
                                    <option value="">-- Pilih Kelas Dahulu --</option>
                                </select>
                                <x-input-error :messages="$errors->get('matapelajaran_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button type="submit" id="submitButton" disabled>
                                {{ __('Lanjut ke Input Nilai') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const kelasSelect = document.getElementById('kelas_id_select');
            const mapelSelect = document.getElementById('mapel_id_select');
            const submitButton = document.getElementById('submitButton');
            const gradeForm = document.getElementById('selectGradeForm');

            // Ambil nilai filter tahun ajaran dan semester dari PHP (atau dari elemen form filter jika ada)
            const filterTahunAjaran = document.getElementById('filter_tahun_ajaran').value;
            const filterSemester = document.getElementById('filter_semester').value;

            const baseUrl = "{{ route('guru.nilai.input', ['kelas_id' => 'KLS_ID', 'matapelajaran_id' => 'MPL_ID']) }}";

            kelasSelect.addEventListener('change', function () {
                mapelSelect.innerHTML = '<option value="">-- Pilih Mata Pelajaran --</option>';
                mapelSelect.disabled = true;
                submitButton.disabled = true;
                gradeForm.action = '#';

                const selectedOption = kelasSelect.options[kelasSelect.selectedIndex];
                const subjectsJson = selectedOption.getAttribute('data-subjects');

                if (subjectsJson) {
                    try {
                        const subjects = JSON.parse(subjectsJson);
                        if (subjects && subjects.length > 0) {
                            subjects.forEach(function (subject) {
                                const option = document.createElement('option');
                                option.value = subject.mapel_id;
                                option.textContent = `${subject.nama_mapel} ${subject.kode_mapel ? '(' + subject.kode_mapel + ')' : ''}`;
                                mapelSelect.appendChild(option);
                            });
                            mapelSelect.disabled = false;
                        } else {
                            mapelSelect.innerHTML = '<option value="">-- Tidak ada mapel diajar di kelas ini --</option>';
                        }
                    } catch (e) {
                        console.error("Error parsing subjects JSON:", e);
                        mapelSelect.innerHTML = '<option value="">-- Error memuat mapel --</option>';
                    }
                }
            });

            mapelSelect.addEventListener('change', function() {
                if (mapelSelect.value && kelasSelect.value) {
                    submitButton.disabled = false;
                    let newAction = baseUrl.replace('KLS_ID', kelasSelect.value).replace('MPL_ID', mapelSelect.value);
                    // Tambahkan parameter filter ke URL action
                    newAction += `?filter_tahun_ajaran=${encodeURIComponent(filterTahunAjaran)}&filter_semester=${encodeURIComponent(filterSemester)}`;
                    gradeForm.action = newAction;
                } else {
                    submitButton.disabled = true;
                    gradeForm.action = '#';
                }
            });
        });
    </script>
    @endpush
</x-app-layout>