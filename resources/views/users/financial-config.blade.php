@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Financial Configuration</h1>
        <p class="mt-1 text-sm text-gray-600">Assign Fee Plans to Initiator: <strong>{{ $user->fname }} {{ $user->lname }}</strong></p>
    </div>

    <!-- Form -->
    <form action="{{ route('users.financial-config.update', $user->sysuser_id) }}" method="POST">
        @csrf

        <div class="space-y-6">
            <!-- Fee Plan Select -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 border-bottom pb-2">Assigned Initiator Fee Plan</h2>
                
                <div>
                    <label for="config_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Fee Plan
                    </label>
                    <select name="config_id" id="config_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('config_id') border-red-500 @enderror">
                        <option value="">No Plan Assigned</option>
                        @foreach($configs as $config)
                            <option value="{{ $config->id }}" {{ isset($assignedConfigId) && $assignedConfigId == $config->id ? 'selected' : '' }}>
                                {{ $config->name }} ({{ $config->student_calculation_type == 1 ? 'Slab-Based' : 'Per-Student' }})
                            </option>
                        @endforeach
                    </select>
                    @error('config_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">Select the plan to use for the initiator's earnings calculations.</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-6">
                <a href="{{ route('users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-6 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-6 py-2 rounded-lg transition">
                    Save Configuration
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
