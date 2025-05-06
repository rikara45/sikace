@if (session('success'))
    <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-700 rounded">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded">
        {{ session('error') }}
    </div>
@endif

{{-- Tampilkan Error Validasi Global (jika ada) --}}
@if ($errors->any() && !session('success') && !session('error')) {{-- Hanya tampilkan jika belum ada pesan session --}}
    <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded">
        <p class="font-bold">Terjadi Kesalahan:</p>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif