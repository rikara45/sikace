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
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pilih Kelas dan Mata Pelajaran</h3>
                    <p class="text-sm text-gray-600 mb-4">Silakan pilih kelas dan mata pelajaran yang ingin Anda input nilainya untuk tahun ajaran {{ $currentTahunAjaran }}.</p>

                     @include('layouts.partials.alert-messages') {{-- Untuk menampilkan error jika tidak ada jadwal --}}

                     {{-- Form Pemilihan --}}
                     {{-- Menggunakan GET untuk menuju halaman input nilai --}}
                     <form method="GET" action="{{ route('guru.nilai.input', ['kelas_id' => 'dummy_kelas', 'matapelajaran_id' => 'dummy_mapel']) }}" id="selectGradeForm">
                         {{-- Kita akan modifikasi action URL dengan JS --}}

                         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             {{-- Pilih Kelas --}}
                             <div>
                                 <x-input-label for="kelas_id_select" :value="__('Kelas yang Diajar')" />
                                 <select id="kelas_id_select" name="kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                     <option value="">-- Pilih Kelas --</option>
                                     @foreach ($assignedClasses as $kelasData)
                                         <option value="{{ $kelasData['kelas_id'] }}" data-subjects="{{ json_encode($kelasData['subjects']) }}">
                                             {{ $kelasData['nama_kelas'] }}
                                         </option>
                                     @endforeach
                                 </select>
                                 <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                             </div>

                             {{-- Pilih Mata Pelajaran --}}
                             <div>
                                 <x-input-label for="mapel_id_select" :value="__('Mata Pelajaran yang Diajar di Kelas Terpilih')" />
                                 <select id="mapel_id_select" name="matapelajaran_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required disabled>
                                     <option value="">-- Pilih Kelas Dahulu --</option>
                                     {{-- Opsi Mapel akan diisi oleh Javascript --}}
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

    {{-- Simple Javascript for Chained Dropdown --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const kelasSelect = document.getElementById('kelas_id_select');
            const mapelSelect = document.getElementById('mapel_id_select');
            const submitButton = document.getElementById('submitButton');
            const gradeForm = document.getElementById('selectGradeForm');

            kelasSelect.addEventListener('change', function () {
                // Reset mapel dropdown
                mapelSelect.innerHTML = '<option value="">-- Pilih Mata Pelajaran --</option>';
                mapelSelect.disabled = true;
                submitButton.disabled = true;

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
                            mapelSelect.disabled = false; // Aktifkan dropdown mapel
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
                 // Aktifkan tombol submit jika mapel dipilih
                 if (mapelSelect.value) {
                     submitButton.disabled = false;
                     // Update action URL form
                     const kelasId = kelasSelect.value;
                     const mapelId = mapelSelect.value;
                     if(kelasId && mapelId) {
                         // Hati-hati hardcode URL, lebih baik generate dari route name jika memungkinkan
                         let baseUrl = "{{ route('guru.nilai.input', ['kelas_id' => 'KLS', 'matapelajaran_id' => 'MPL']) }}";
                         let newAction = baseUrl.replace('KLS', kelasId).replace('MPL', mapelId);
                         gradeForm.action = newAction;
                     }

                 } else {
                     submitButton.disabled = true;
                 }
            });
        });
    </script>
    @endpush

</x-app-layout>