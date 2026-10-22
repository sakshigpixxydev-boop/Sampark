@props(['label' => '', 'name' => '', 'type' => 'text', 'required' => false, 'error' => ''])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <input 
        type="{{ $type === 'date' ? 'text' : $type }}" 
        name="{{ $name }}" 
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent transition-colors {{ $error ? 'border-red-300 focus:ring-red-500' : 'border-gray-300' }} {{ $type === 'date' ? 'date-picker' : '' }}"
        {{ $type === 'date' ? 'placeholder="DD/MM/YYYY"' : '' }}
        {{ $attributes->except(['class', 'label', 'name', 'type', 'required', 'error']) }}
    >
    
    @if($error)
        <p class="text-sm text-red-600 mt-1">{{ $error }}</p>
    @endif
</div>
