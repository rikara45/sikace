<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Guru Dashboard') }}
            </h2>
            <!-- Tombol Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    {{ __('Logout') }}
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">{{ __("Selamat datang di Dashboard Guru!") }}</h3>
                    <p class="mb-4">Hai Kamu @Guru</p>
                    
                    {{-- Tambahkan ringkasan kelas atau nilai belum diinput di sini --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>