@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Add New Parcel Company</h1>
        <p class="text-sm text-gray-600 mt-1">Create a new parcel company record</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('parcel-companies.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="companyname" class="block text-sm font-medium text-gray-700 mb-1">
                    Company Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="companyname" id="companyname" value="{{ old('companyname') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('companyname') border-red-500 @enderror"
                       placeholder="Enter company name">
                @error('companyname')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="trackingurl" class="block text-sm font-medium text-gray-700 mb-1">
                    Tracking URL
                </label>
                <input type="url" name="trackingurl" id="trackingurl" value="{{ old('trackingurl') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('trackingurl') border-red-500 @enderror"
                       placeholder="https://track.example.com/?id={tracking_number}">
                <p class="mt-1 text-xs text-gray-500">Use {tracking_number} as placeholder for the tracking number</p>
                @error('trackingurl')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="apiendpoint" class="block text-sm font-medium text-gray-700 mb-1">
                    API Endpoint
                </label>
                <input type="url" name="apiendpoint" id="apiendpoint" value="{{ old('apiendpoint') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('apiendpoint') border-red-500 @enderror"
                       placeholder="https://api.example.com/v1">
                @error('apiendpoint')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="apikey" class="block text-sm font-medium text-gray-700 mb-1">
                    API Key
                </label>
                <input type="text" name="apikey" id="apikey" value="{{ old('apikey') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('apikey') border-red-500 @enderror"
                       placeholder="Enter API key">
                @error('apikey')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="supportcontact" class="block text-sm font-medium text-gray-700 mb-1">
                    Support Contact
                </label>
                <input type="text" name="supportcontact" id="supportcontact" value="{{ old('supportcontact') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('supportcontact') border-red-500 @enderror"
                       placeholder="Enter support contact">
                @error('supportcontact')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="deliverytimeestimate" class="block text-sm font-medium text-gray-700 mb-1">
                    Delivery Time Estimate
                </label>
                <input type="text" name="deliverytimeestimate" id="deliverytimeestimate" value="{{ old('deliverytimeestimate') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('deliverytimeestimate') border-red-500 @enderror"
                       placeholder="e.g., 2-5 days">
                @error('deliverytimeestimate')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="inactive" value="1" {{ old('inactive') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]">
                    <span class="ml-2 text-sm text-gray-700">Mark as Inactive</span>
                </label>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
                <a href="{{ route('parcel-companies.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Create Company
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
