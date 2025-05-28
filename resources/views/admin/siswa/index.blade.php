@push('styles')
<style>
    .custom-scroll::-webkit-scrollbar {
        width: 8px;
    }

    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .custom-scroll::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
</style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Tombol Tambah --}}
                    <div class="mb-4 flex justify-between items-center">
                        <a href="{{ route('admin.siswa.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-green-700 rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-plus mr-2"></i> {{ __('Tambah Siswa') }}
                        </a>
                        <a href="{{ route('admin.siswa.showImportForm') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-blue-700 rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <i class="fas fa-file-import mr-2"></i> {{ __('Impor Siswa dari CSV') }}
                        </a>
                    </div>

                      {{-- Pesan Sukses/Error --}}
                      @include('layouts.partials.alert-messages')

                      {{-- Peringatan Selama Impor Sebelumnya --}}
                      @if (session('import_errors'))
                          <div class="mb-4 p-4 bg-yellow-100 border border-yellow-300 text-yellow-700 rounded">
                              <p class="font-bold">Peringatan Selama Impor Sebelumnya:</p>
                              <div class="text-sm custom-scroll overflow-y-auto max-h-32">{!! session('import_errors') !!}</div>
                          </div>
                      @endif

                      {{-- Form Pencarian dan Filter Kelas --}}
                      <form id="filterForm" method="GET" action="{{ route('admin.siswa.index') }}" class="mb-4">
                          <input type="hidden" name="sort" value="{{ request('sort', 'nis') }}">
                          <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">
                          {{-- Input tersembunyi untuk kelas yang dipilih (akan di-manage oleh JS, tapi tetap perlu jika form disubmit cara lain) --}}
                          @if(request('kelas_ids'))
                              @foreach(request('kelas_ids') as $kelas_id)
                                  <input type="hidden" name="kelas_ids[]" value="{{ $kelas_id }}">
                              @endforeach
                          @endif

                          <div class="flex items-center">
                              <x-text-input id="search" class="block mt-1 w-full mr-2" type="text" name="search" :value="request('search')" placeholder="Cari Nama atau NIS..." />
                              <x-primary-button type="submit">
                                  {{ __('Cari') }}
                              </x-primary-button>
                          </div>
                      </form>


                    {{-- Tabel Data Siswa --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    @php
                                        $currentSort = request('sort', 'nis');
                                        $currentDirection = request('direction', 'asc');

                                        $nextDirectionNis = ($currentSort === 'nis' && $currentDirection === 'asc') ? 'desc' : 'asc';
                                        $nextDirectionNama = ($currentSort === 'nama_siswa' && $currentDirection === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ route('admin.siswa.index', array_merge(request()->query(), ['sort' => 'nis', 'direction' => $nextDirectionNis])) }}" class="flex items-center justify-center gap-1 hover:underline">
                                            NIS
                                            @if($currentSort === 'nis')
                                                <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $currentDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ route('admin.siswa.index', array_merge(request()->query(), ['sort' => 'nama_siswa', 'direction' => $nextDirectionNama])) }}" class="flex items-center justify-center gap-1 hover:underline">
                                            Nama Siswa
                                            @if($currentSort === 'nama_siswa')
                                                 <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $currentDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path></svg>
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider relative" x-data="{ open: false }" @click.away="open = false">
                                        <button type="button" class="flex items-center gap-1 hover:underline focus:outline-none" @click="open = !open">
                                            Kelas
                                            @if(request('kelas_ids'))
                                                <span class="ml-1 text-blue-600">({{ count(request('kelas_ids')) }})</span> {{-- Indikator filter aktif --}}
                                            @endif
                                            <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="{'transform rotate-180': open}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                        </button>

                                        {{-- Dropdown Filter Kelas --}}
                                        <div x-show="open" class="absolute z-10 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 p-4" style="display: none;">
                                            <h4 class="font-semibold text-gray-800 mb-2">Filter Kelas</h4>
                                            <div class="space-y-2 max-h-40 overflow-y-auto custom-scroll">
                                                @foreach($kelas as $k)
                                                    <label for="filter_kelas_{{ $k->id }}" class="flex items-center text-gray-700 text-sm">
                                                        <input type="checkbox" id="filter_kelas_{{ $k->id }}" name="filter_kelas_ids[]" value="{{ $k->id }}"
                                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                               {{ in_array($k->id, request('kelas_ids', [])) ? 'checked' : '' }}>
                                                        <span class="ml-2">{{ $k->nama_kelas }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="mt-4 flex justify-between items-center">
                                                <button type="button" class="text-sm text-blue-600 hover:underline" @click="
                                                    const form = document.getElementById('filterForm');
                                                    document.querySelectorAll('input[name=\'filter_kelas_ids[]\']').forEach(el => el.checked = false);
                                                    form.querySelectorAll('input[name=\'kelas_ids[]\']').forEach(el => el.remove()); // Hapus input tersembunyi
                                                    form.submit();
                                                    open = false;
                                                ">
                                                    Hapus Filter
                                                </button>
                                                <button type="button" class="px-3 py-1 bg-indigo-600 text-black text-xs font-semibold rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" @click="
                                                    const form = document.getElementById('filterForm');
                                                    // Hapus input kelas_ids yang lama
                                                    form.querySelectorAll('input[name=\'kelas_ids[]\']').forEach(el => el.remove());
                                                    // Tambahkan input kelas_ids yang baru dari checkbox (CARI DI SELURUH DOKUMEN)
                                                    document.querySelectorAll('input[name=\'filter_kelas_ids[]\']:checked').forEach(el => {
                                                        const hiddenInput = document.createElement('input');
                                                        hiddenInput.type = 'hidden';
                                                        hiddenInput.name = 'kelas_ids[]';
                                                        hiddenInput.value = el.value;
                                                        form.appendChild(hiddenInput);
                                                    });
                                                    form.submit();
                                                    open = false;
                                                ">
                                                    Terapkan
                                                </button>
                                            </div>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($siswas as $index => $siswa)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $siswas->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $siswa->nis }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $siswa->nama_siswa }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $siswa->kelas->nama_kelas ?? 'Belum ada kelas' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('admin.siswa.show', $siswa) }}"
                                                   class="inline-flex items-center px-3 py-1 border border-blue-600 text-blue-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-blue-600 hover:text-white transition-colors duration-150">
                                                    <i class="fas fa-eye mr-1"></i> Lihat
                                                </a>
                                                <a href="{{ route('admin.siswa.edit', $siswa) }}"
                                                   class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-indigo-600 hover:text-white transition-colors duration-150">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.siswa.destroy', $siswa) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center px-3 py-1 border border-red-600 text-red-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-red-600 hover:text-white transition-colors duration-150">
                                                        <i class="fas fa-trash mr-1"></i> Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Data siswa tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{ $siswas->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>