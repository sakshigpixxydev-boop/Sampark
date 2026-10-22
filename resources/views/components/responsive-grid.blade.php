@props(['cols' => 3])

@php
$gridClass = match($cols) {
    1 => 'grid-cols-1',
    2 => 'grid-cols-1 md:grid-cols-2',
    3 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
    4 => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
    default => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
};
@endphp

<div {{ $attributes->merge(['class' => "grid $gridClass gap-6"]) }}>
    {{ $slot }}
</div>
