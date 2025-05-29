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

            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 shadow-md rounded-md" role="alert">
                <div class="flex">
                    <div class="py-1"><svg class="fill-current h-6 w-6 text-blue-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                    <div>
                        <p class="font-bold">Informasi Akademik Aktif</p>
                        <p class="text-sm">Tahun Ajaran: <span class="font-semibold">{{ $tahunAjaranAktif }}</span></p>
                        <p class="text-sm">Semester: <span class="font-semibold">{{ $semesterAktif == '1' || $semesterAktif == '2' ? 'Semester ' . $semesterAktif : $semesterAktif }}</span></p>
                        {{-- Tombol Ubah Pengaturan dengan Ikon --}}
                        <a href="{{ route('admin.settings.index') }}"
                           class="mt-1 inline-flex items-center px-3 py-1 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                           <i class="fas fa-cog mr-2"></i>
                           Ubah Pengaturan
                        </a>
                    </div>
                </div>
            </div>

            {{-- Kontainer Statistik Terkonsolidasi --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Umum</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {{-- Total Siswa --}}
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
                        {{-- Total Guru --}}
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
                        {{-- Total Kelas --}}
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
                        {{-- Total Mapel --}}
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


            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Notifikasi & Tugas</h3>
                        
                        @if($kelasTanpaWali->count() > 0)
                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-300 rounded-md">
                            <p class="text-sm font-semibold text-yellow-700 mb-1">Kelas Belum Memiliki Wali Kelas:</p>
                            <ul class="list-disc list-inside text-xs text-yellow-600">
                                @foreach($kelasTanpaWali as $kelas)
                                    <li>
                                        <a href="{{ route('admin.kelas.edit', $kelas) }}" class="hover:underline">{{ $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <p class="text-sm text-gray-500 mb-3">Semua kelas sudah memiliki wali kelas.</p>
                        @endif

                        @if($guruTanpaAkun->count() > 0)
                        <div class="p-3 bg-orange-50 border border-orange-300 rounded-md">
                            <p class="text-sm font-semibold text-orange-700 mb-1">Guru Belum Memiliki Akun Login:</p>
                            <ul class="list-disc list-inside text-xs text-orange-600">
                                @foreach($guruTanpaAkun as $guru)
                                    <li>
                                        <a href="{{ route('admin.guru.edit', $guru) }}" class="hover:underline">{{ $guru->nama_guru }} {{ $guru->nip ? '('.$guru->nip.')' : '' }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <p class="text-sm text-gray-500">Semua guru sudah memiliki akun login.</p>
                        @endif

                        @if($kelasTanpaWali->count() == 0 && $guruTanpaAkun->count() == 0)
                            <p class="text-sm text-gray-500">Tidak ada notifikasi saat ini.</p>
                        @endif
                    </div>
                </div>
                
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
            
            {{-- Tambahkan konten lain sesuai kebutuhan --}}

        </div>
    </div>
</x-app-layout>