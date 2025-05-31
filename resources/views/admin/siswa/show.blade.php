<x-app-layout>
    {{-- Slot Header --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Siswa: ') . $siswa->nama_siswa }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Kotak Detail Siswa --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Tombol Kembali --}}
                    <div class="mb-4">
                        <a href="{{ route('admin.siswa.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                             <i class="fas fa-arrow-left mr-2"></i> {{ __('Kembali ke Daftar Siswa') }}
                        </a>
                    </div>

                    {{-- Tabel Informasi Siswa --}}
                    <table class="table-auto w-full mb-6 text-sm">
                        <tbody>
                            <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700" >NIS</td>
                                <td class="px-4 py-2">{{ $siswa->nis }}</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 font-semibold text-gray-700" >NISN</td>
                                <td class="px-4 py-2">{{ $siswa->nisn ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700" >Nama Siswa</td>
                                <td class="px-4 py-2">{{ $siswa->nama_siswa }}</td>
                            </tr>
                             <tr class="bg-gray-50">
                                <td class="px-4 py-2 font-semibold text-gray-700" >Kelas</td>
                                <td class="px-4 py-2">{{ $siswa->kelas?->nama_kelas ?? 'Belum ada kelas' }} ({{ $siswa->kelas?->tahun_ajaran ?? '-' }})</td>
                            </tr>
                             <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700" >Jenis Kelamin</td>
                                <td class="px-4 py-2">
                                    @if($siswa->jenis_kelamin == 'L') Laki-laki
                                    @elseif($siswa->jenis_kelamin == 'P') Perempuan
                                    @else -
                                    @endif
                                </td>
                            </tr>
                             <tr class="bg-gray-50">
                                <td class="px-4 py-2 font-semibold text-gray-700" >Email Login (Internal)</td>
                                <td class="px-4 py-2">{{ $siswa->user?->email ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                     {{-- Tombol Aksi --}}
                     <div class="mt-6 flex justify-end space-x-2">
                        <a href="{{ route('admin.siswa.edit', $siswa) }}"
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit
                        </a>
                        <form action="{{ route('admin.siswa.destroy', $siswa) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini? Menghapus siswa juga akan menghapus akun login dan data nilai terkait.');">
                            @csrf
                            @method('DELETE')
                            <x-danger-button type="submit">
                                Hapus
                            </x-danger-button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- (Opsional) Kotak Rapor Nilai Singkat --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">Ringkasan Nilai Terakhir</h3>
                    @if($siswa->nilais->count() > 0)
                        @php
                            $grouped = $siswa->nilais
                                ->sortByDesc('tahun_ajaran')
                                ->sortByDesc('semester')
                                ->groupBy(function($item) {
                                    return $item->tahun_ajaran . '||' . $item->semester;
                                });

                            $latestKey = $grouped->keys()->first();
                            $latestNilaiGroup = $latestKey ? $grouped[$latestKey] : null;
                            if ($latestKey) {
                                list($latestTahun, $latestSemester) = explode('||', $latestKey);
                            } else {
                                $latestTahun = null; $latestSemester = null;
                            }
                        @endphp

                        @if($latestNilaiGroup)
                            <p class="text-sm text-gray-600 mb-4">Menampilkan nilai untuk Semester {{ $latestSemester }} Tahun Ajaran {{ $latestTahun }}</p>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left border">Mata Pelajaran</th>
                                            <th class="px-4 py-2 text-center border">Nilai Akhir</th>
                                            <th class="px-4 py-2 text-center border">Predikat</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($latestNilaiGroup->sortBy('mataPelajaran.nama_mapel') as $nilai)
                                        <tr class="border-b">
                                            <td class="px-4 py-2 border">{{ $nilai->mataPelajaran?->nama_mapel ?? 'N/A' }}</td>
                                            <td class="px-4 py-2 text-center border">{{ !is_null($nilai->nilai_akhir) ? number_format($nilai->nilai_akhir, 2) : '-' }}</td>
                                            <td class="px-4 py-2 text-center border">{{ $nilai->predikat ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center text-gray-500">Belum ada data nilai.</p>
                        @endif
                    @else
                        <p class="text-center text-gray-500">Belum ada data nilai.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>