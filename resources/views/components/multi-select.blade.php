@props([
    'name' => '',
    'label' => '',
    'options' => [],
    'selected' => [],
    'placeholder' => 'Select options...',
    'valueKey' => 'id',
    'labelKey' => 'name'
])

<div x-data="multiSelect({{ json_encode($selected) }}, {{ json_encode($options) }}, '{{ $valueKey }}', '{{ $labelKey }}', '{{ $name }}')" class="relative">
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    
    <!-- Hidden inputs for selected values -->
    <template x-for="(value, index) in selectedValues" :key="index">
        <input type="hidden" :name="'{{ $name }}[]'" :value="value">
    </template>
    
    <!-- Display Box -->
    <div @click="open = !open" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent cursor-pointer bg-white min-h-[42px] flex items-center justify-between">
        <div class="flex flex-wrap gap-1 flex-1">
            <template x-if="selectedValues.length === 0">
                <span class="text-gray-400 text-sm">{{ $placeholder }}</span>
            </template>
            <template x-for="value in selectedValues" :key="value">
                <span class="inline-flex items-center px-2 py-1 rounded bg-[#00bde0] text-white text-xs font-medium">
                    <span x-text="getLabel(value)"></span>
                    <button type="button" @click.stop="removeValue(value)" class="ml-1 text-white hover:text-gray-100">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            </template>
        </div>
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
    
    <!-- Dropdown -->
    <div x-show="open" @click.away="open = false" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto"
         x-cloak>
        
        <!-- Search Box -->
        <div class="p-2 border-b border-gray-200">
            <input type="text" x-model="searchQuery" placeholder="Search..." 
                   class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
        </div>
        
        <!-- Options -->
        <div class="py-1">
            @foreach($options as $option)
                @php
                    $value = is_array($option) ? $option[$valueKey] : $option->$valueKey;
                    $optionLabel = is_array($option) ? $option[$labelKey] : $option->$labelKey;
                @endphp
                <div x-show="matchesSearch('{{ $optionLabel }}')" 
                     @click="toggleValue('{{ $value }}')"
                     class="px-4 py-2 hover:bg-gray-50 cursor-pointer flex items-center justify-between">
                    <span class="text-sm text-gray-900">{{ $optionLabel }}</span>
                    <svg x-show="isSelected('{{ $value }}')" class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            @endforeach
        </div>
    </div>
</div>

@once
<script>
function multiSelect(initialSelected, options, valueKey, labelKey, name) {
    return {
        open: false,
        searchQuery: '',
        selectedValues: Array.isArray(initialSelected) ? initialSelected : [],
        options: options,
        valueKey: valueKey,
        labelKey: labelKey,
        name: name,
        
        init() {
            this.$watch('selectedValues', (value) => {
                this.$dispatch('multi-select-changed', {
                    name: this.name,
                    values: value
                });
            });
        },
        
        toggleValue(value) {
            const index = this.selectedValues.indexOf(value);
            if (index > -1) {
                this.selectedValues.splice(index, 1);
            } else {
                this.selectedValues.push(value);
            }
        },
        
        removeValue(value) {
            const index = this.selectedValues.indexOf(value);
            if (index > -1) {
                this.selectedValues.splice(index, 1);
            }
        },
        
        isSelected(value) {
            return this.selectedValues.includes(value);
        },
        
        getLabel(value) {
            const option = this.options.find(opt => {
                const optValue = typeof opt === 'object' ? opt[this.valueKey] : opt;
                return String(optValue) === String(value);
            });
            return option ? (typeof option === 'object' ? option[this.labelKey] : option) : value;
        },
        
        matchesSearch(label) {
            if (!this.searchQuery) return true;
            return label.toLowerCase().includes(this.searchQuery.toLowerCase());
        }
    }
}
</script>
@endonce

<style>
    [x-cloak] { display: none !important; }
</style>
