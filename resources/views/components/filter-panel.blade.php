@props(['action' => '', 'method' => 'GET', 'expanded' => null])

<div x-data="filterPanel()" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
    <!-- Filter Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
            <h3 class="text-sm font-medium text-gray-900">Filters</h3>
            <span x-show="activeFilterCount() > 0" class="ml-2 px-2 py-0.5 text-xs font-medium bg-[#00bde0] text-white rounded-full" x-text="activeFilterCount()"></span>
        </div>
        <button @click="expanded = !expanded" type="button" class="text-gray-500 hover:text-gray-700">
            <svg x-show="!expanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            <svg x-show="expanded" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
            </svg>
        </button>
    </div>

    <!-- Filter Content -->
    <div x-show="expanded" x-collapse>
        <form method="{{ $method }}" action="{{ $action }}" class="p-6">
            @csrf
            <div class="space-y-4">
                {{ $slot }}
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200">
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Apply Filters
                </button>
                <a href="{{ $action . (str_contains($action, '?') ? '&' : '?') . 'clear_filters=1' }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition text-center">
                    Clear All
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function filterPanel() {
    return {
        expanded: {{ $expanded !== null ? ($expanded ? 'true' : 'false') : (count(request()->except(['page', 'status', 'clear_filters'])) > 0 ? 'true' : 'false') }},
        
        activeFilterCount() {
            const form = this.$el.querySelector('form');
            if (!form) return 0;
            
            const inputs = form.querySelectorAll('input[name], select[name]');
            let count = 0;
            
            inputs.forEach(input => {
                if (input.name && input.name !== 'status' && input.name !== 'page' && input.value && input.value !== '') {
                    count++;
                }
            });
            
            return count;
        },
        
        clearFilters() {
            const form = this.$el.querySelector('form');
            if (form) {
                const inputs = form.querySelectorAll('input[name], select[name]');
                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else if (input.name !== 'status' && input.name !== 'page') {
                        input.value = '';
                    }
                });
                form.submit();
            }
        }
    }
}
</script>
