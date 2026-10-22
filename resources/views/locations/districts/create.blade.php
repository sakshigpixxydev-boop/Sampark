@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Create District</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('locations.districts.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="state_id" class="block text-sm font-medium text-gray-700 mb-1">State <span class="text-red-500">*</span></label>
                    <select name="state_id" id="state_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" required>
                        <option value="">Select State</option>
                        @foreach($states as $state)
                            <option value="{{ $state->state_id }}">{{ $state->sname }} ({{ $state->country->cname ?? '' }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="dname" class="block text-sm font-medium text-gray-700 mb-1">District Name <span class="text-red-500">*</span></label>
                    <input type="text" name="dname" id="dname" value="{{ old('dname') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" required>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('locations.districts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">Cancel</a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">Create District</button>
            </div>
        </form>
    </div>
</div>
@endsection
