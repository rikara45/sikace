<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Input Nilai: {{ $mapel->nama_mapel }} - Kelas {{ $kelas->nama_kelas }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="mb-4">
                         <a href="{{ route('guru.nilai.create') }}" class="text-blue-600 hover:text-blue-900">&larr; Kembali Pilih Kelas/Mapel</a>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900">
                        Mata Pelajaran: {{ $mapel->nama_mapel }} {{ $mapel->kode_mapel ? '('.$mapel->kode_mapel.')' : '' }}
                    </h3>
                    <p class="text-sm text-gray-600">Kelas: {{ $kelas->nama_kelas }}</p>
                    <p class="text-sm text-gray-600">Tahun Ajaran: {{ $tahunAjaran }}</p>
                    <p class="text-sm text-gray-600 mb-6">Semester: {{ $semester }}</p>

                    @include('layouts.partials.alert-messages')

                    <form method="POST" action="{{ route('guru.nilai.store') }}">
                        @csrf
                        {{-- Hidden inputs untuk mengirim konteks --}}
                        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                        <input type="hidden" name="matapelajaran_id" value="{{ $mapel->id }}">
                        <input type="hidden" name="tahun_ajaran" value="{{ $tahunAjaran }}">
                        <input type="hidden" name="semester" value="{{ $semester }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                        {{-- Input Nilai Komponen --}}
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tugas</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UTS</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UAS</th>
                                         {{-- Nanti bisa tampilkan Nilai Akhir & Predikat jika sudah ada --}}
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($siswaList as $index => $siswa)
                                        @php
                                            // Ambil nilai yang sudah ada untuk siswa ini, jika ada
                                            $nilaiSiswa = $existingGrades->get($siswa->id);
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $siswa->nis }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $siswa->nama_siswa }}</td>
                                            {{-- Input Nilai Tugas --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <input type="number" step="0.01" min="0" max="100"
                                                       name="grades[{{ $siswa->id }}][nilai_tugas]"
                                                       value="{{ old('grades.'.$siswa->id.'.nilai_tugas', $nilaiSiswa?->nilai_tugas) }}"
                                                       class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                <x-input-error :messages="$errors->get('grades.'.$siswa->id.'.nilai_tugas')" class="mt-1" />
                                            </td>
                                            {{-- Input Nilai UTS --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                 <input type="number" step="0.01" min="0" max="100"
                                                       name="grades[{{ $siswa->id }}][nilai_uts]"
                                                       value="{{ old('grades.'.$siswa->id.'.nilai_uts', $nilaiSiswa?->nilai_uts) }}"
                                                       class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                 <x-input-error :messages="$errors->get('grades.'.$siswa->id.'.nilai_uts')" class="mt-1" />
                                            </td>
                                            {{-- Input Nilai UAS --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                 <input type="number" step="0.01" min="0" max="100"
                                                       name="grades[{{ $siswa->id }}][nilai_uas]"
                                                       value="{{ old('grades.'.$siswa->id.'.nilai_uas', $nilaiSiswa?->nilai_uas) }}"
                                                       class="w-20 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                  <x-input-error :messages="$errors->get('grades.'.$siswa->id.'.nilai_uas')" class="mt-1" />
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Seharusnya tidak terjadi karena sudah dicek di controller --}}
                                        <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada siswa di kelas ini.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button type="submit">
                                {{ __('Simpan Semua Nilai') }}
                            </x-primary-button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>