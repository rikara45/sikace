{{-- filepath: resources/views/components/nav-link.blade.php --}}
@props(['active', 'sidebar' => false])

@php
if ($sidebar) {
    // Styling untuk link di dalam sidebar dengan background bg-slate-900
    $baseClasses = 'flex items-center px-4 py-2 rounded transition-colors duration-150 ease-in-out w-full';
    // Active: sky-500 border, bg-slate-800 (lighter than sidebar), white text
    $activeClasses = $baseClasses . ' border-l-4 border-sky-500 bg-slate-800 text-white font-semibold'; 
    // Inactive: slate-300 text, hover bg-slate-800 & white text
    $inactiveClasses = $baseClasses . ' text-slate-300 hover:bg-slate-800 hover:text-white'; 
} else {
    // Styling default Breeze untuk link di navigasi atas (jika masih digunakan)
    $baseClasses = 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out';
    $activeClasses = $baseClasses . ' border-indigo-400 text-gray-900 focus:outline-none focus:border-indigo-700';
    $inactiveClasses = $baseClasses . ' border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300';
}

$classes = ($active ?? false)
            ? $activeClasses
            : $inactiveClasses;
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>