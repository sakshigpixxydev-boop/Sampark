@props(['title', 'subtitle' => ''])

<div class="mb-8">
    <h2 class="text-3xl font-bold text-gray-900">{{ $title }}</h2>
    @if($subtitle)
        <p class="mt-2 text-sm text-gray-600">{{ $subtitle }}</p>
    @endif
    @if(isset($actions))
        <div class="mt-4">
            {{ $actions }}
        </div>
    @endif
</div>
