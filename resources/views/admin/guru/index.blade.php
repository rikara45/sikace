<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Data Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Tombol Tambah --}}
                    <div class="mb-4">
                        <a href="{{ route('admin.guru.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-green-600 border border-green-700 rounded-md font-semibold text-xs text-black uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150" style="color: black;">
                            <i class="fas fa-plus mr-2"></i> {{ __('Tambah Guru') }}
                        </a>
                    </div>

                    {{-- Pesan Sukses/Error --}}
                    @include('layouts.partials.alert-messages')

                      {{-- Form Pencarian --}}
                      <form method="GET" action="{{ route('admin.guru.index') }}" class="mb-4">
                           {{-- Tambahkan input tersembunyi untuk mempertahankan sort saat mencari --}}
                           <input type="hidden" name="sort" value="{{ request('sort', 'nama_guru') }}">
                           <input type="hidden" name="direction" value="{{ request('direction', 'asc') }}">

                          <div class="flex">
                              <x-text-input id="search" class="block mt-1 w-full mr-2" type="text" name="search" :value="request('search')" placeholder="Cari Nama, NIP, atau Email..." />
                              <x-primary-button>
                                  {{ __('Cari') }}
                              </x-primary-button>
                          </div>
                      </form>

                    {{-- Tabel Data Guru --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    @php
                                        // Ambil parameter sort & direction saat ini, set default jika tidak ada
                                        $currentSort = request('sort', 'nip'); // Default sort NIP
                                        $currentDirection = request('direction', 'asc'); // Default direction asc

                                        // Hitung arah sorting berikutnya untuk NIP
                                        $nextDirectionNip = ($currentSort === 'nip' && $currentDirection === 'asc') ? 'desc' : 'asc';
                                        // Hitung arah sorting berikutnya untuk Nama Guru
                                        $nextDirectionNama = ($currentSort === 'nama_guru' && $currentDirection === 'asc') ? 'desc' : 'asc';
                                    @endphp
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{-- Tautan Sorting NIP --}}
                                        <a href="{{ route('admin.guru.index', array_merge(request()->query(), ['sort' => 'nip', 'direction' => $nextDirectionNip])) }}" class="flex items-center justify-center gap-1 hover:underline">
                                            NIP
                                            {{-- Tampilkan ikon jika NIP sedang di-sort atau default --}}
                                            @if($currentSort === 'nip' || (!request()->has('sort') && !request()->has('direction')))
                                                @if($currentDirection === 'asc' || (!request()->has('sort') && !request()->has('direction')))
                                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                                @else
                                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                         {{-- Tautan Sorting Nama Guru --}}
                                        <a href="{{ route('admin.guru.index', array_merge(request()->query(), ['sort' => 'nama_guru', 'direction' => $nextDirectionNama])) }}" class="flex items-center justify-center gap-1 hover:underline">
                                            Nama Guru
                                            {{-- Tampilkan ikon jika Nama Guru sedang di-sort --}}
                                            @if($currentSort === 'nama_guru')
                                                @if($currentDirection === 'asc')
                                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path></svg>
                                                @else
                                                    <svg class="w-3 h-3 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email Login</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($gurus as $index => $guru)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $gurus->firstItem() + $index }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $guru->nip ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $guru->nama_guru }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $guru->user->email ?? '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('admin.guru.show', $guru) }}"
                                                   class="inline-flex items-center px-3 py-1 border border-blue-600 text-blue-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-blue-600 hover:text-white transition-colors duration-150">
                                                    <i class="fas fa-eye mr-1"></i> Lihat
                                                </a>
                                                <a href="{{ route('admin.guru.edit', $guru) }}"
                                                   class="inline-flex items-center px-3 py-1 border border-indigo-600 text-indigo-600 bg-white rounded-md text-xs font-semibold uppercase hover:bg-indigo-600 hover:text-white transition-colors duration-150">
                                                    <i class="fas fa-edit mr-1"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.guru.destroy', $guru) }}" method="POST" class="inline"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data guru ini? Menghapus guru juga akan menghapus akun login terkait.');">
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
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Data guru tidak ditemukan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links (Gunakan appends untuk mempertahankan query string) --}}
                    <div class="mt-4">
                         {{ $gurus->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>