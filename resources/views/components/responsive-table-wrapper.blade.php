@props(['mobileCards' => false])

<div class="overflow-x-auto -mx-4 sm:mx-0">
    <div class="inline-block min-w-full align-middle">
        <div class="overflow-hidden {{ $mobileCards ? 'sm:rounded-lg sm:shadow' : '' }}">
            {{ $slot }}
        </div>
    </div>
</div>
