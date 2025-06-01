<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Pesan Selamat Datang --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Selamat datang, {{ Auth::user()->name }}!</h3>
                </div>
            </div>

            {{-- Informasi Akademik Aktif (Sudah bagus, biarkan) --}}
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 shadow-md rounded-r-md" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-blue-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                    <div>
                        <p class="font-bold">Informasi Akademik Aktif</p>
                        <p class="text-sm">Tahun Ajaran: <span class="font-semibold">{{ $tahunAjaranAktif }}</span></p>
                        <p class="text-sm">Semester: <span class="font-semibold">{{ $semesterAktif == '1' || $semesterAktif == '2' ? 'Semester ' . $semesterAktif : $semesterAktif }}</span></p>
                        <a href="{{ route('admin.settings.index') }}"
                           class="mt-1 inline-flex items-center px-3 py-1 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                           <i class="fas fa-cog mr-2"></i>
                           Ubah Pengaturan
                        </a>
                    </div>
                </div>
            </div>

            {{-- Kontainer Statistik Terkonsolidasi (Biarkan) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Umum</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="p-4 bg-gray-50 rounded-lg shadow">
                            <div class="flex items-center">
                                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                                    <i class="fas fa-users fa-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase">Total Siswa</p>
                                    <p class="text-2xl font-semibold text-gray-700">{{ $totalSiswa }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg shadow">
                            <div class="flex items-center">
                                <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                                    <i class="fas fa-chalkboard-teacher fa-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase">Total Guru</p>
                                    <p class="text-2xl font-semibold text-gray-700">{{ $totalGuru }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg shadow">
                            <div class="flex items-center">
                                <div class="p-3 mr-4 text-indigo-500 bg-indigo-100 rounded-full">
                                    <i class="fas fa-door-open fa-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase">Total Kelas</p>
                                    <p class="text-2xl font-semibold text-gray-700">{{ $totalKelas }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg shadow">
                            <div class="flex items-center">
                                <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full">
                                    <i class="fas fa-book fa-xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase">Total Mapel</p>
                                    <p class="text-2xl font-semibold text-gray-700">{{ $totalMataPelajaran }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notifikasi & Tugas Section --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 space-y-4"> {{-- Tambahkan space-y-4 --}}
                        <h3 class="text-lg font-semibold text-gray-800">Notifikasi & Tugas</h3>

                        @if($kelasTanpaWali->count() > 0 || $guruTanpaAkun->count() > 0)
                            @if($kelasTanpaWali->count() > 0)
                            <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-md">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 pt-0.5">
                                        <i class="fas fa-exclamation-triangle fa-lg text-yellow-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-md font-semibold text-yellow-700 mb-1">Kelas Belum Memiliki Wali Kelas</h4>
                                        <ul class="list-disc list-inside text-sm text-yellow-600 space-y-1">
                                            @foreach($kelasTanpaWali as $kelas)
                                                <li>
                                                    <a href="{{ route('admin.kelas.edit', $kelas) }}" class="hover:underline hover:text-yellow-800">
                                                        {{ $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($guruTanpaAkun->count() > 0)
                            <div class="p-4 bg-orange-50 border-l-4 border-orange-400 rounded-r-md">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 pt-0.5">
                                        <i class="fas fa-user-plus fa-lg text-orange-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-md font-semibold text-orange-700 mb-1">Guru Belum Memiliki Akun Login</h4>
                                        <ul class="list-disc list-inside text-sm text-orange-600 space-y-1">
                                            @foreach($guruTanpaAkun as $guru)
                                                <li>
                                                    <a href="{{ route('admin.guru.edit', $guru) }}" class="hover:underline hover:text-orange-800">
                                                        {{ $guru->nama_guru }} {{ $guru->nip ? '('.$guru->nip.')' : '' }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @else
                            <div class="p-4 bg-green-50 border-l-4 border-green-400 rounded-r-md">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 pt-0.5">
                                        <i class="fas fa-check-circle fa-lg text-green-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-700">Tidak ada notifikasi penting saat ini.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Data Terbaru Ditambahkan (Biarkan atau sesuaikan jika perlu) --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Data Terbaru Ditambahkan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-2">Siswa Terbaru:</p>
                                @if($siswaTerbaru->count() > 0)
                                <ul class="list-disc list-inside text-xs text-gray-700 space-y-1">
                                    @foreach($siswaTerbaru as $siswa)
                                    <li>
                                        <a href="{{ route('admin.siswa.show', $siswa) }}" class="hover:underline">{{ $siswa->nama_siswa }} ({{ $siswa->nis }})</a>
                                        <span class="text-gray-400">- {{ $siswa->created_at->diffForHumans() }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="text-xs text-gray-500">Belum ada data siswa.</p>
                                @endif
                            </div>

                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-2">Guru Terbaru:</p>
                                @if($guruTerbaru->count() > 0)
                                <ul class="list-disc list-inside text-xs text-gray-700 space-y-1">
                                    @foreach($guruTerbaru as $guru)
                                    <li>
                                        <a href="{{ route('admin.guru.show', $guru) }}" class="hover:underline">{{ $guru->nama_guru }}</a>
                                        <span class="text-gray-400">- {{ $guru->created_at->diffForHumans() }}</span>
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="text-xs text-gray-500">Belum ada data guru.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>