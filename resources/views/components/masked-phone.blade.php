@props(['number' => null, 'class' => 'hover:text-[#00bde0] transition-colors'])

@php
    use App\Helpers\DataMaskingHelper;
    $canViewFull = DataMaskingHelper::canViewFullData();
    $displayNumber = DataMaskingHelper::maskPhone($number, $canViewFull);
@endphp

@if($number)
    @if($canViewFull)
        <a 
            href="tel:{{ $number }}" 
            onclick="navigator.clipboard.writeText('{{ $number }}'); alert('Copied to clipboard');" 
            class="{{ $class }} text-sm text-gray-900" 
            title="Click to copy and call (Admin - Full number visible)"
        >
            {{ $displayNumber }}
        </a>
    @else
        <span class="text-sm text-gray-500" title="Phone masked for security">
            {{ $displayNumber }}
        </span>
    @endif
@else
    <span class="text-sm text-gray-500">N/A</span>
@endif
