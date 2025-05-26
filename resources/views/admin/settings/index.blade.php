<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8"> {{-- max-w-2xl agar tidak terlalu lebar --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Tahun Ajaran & Semester Aktif</h3>
                    @include('layouts.partials.alert-messages')

                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        <div class="space-y-4">
                            {{-- Tahun Ajaran Aktif --}}
                            <div>
                                <x-input-label for="tahun_ajaran_aktif" :value="__('Tahun Ajaran Aktif')" />
                                <select id="tahun_ajaran_aktif" name="tahun_ajaran_aktif" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @foreach ($availableTahunAjaran as $tahun)
                                        <option value="{{ $tahun }}" @selected(old('tahun_ajaran_aktif', $tahunAjaranAktif) == $tahun)>
                                            {{ $tahun }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tahun_ajaran_aktif')" class="mt-2" />
                            </div>

                            {{-- Semester Aktif --}}
                            <div>
                                <x-input-label for="semester_aktif" :value="__('Semester Aktif')" />
                                <select id="semester_aktif" name="semester_aktif" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    @foreach ($availableSemester as $semester)
                                        <option value="{{ $semester }}" @selected(old('semester_aktif', $semesterAktif) == $semester)>
                                            Semester {{ $semester }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('semester_aktif')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Simpan Pengaturan') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>