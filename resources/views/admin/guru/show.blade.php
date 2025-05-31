<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Guru: ') . $guru->nama_guru }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.guru.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-arrow-left mr-2"></i>
                            {{ __('Kembali ke Daftar Guru') }}
                        </a>
                    </div>

                    <table class="table-auto w-full mb-6">
                        <tbody>
                            <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700 w-1/3">NIP</td>
                                <td class="px-4 py-2">{{ $guru->nip ?? '-' }}</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2 font-semibold text-gray-700 w-1/3">Nama Guru</td>
                                <td class="px-4 py-2">{{ $guru->nama_guru }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-2 font-semibold text-gray-700 w-1/3">Email Login</td>
                                <td class="px-4 py-2">{{ $guru->user?->email ?? '-' }}</td>
                            </tr>
                             <tr class="bg-gray-50">
                                <td class="px-4 py-2 font-semibold text-gray-700 w-1/3">Wali Kelas</td>
                                 <td class="px-4 py-2">{{ $guru->kelasWali?->nama_kelas ?? '-' }}</td>
                            </tr>
                             {{-- Modifikasi Bagian Mata Pelajaran Diampu --}}
                            <tr class="bg-white">
                                <td class="px-4 py-2 font-semibold text-gray-700 align-top w-1/3" colspan="2">Mata Pelajaran Diampu:</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="px-4 pb-2 pt-0"> {{-- Mengurangi padding atas untuk label --}}
                                    @if($teachingAssignments && $teachingAssignments->count() > 0)
                                        <div class="overflow-x-auto border border-gray-200 rounded-md">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Mata Pelajaran</th>
                                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Kelas (Tahun Ajaran)</th>
                                                        {{-- Kolom Guru Pengampu tidak perlu karena ini sudah halaman detail guru --}}
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach ($teachingAssignments as $assignment)
                                                        <tr class="{{ $loop->even ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100">
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                                                {{ $assignment->nama_mapel }} {{ $assignment->kode_mapel ? '('.$assignment->kode_mapel.')' : '' }}
                                                            </td>
                                                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                                                <a href="{{ route('admin.kelas.show', $assignment->kelas_id_for_link) }}" class="text-blue-600 hover:underline">
                                                                    {{ $assignment->nama_kelas }} ({{ $assignment->tahun_ajaran }})
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <p class="text-gray-500 text-sm pl-4">- Guru ini belum ditugaskan mengajar mata pelajaran di kelas manapun.</p>
                                    @endif
                                </td>
                            </tr>
                            {{-- Akhir Modifikasi --}}
                        </tbody>
                    </table>

                     <div class="mt-6 flex justify-end space-x-2">
                         <a href="{{ route('admin.guru.edit', $guru) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                             Edit
                         </a>
                         <form action="{{ route('admin.guru.destroy', $guru) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini? Menghapus guru juga akan menghapus akun login terkait.');">
                             @csrf
                             @method('DELETE')
                             <x-danger-button type="submit">
                                 Hapus
                             </x-danger-button>
                         </form>
                     </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>