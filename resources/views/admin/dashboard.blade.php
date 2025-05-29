    <x-app-layout>
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Admin Dashboard') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 shadow-md rounded-md" role="alert">
                    <div class="flex">
                        <div class="py-1"><svg class="fill-current h-6 w-6 text-blue-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                        <div>
                            <p class="font-bold">Informasi Akademik Aktif</p>
                            <p class="text-sm">Tahun Ajaran: <span class="font-semibold">{{ $tahunAjaranAktif }}</span></p>
                            <p class="text-sm">Semester: <span class="font-semibold">{{ $semesterAktif == '1' || $semesterAktif == '2' ? 'Semester ' . $semesterAktif : $semesterAktif }}</span></p>
                            <a href="{{ route('admin.settings.index') }}" class="text-xs text-blue-600 hover:text-blue-800 underline">Ubah Pengaturan</a>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center">
                                <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase">Total Siswa</p>
                                    <p class="text-2xl font-semibold text-gray-700">{{ $totalSiswa }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center">
                                <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase">Total Guru</p>
                                    <p class="text-2xl font-semibold text-gray-700">{{ $totalGuru }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center">
                                <div class="p-3 mr-4 text-indigo-500 bg-indigo-100 rounded-full">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase">Total Kelas</p>
                                    <p class="text-2xl font-semibold text-gray-700">{{ $totalKelas }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            <div class="flex items-center">
                                <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m0 0a7.5 7.5 0 100-11.494A7.5 7.5 0 0012 17.747zM12 6.253V3M12 6.253l-.054.059a2.65 2.65 0 010 3.748l.054.059m0 0l-.054-.059a2.65 2.65 0 000-3.748l.054-.059m0 0l.054.059a2.65 2.65 0 010 3.748l-.054.059"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-600 uppercase">Total Mapel</p>
                                    <p class="text-2xl font-semibold text-gray-700">{{ $totalMataPelajaran }}</p>
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
                    <br>
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
                                <br>
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