@props(['number' => null, 'alpineNumber' => null, 'class' => 'hover:text-[#00bde0] transition-colors'])

@if($number || $alpineNumber)
<a 
    @if($number)
        href="tel:{{ $number }}" 
        onclick="navigator.clipboard.writeText('{{ $number }}'); alert('Copied to clipboard');" 
    @elseif($alpineNumber)
        x-bind:href="'tel:' + {{ $alpineNumber }}"
        @click="navigator.clipboard.writeText({{ $alpineNumber }}); alert('Copied to clipboard');"
        x-show="{{ $alpineNumber }}"
    @endif
    class="{{ $class }}" 
    title="Click to copy and call"
>
    @if($number)
        {{ $number }}
    @elseif($alpineNumber)
        <span x-text="{{ $alpineNumber }}"></span>
    @endif
</a>
@endif
