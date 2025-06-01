<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Kelas') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('admin.kelas.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-plus mr-2"></i> {{ __('Tambah Kelas') }}
                        </a>
                    </div>
                    @include('layouts.partials.alert-messages')

                     <form id="filterForm" method="GET" action="{{ route('admin.kelas.index') }}" class="mb-4">
                        {{-- Hidden inputs untuk sort --}}
                        <input type="hidden" name="sort" value="{{ request('sort', 'nama_kelas') }}">
                        <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
                        
                        {{-- Hidden inputs untuk filter tahun ajaran yang terpilih (diisi oleh JS) --}}
                        @if(request('tahun_ajaran_filters'))
                            @foreach(request('tahun_ajaran_filters') as $ta_filter)
                                <input type="hidden" name="tahun_ajaran_filters[]" value="{{ $ta_filter }}">
                            @endforeach
                        @endif
                        {{-- Bisa tambahkan hidden input untuk filter lain jika ada (misal wali kelas) --}}

                        <div class="flex items-center">
                             <x-text-input id="search" class="block mt-1 w-full mr-2" type="text" name="search" :value="request('search')" placeholder="Cari Nama Kelas, Wali Kelas..." />
                             <x-primary-button type="submit"> {{ __('Cari') }} </x-primary-button>
                         </div>
                     </form>

                    <div class="overflow-x-auto border border-gray-200 rounded-md">
                        <table class="min-w-full divide-y divide-gray-200 border-collapse border border-gray-300">
                            <thead class="bg-gray-100">
                                <tr>
                                    @php
                                        // sort dan direction sudah di-pass dari KelasController
                                        $currentSort = $sort ?? request('sort', 'nama_kelas');
                                        $currentDirection = $direction ?? request('direction', 'asc');
                                        $nextDirectionNamaKelas = ($currentSort === 'nama_kelas' && $currentDirection === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">No</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">
                                        <a href="{{ route('admin.kelas.index', array_merge(request()->except(['page']), ['sort' => 'nama_kelas', 'direction' => $nextDirectionNamaKelas])) }}" class="flex items-center justify-center gap-1 hover:underline">
                                            Nama Kelas  
                                            @if($currentSort === 'nama_kelas')
                                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $currentDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                                </svg>
                                            @endif
                                        </a>
                                    </th>
                                    
                                    {{-- Kolom Header Tahun Ajaran dengan Filter Dropdown --}}
                                    <th scope="col" class="px-4 py-2 text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300 relative text-center" x-data="{ openTaFilter: false }" @click.away="openTaFilter = false">
                                        <button type="button" @click="openTaFilter = !openTaFilter" class="inline-flex items-center justify-center w-full px-3 py-2 text-xs font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500">
                                            TAHUN AJARAN
                                            @if(isset($selectedTahunAjarans) && count($selectedTahunAjarans) > 0)
                                                <span class="ml-1.5 px-1.5 py-0.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">({{ count($selectedTahunAjarans) }})</span>
                                            @endif
                                            <svg class="w-4 h-4 ml-1.5 transition-transform duration-200" :class="{'transform rotate-180': openTaFilter}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>
                                        <div x-show="openTaFilter" 
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             class="absolute z-20 mt-1 w-56 origin-top-right right-0 lg:origin-top-left lg:left-0 bg-white rounded-md shadow-xl ring-1 ring-black ring-opacity-5 p-3" 
                                             style="display: none;">
                                            <h4 class="font-semibold text-gray-800 mb-2 text-sm text-left">Filter Tahun Ajaran</h4>
                                            <div class="space-y-1 max-h-48 overflow-y-auto custom-scroll text-left">
                                                @foreach($availableTahunAjarans as $ta)
                                                    <label for="filter_ta_{{ Str::slug($ta) }}" class="flex items-center text-gray-700 text-sm hover:bg-gray-100 px-2 py-1.5 rounded cursor-pointer">
                                                        <input type="checkbox" id="filter_ta_{{ Str::slug($ta) }}" name="dropdown_tahun_ajaran_options[]" value="{{ $ta }}"
                                                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                               {{ (isset($selectedTahunAjarans) && is_array($selectedTahunAjarans) && in_array($ta, $selectedTahunAjarans)) ? 'checked' : '' }}>
                                                        <span class="ml-2">{{ $ta }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between items-center">
                                                <button type="button" class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1" @click="
                                                    const form = document.getElementById('filterForm');
                                                    document.querySelectorAll('input[name=\'dropdown_tahun_ajaran_options[]\']').forEach(el => el.checked = false);
                                                    form.querySelectorAll('input[name=\'tahun_ajaran_filters[]\']').forEach(el => el.remove());
                                                    form.submit();
                                                    openTaFilter = false;
                                                "> Hapus Filter </button>
                                                <button type="button" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1" @click="
                                                    const form = document.getElementById('filterForm');
                                                    form.querySelectorAll('input[name=\'tahun_ajaran_filters[]\']').forEach(el => el.remove()); // Hapus filter lama
                                                    document.querySelectorAll('input[name=\'dropdown_tahun_ajaran_options[]\']:checked').forEach(el => {
                                                        const hiddenInput = document.createElement('input');
                                                        hiddenInput.type = 'hidden';
                                                        hiddenInput.name = 'tahun_ajaran_filters[]';
                                                        hiddenInput.value = el.value;
                                                        form.appendChild(hiddenInput);
                                                    });
                                                    form.submit();
                                                    openTaFilter = false;
                                                "> Terapkan </button>
                                            </div>
                                        </div>
                                    </th>

                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Wali Kelas</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Jumlah Siswa</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-600 uppercase tracking-wider border border-gray-300">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($kelasList as $index => $kelas)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 border border-gray-300 text-center">{{ $kelasList->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-300 text-center">{{ $kelas->nama_kelas }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-300 text-center">{{ $kelas->tahun_ajaran }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-300 text-center">{{ $kelas->waliKelas?->nama_guru ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border border-gray-300 text-center">{{ $kelas->siswas_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium border border-gray-300 text-center">
                                            <div class="flex flex-wrap gap-2 justify-center">
                                                <a href="{{ route('admin.kelas.show', $kelas) }}"
                                                   class="inline-flex items-center px-3 py-1 border border-blue-600 text-blue-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-blue-600 hover:text-white transition-colors duration-150">
                                                    <i class="fas fa-eye mr-1"></i> Lihat
                                                </a>
                                                <a href="{{ route('admin.kelas.edit', $kelas) }}"
                                                   class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-indigo-600 hover:text-white transition-colors duration-150">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.kelas.destroy', $kelas) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelas ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center px-3 py-1 border border-red-600 text-red-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-red-600 hover:text-white transition-colors duration-150">
                                                        <i class="fas fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 border border-gray-300">Data kelas tidak ditemukan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $kelasList->links() }}</div>
                </div>
            </div>
        </div>
    </div>
    @push('styles') {{-- Ini adalah style tambahan jika belum ada di app.css --}}
    <style>
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #c5c5c5;
            border-radius: 3px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #a5a5a5;
        }
    </style>
    @endpush
</x-app-layout>