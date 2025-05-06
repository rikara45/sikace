{{-- filepath: resources/views/components/nav-link.blade.php --}}
@props(['active'])

@php
$classes = ($active ?? false)
    ? 'bg-blue-900 text-gray-500 font-semibold transition duration-150 ease-in-out' // Aktif: background biru gelap, teks abu
    : 'text-black hover:text-white hover:bg-blue-800 transition duration-150 ease-in-out'; // Tidak aktif: teks hitam, hover jadi putih
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>