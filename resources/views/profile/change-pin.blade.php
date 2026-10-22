@extends('layouts.app')

@section('title', 'Change PIN')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Change PIN</h2>
    </div>

    <!-- Change PIN Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('pin.update.profile') }}">
            @csrf
            @method('PUT')

            <!-- Current PIN -->
            <div class="mb-4">
                <label for="current_pin" class="block text-sm font-medium text-gray-700 mb-1">
                    Current PIN <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="current_pin" 
                    name="current_pin" 
                    maxlength="4"
                    pattern="[0-9]{4}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('current_pin') border-red-500 @enderror"
                >
                @error('current_pin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- New PIN -->
            <div class="mb-4">
                <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">
                    New PIN <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="pin" 
                    name="pin" 
                    maxlength="4"
                    pattern="[0-9]{4}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('pin') border-red-500 @enderror"
                >
                @error('pin')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Must be exactly 4 digits</p>
            </div>

            <!-- Confirm PIN -->
            <div class="mb-6">
                <label for="pin_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm New PIN <span class="text-red-500">*</span>
                </label>
                <input 
                    type="password" 
                    id="pin_confirmation" 
                    name="pin_confirmation" 
                    maxlength="4"
                    pattern="[0-9]{4}"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                >
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('profile.show') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Change PIN
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
