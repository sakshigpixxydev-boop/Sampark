@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit State</h1>
        <p class="text-sm text-gray-600 mt-1">Update state information</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('locations.states.update', $state->state_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label for="country_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Country <span class="text-red-500">*</span>
                    </label>
                    <select name="country_id" id="country_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('country_id') border-red-500 @enderror" required>
                        <option value="">Select Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->country_id }}" {{ old('country_id', $state->country_id) == $country->country_id ? 'selected' : '' }}>
                                {{ $country->cname }}
                            </option>
                        @endforeach
                    </select>
                    @error('country_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sname" class="block text-sm font-medium text-gray-700 mb-1">
                        State Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="sname" id="sname" value="{{ old('sname', $state->sname) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('sname') border-red-500 @enderror" required>
                    @error('sname')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('locations.states.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">Cancel</a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">Update State</button>
            </div>
        </form>
    </div>
</div>
@endsection
