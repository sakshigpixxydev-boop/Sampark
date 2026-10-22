@props(['title' => 'Filters'])

<div x-data="{ open: false }">
    <!-- Mobile Filter Button -->
    <button 
        @click="open = true"
        class="lg:hidden fixed bottom-4 right-4 z-40 bg-[#00bde0] text-white p-4 rounded-full shadow-lg hover:bg-[#0099b8] transition-colors"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
        </svg>
    </button>

    <!-- Bottom Sheet Overlay -->
    <div 
        x-show="open"
        @click="open = false"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"
        x-cloak
    ></div>

    <!-- Bottom Sheet -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="transform translate-y-full"
        x-transition:enter-end="transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="transform translate-y-0"
        x-transition:leave-end="transform translate-y-full"
        class="fixed bottom-0 inset-x-0 bg-white rounded-t-2xl shadow-2xl z-50 lg:hidden max-h-[80vh] overflow-y-auto"
        x-cloak
    >
        <!-- Handle Bar -->
        <div class="flex justify-center pt-3 pb-2">
            <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
        </div>

        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-4">
            {{ $slot }}
        </div>

        <!-- Actions -->
        @if(isset($actions))
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
