@props(['email' => null])

@php
    use App\Helpers\DataMaskingHelper;
    $canViewFull = DataMaskingHelper::canViewFullData();
    $displayEmail = DataMaskingHelper::maskEmail($email, $canViewFull);
@endphp

@if($email)
    <span class="text-sm {{ $canViewFull ? 'text-gray-900' : 'text-gray-500' }}" title="{{ $canViewFull ? 'Full email visible (Admin)' : 'Email masked for security' }}">
        {{ $displayEmail }}
    </span>
@else
    <span class="text-sm text-gray-500">N/A</span>
@endif
