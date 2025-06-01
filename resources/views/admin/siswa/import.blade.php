<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Impor Data Siswa dari CSV') }}
        </h2>
    </x-slot>

    {{-- Loading Overlay --}}
    <div id="loadingOverlay" style="display: none;" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50">
        <div class="flex flex-col items-center">
            <div class="animate-spin rounded-full h-16 w-16 border-t-4 border-b-4 border-white mb-4"></div>
            <p class="text-white text-lg font-semibold">Mengimpor data siswa, mohon tunggu...</p>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Tombol Kembali ke Daftar Siswa --}}
            <div class="mb-6">
                <a href="{{ route('admin.siswa.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('Kembali ke Daftar Siswa') }}
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- Panduan Format CSV --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="guide-container">
                        <div class="header bg-gradient-to-br from-indigo-600 to-purple-700 text-white p-6 text-center rounded-t-lg">
                            <h1 class="text-xl font-semibold mb-2">Panduan Import Data Siswa</h1>
                            <p class="text-indigo-100 text-sm">Gunakan template CSV yang sudah disediakan</p>
                        </div>
                        
                        <div class="p-6">
                            {{-- ... (rest of your guide content) ... --}}
                            <div class="mb-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z" />
                                    </svg>
                                    Cara Menggunakan Template
                                </h2>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                    <div class="flex items-center mb-2">
                                        <span class="font-semibold text-green-800">Gunakan Template CSV Yang Sudah Disediakan</span>
                                    </div>
                                    <p class="text-green-700 text-sm">Unduh template CSV yang sudah disediakan, lalu tambahkan data siswa ke dalam file tersebut.    </p>
                                </div>
                            </div>

                            <div class="mb-6">
                                <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z" />
                                    </svg>
                                    Keterangan Kolom Data
                                </h2>
                                <p class="text-gray-600 text-sm mb-4">Template sudah memiliki kolom-kolom berikut untuk diisi:</p>
                                
                                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                    <div class="flex items-center p-3 bg-white rounded-md border-l-4 border-gray-300">
                                        <code class="bg-gray-200 px-2 py-1 rounded text-sm font-mono font-semibold mr-3">no_urut</code>
                                        <span class="text-sm text-gray-600 flex-1">Boleh kosong atau diisi angka urut</span>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Opsional</span>
                                    </div>
                                    
                                    <div class="flex items-center p-3 bg-white rounded-md border-l-4 border-red-400">
                                        <code class="bg-gray-200 px-2 py-1 rounded text-sm font-mono font-semibold mr-3">no_induk</code>
                                        <span class="text-sm text-gray-600 flex-1">Akan menjadi NIS siswa</span>
                                        <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded-full">Wajib</span>
                                    </div>
                                    
                                    <div class="flex items-center p-3 bg-white rounded-md border-l-4 border-red-400">
                                        <code class="bg-gray-200 px-2 py-1 rounded text-sm font-mono font-semibold mr-3">nama_peserta_didik</code>
                                        <span class="text-sm text-gray-600 flex-1">Nama lengkap siswa</span>
                                        <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded-full">Wajib</span>
                                    </div>
                                    
                                    <div class="flex items-center p-3 bg-white rounded-md border-l-4 border-green-400">
                                        <code class="bg-gray-200 px-2 py-1 rounded text-sm font-mono font-semibold mr-3">lp</code>
                                        <span class="text-sm text-gray-600 flex-1">L (Laki-laki) atau P (Perempuan)</span>
                                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded-full">Opsional</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="font-semibold text-blue-800 mb-2 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9,22A1,1 0 0,1 8,21V18H4A2,2 0 0,1 2,16V4C2,2.89 2.9,2 4,2H20A2,2 0 0,1 22,4V16A2,2 0 0,1 20,18H13.9L10.2,21.71C10,21.9 9.75,22 9.5,22V22H9Z" />
                                        </svg>
                                        Langkah-langkah Penggunaan:
                                    </div>
                                    <ol class="text-blue-700 text-sm space-y-2 ml-4 list-decimal">
                                        <li><strong>Unduh template CSV</strong> dengan klik tombol "Unduh Template CSV" di atas</li>
                                        <li><strong>Buka file</strong> menggunakan Excel, Google Sheets, atau aplikasi spreadsheet lainnya</li>
                                        <li><strong>Isi data siswa</strong> pada baris-baris kosong (jangan ubah header)</li>
                                        <li><strong>Simpan file</strong> dalam format CSV</li>
                                        <li><strong>Upload file</strong> yang sudah diisi melalui form di atas</li>
                                    </ol>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <div class="font-semibold text-red-800 mb-2 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                                        </svg>
                                        Penting untuk Diperhatikan
                                    </div>
                                    <p class="text-red-700 text-sm"><strong>Pastikan tidak ada NIS yang duplikat</strong>, baik di dalam file maupun yang sudah ada di sistem. NIS harus unik untuk setiap siswa.</p>
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="font-semibold text-blue-800 mb-2">Informasi Akun Siswa</div>
                                <p class="text-blue-700 text-sm">Setelah data berhasil diimpor, sistem akan otomatis membuat akun untuk setiap siswa dengan:</p>
                                <ul class="text-blue-700 text-sm mt-2 ml-4 list-disc">
                                    <li><strong>Username:</strong> NIS siswa</li>
                                    <li><strong>Password awal:</strong> NIS siswa</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Form Import --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        @include('layouts.partials.alert-messages')

                        @if (session('import_validation_errors'))
                            <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                                <p class="font-bold">Kesalahan Validasi Data CSV:</p>
                                <ul class="list-disc list-inside text-sm">
                                    @foreach(session('import_validation_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                         @if (session('import_errors'))
                            <div class="mb-4 p-4 bg-yellow-100 border border-yellow-300 text-yellow-700 rounded">
                                <p class="font-bold">Peringatan Selama Impor:</p>
                                <div class="text-sm">{!! session('import_errors') !!}</div>
                            </div>
                        @endif

                        <form id="siswaImportForm" method="POST" action="{{ route('admin.siswa.import') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-6">
                                <a href="{{ asset('templates/TEMPLATE IMPORT SISWA.csv') }}" download
                                   class="inline-flex items-center px-4 py-2 bg-white border border-blue-600 text-blue-600 rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-colors duration-150 shadow-sm">
                                    <i class="fas fa-download mr-2"></i> Unduh Template CSV
                                </a>
                            </div>

                            <div class="mt-4">
                                <x-input-label for="kelas_id" :value="__('Pilih Kelas untuk Siswa yang Diimpor')" />
                                <select id="kelas_id" name="kelas_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach ($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->nama_kelas }} ({{ $kelas->tahun_ajaran }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('kelas_id')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="csv_file" :value="__('Pilih File CSV')" />
                                <input id="csv_file" class="block mt-1 w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:bg-indigo-600 file:text-white file:text-sm file:font-semibold file:border-0 file:py-2 file:px-4 hover:file:bg-indigo-700" type="file" name="csv_file" required accept=".csv,.txt">
                                <x-input-error :messages="$errors->get('csv_file')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <x-primary-button>
                                    {{ __('Impor Siswa') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const importForm = document.getElementById('siswaImportForm'); // Get form by ID
            const loadingOverlay = document.getElementById('loadingOverlay');
            const fileInput = document.getElementById('csv_file');
            const kelasInput = document.getElementById('kelas_id');

            if (importForm && loadingOverlay && fileInput && kelasInput) {
                importForm.addEventListener('submit', function(event) {
                    // Check if file and class are selected
                    let isValid = true;
                    if (fileInput.files.length === 0) {
                        // You can add a custom message here or rely on Laravel's validation
                        isValid = false;
                    }
                    if (kelasInput.value === '') {
                        // You can add a custom message here
                        isValid = false;
                    }

                    if (isValid) {
                        loadingOverlay.style.display = 'flex';
                    } else {
                        // If not valid, prevent showing the loader and let HTML5/Laravel validation handle it
                        // event.preventDefault(); // Uncomment if you want to stop submission here for custom alerts
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>