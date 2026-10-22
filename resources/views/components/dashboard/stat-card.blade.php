@props(['title', 'value', 'icon' => '', 'href' => '#', 'color' => 'primary'])

@php
$gradientClass = match($color) {
    'primary' => 'from-[#00bde0] to-[#0099b8]',
    'success' => 'from-green-500 to-green-600',
    'warning' => 'from-yellow-500 to-yellow-600',
    'danger' => 'from-red-500 to-red-600',
    'info' => 'from-blue-500 to-blue-600',
    default => 'from-[#00bde0] to-[#0099b8]',
};
@endphp

<a href="{{ $href }}" class="block">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">{{ $title }}</p>
                <p class="text-4xl font-bold text-gray-900 mt-3">{{ $value }}</p>
            </div>
            @if($icon)
                <div class="bg-gradient-to-br {{ $gradientClass }} p-4 rounded-xl shadow-md">
                    {!! $icon !!}
                </div>
            @endif
        </div>
    </div>
</a>
