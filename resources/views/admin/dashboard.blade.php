{{-- resources/views/admin/dashboard.blade.php (Tidak perlu diubah) --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12"> {{-- Padding ini mungkin bisa dihilangkan/disesuaikan karena sudah ada di layout --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> {{-- Ini juga bisa dihilangkan/disesuaikan --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("Selamat datang di Dashboard Admin!") }}
                    {{-- Tambahkan statistik atau notifikasi admin di sini --}}
                    <p>Testetstets</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>