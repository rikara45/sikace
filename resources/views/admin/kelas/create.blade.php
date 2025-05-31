<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Kelas Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.kelas.store') }}">
                        @csrf

                        <div class="mt-4">
                            <x-input-label for="nama_kelas" :value="__('Nama Kelas')" />
                            <x-text-input id="nama_kelas" class="block mt-1 w-full" type="text" name="nama_kelas" :value="old('nama_kelas')" required autofocus />
                            <x-input-error :messages="$errors->get('nama_kelas')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="tahun_ajaran" :value="__('Tahun Ajaran')" />
                            <select id="tahun_ajaran" name="tahun_ajaran" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Pilih Tahun Ajaran --</option>
                                @foreach ($tahunAjaranOptions as $tahun)
                                    <option value="{{ $tahun }}" @selected(old('tahun_ajaran', $tahunAjaranAktif ?? '') == $tahun)>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tahun_ajaran')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="wali_kelas_id" :value="__('Wali Kelas (Opsional)')" />
                            <select id="wali_kelas_id" name="wali_kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Pilih Wali Kelas --</option>
                                @foreach ($gurus as $guru)
                                    <option value="{{ $guru->id }}" @selected(old('wali_kelas_id') == $guru->id)>
                                        {{ $guru->nama_guru }} {{ $guru->nip ? '('.$guru->nip.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('wali_kelas_id')" class="mt-2" />
                        </div>
                        
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Mata Pelajaran & Guru Pengampu di Kelas Ini</h3>
                            <div class="space-y-4 border border-gray-200 rounded-md p-4">
                                @forelse ($mataPelajaranList as $mapel)
                                    <div class="mapel-item p-3 border rounded-md {{ $errors->has('mata_pelajaran_guru.'.$mapel->id) ? 'border-red-400' : 'border-gray-200' }}">
                                        <div class="flex items-center mb-2">
                                            <input type="checkbox" id="mapel_{{ $mapel->id }}" name="mata_pelajaran_ids[]" value="{{ $mapel->id }}" 
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mapel-checkbox"
                                                   data-mapel-id="{{ $mapel->id }}"
                                                   @if(is_array(old('mata_pelajaran_ids')) && in_array($mapel->id, old('mata_pelajaran_ids', []))) checked @endif>
                                            <label for="mapel_{{ $mapel->id }}" class="ml-2 text-sm font-medium text-gray-700">{{ $mapel->nama_mapel }} ({{ $mapel->kode_mapel }})</label>
                                        </div>
                                        <div class="guru-select-container ml-6 {{ (is_array(old('mata_pelajaran_ids')) && in_array($mapel->id, old('mata_pelajaran_ids', []))) ? '' : 'hidden' }}">
                                            <x-input-label :for="'mata_pelajaran_guru_'.$mapel->id" :value="__('Guru Pengampu')" class="text-xs text-gray-600 mb-1" />
                                            <select name="mata_pelajaran_guru[{{ $mapel->id }}]" id="mata_pelajaran_guru_{{ $mapel->id }}" 
                                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm guru-pengampu-select"
                                                    {{ (is_array(old('mata_pelajaran_ids')) && in_array($mapel->id, old('mata_pelajaran_ids', []))) ? '' : 'disabled' }}>
                                                <option value="">-- Pilih Guru Pengampu (Opsional) --</option>
                                                @foreach ($gurus as $guru)
                                                    <option value="{{ $guru->id }}" @selected(old('mata_pelajaran_guru.'.$mapel->id) == $guru->id)>
                                                        {{ $guru->nama_guru }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <x-input-error :messages="$errors->get('mata_pelajaran_guru.'.$mapel->id)" class="mt-1 text-xs" />
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">Belum ada data mata pelajaran. Silakan tambahkan terlebih dahulu.</p>
                                @endforelse
                            </div>
                            <x-input-error :messages="$errors->get('mata_pelajaran_ids')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.kelas.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Batal') }}
                            </a>
                            <x-primary-button>
                                {{ __('Simpan Kelas') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.mapel-checkbox').forEach(function(checkbox) {
                const mapelId = checkbox.dataset.mapelId;
                const guruSelectContainer = checkbox.closest('.mapel-item').querySelector('.guru-select-container');
                const guruSelect = guruSelectContainer.querySelector('.guru-pengampu-select');

                // Initial state based on checkbox
                if (checkbox.checked) {
                    guruSelectContainer.classList.remove('hidden');
                    guruSelect.disabled = false; // INI PENTING: harus false jika checked
                } else {
                    guruSelectContainer.classList.add('hidden');
                    guruSelect.disabled = true;
                    guruSelect.value = ''; 
                }

                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        guruSelectContainer.classList.remove('hidden');
                        guruSelect.disabled = false; // Di-enable saat dicentang
                    } else {
                        guruSelectContainer.classList.add('hidden');
                        guruSelect.disabled = true; // Di-disable saat tidak dicentang
                        guruSelect.value = ''; 
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>