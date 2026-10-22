@props(['icon' => '', 'title', 'description' => '', 'time' => '', 'href' => '#'])

<a href="{{ $href }}" class="block hover:bg-gray-50 transition-colors">
    <div class="px-6 py-4 flex items-start space-x-4">
        @if($icon)
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-[#00bde0] bg-opacity-10 rounded-full flex items-center justify-center">
                    {!! $icon !!}
                </div>
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900">{{ $title }}</p>
            @if($description)
                <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
            @endif
        </div>
        @if($time)
            <div class="flex-shrink-0">
                <p class="text-xs text-gray-400">{{ $time }}</p>
            </div>
        @endif
    </div>
</a>
