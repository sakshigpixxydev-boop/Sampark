@props([
    'label' => 'Date Range',
    'nameFrom' => 'date_from',
    'nameTo' => 'date_to',
    'valueFrom' => '',
    'valueTo' => ''
])

<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <input 
                type="text" 
                name="{{ $nameFrom }}" 
                value="{{ $valueFrom }}"
                placeholder="DD/MM/YYYY"
                class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm"
            >
        </div>
        <div>
            <input 
                type="text" 
                name="{{ $nameTo }}" 
                value="{{ $valueTo }}"
                placeholder="DD/MM/YYYY"
                class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent text-sm"
            >
        </div>
    </div>
</div>
