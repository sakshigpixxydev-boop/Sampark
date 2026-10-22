@props(['title', 'subtitle' => ''])

<div class="px-6 py-5 border-b border-gray-200">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-semibold text-gray-900">{{ $title }}</h3>
            @if($subtitle)
                <p class="mt-1 text-sm text-gray-500">{{ $subtitle }}</p>
            @endif
        </div>
        @if(isset($actions))
            <div>
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
