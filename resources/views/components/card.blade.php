@props(['padding' => true])

@php
$paddingClass = $padding ? 'p-6' : '';
@endphp

<div {{ $attributes->merge(['class' => "bg-white rounded-lg shadow-sm border border-gray-200 $paddingClass"]) }}>
    {{ $slot }}
</div>
