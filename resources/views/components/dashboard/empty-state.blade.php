@props(['icon' => '', 'message' => 'No data found', 'colspan' => 1])

<tr>
    <td colspan="{{ $colspan }}" class="px-6 py-12 text-center">
        <div class="flex flex-col items-center">
            @if($icon)
                {!! $icon !!}
            @else
                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
            @endif
            <p class="text-sm text-gray-500">{{ $message }}</p>
            @if(isset($action))
                <div class="mt-4">
                    {{ $action }}
                </div>
            @endif
        </div>
    </td>
</tr>
