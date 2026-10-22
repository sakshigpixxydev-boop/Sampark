@php
    $label = $status ?: 'N/A';
    $tone = match (strtolower((string) $status)) {
        'approved', 'paid', 'verified' => 'bg-green-100 text-green-800',
        'rejected', 'failed' => 'bg-red-100 text-red-800',
        'pending' => 'bg-amber-100 text-amber-800',
        'partial' => 'bg-blue-100 text-blue-800',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp
<span class="px-3 py-1 text-xs font-semibold rounded-full {{ $tone }}">{{ $label }}</span>
