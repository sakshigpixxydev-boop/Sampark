@props(['type' => 'button', 'variant' => 'primary', 'href' => null])

@php
$classes = match($variant) {
    'primary' => 'bg-primary-500 hover:bg-primary-600 text-white',
    'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-800',
    'danger' => 'bg-red-500 hover:bg-red-600 text-white',
    'success' => 'bg-green-500 hover:bg-green-600 text-white',
    'outline' => 'border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white',
    default => 'bg-primary-500 hover:bg-primary-600 text-white',
};

$baseClasses = "inline-flex items-center justify-center px-4 py-2 font-medium rounded-lg transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed $classes";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </button>
@endif
