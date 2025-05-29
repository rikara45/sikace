<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if (Auth::user()->hasRole('siswa'))
                @php
                    // Ambil data siswa terkait user yang login
                    // Pastikan relasi 'siswa' sudah didefinisikan di model User.php
                    $siswa = Auth::user()->siswa; 
                @endphp
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <h3 class="text-lg font-medium text-gray-900">Informasi Akun Siswa</h3>
                        <p class="mt-1 text-sm text-gray-600 mb-6">
                            Untuk perubahan data, silakan hubungi administrasi.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <x-input-label :value="__('Nama Lengkap')" />
                                <x-text-input class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="Auth::user()->name" disabled readonly />
                            </div>
                            <div>
                                <x-input-label :value="__('Nomor Induk Siswa (NIS)')" />
                                <x-text-input class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="$siswa?->nis ?? 'Tidak tersedia'" disabled readonly />
                            </div>
                            @if($siswa?->nisn)
                            <div>
                                <x-input-label :value="__('NISN')" />
                                <x-text-input class="block mt-1 w-full bg-gray-100 cursor-not-allowed" type="text" :value="$siswa->nisn" disabled readonly />
                            </div>
                            @endif
                             {{-- Email tidak lagi relevan untuk ditampilkan sebagai info utama siswa di sini --}}
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            @else 
                {{-- Untuk Admin dan Guru --}}
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
                {{-- Delete user form bisa dikondisikan lebih lanjut jika perlu --}}
            @endif
        </div>
    </div>
</x-app-layout>
