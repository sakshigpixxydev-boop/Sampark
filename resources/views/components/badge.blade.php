@props(['variant' => 'default'])

@php
$classes = match($variant) {
    'success' => 'bg-green-100 text-green-800',
    'error' => 'bg-red-100 text-red-800',
    'warning' => 'bg-yellow-100 text-yellow-800',
    'info' => 'bg-blue-100 text-blue-800',
    'primary' => 'bg-[#00bde0] bg-opacity-10 text-[#00bde0]',
    default => 'bg-gray-100 text-gray-800',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full $classes"]) }}>
    {{ $slot }}
</span>
