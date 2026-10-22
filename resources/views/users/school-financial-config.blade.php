@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">School Financial Configuration</h1>
        <p class="mt-1 text-sm text-gray-600">Assign School Fee Plans to School User: <strong>{{ $user->fname }} {{ $user->lname }}</strong></p>
    </div>

    <!-- Form -->
    <form action="{{ route('users.school-financial-config.update', $user->sysuser_id) }}" method="POST">
        @csrf

        <div class="space-y-6">
            <!-- School Fee Plans Multi-Select -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 border-bottom pb-2">Assigned School Fee Plans</h2>
                
                <div>
                    <label for="fee_plan_ids" class="block text-sm font-medium text-gray-700 mb-2">
                        Select School Fee Plans (Hold Ctrl/Cmd to select multiple)
                    </label>
                    <select name="fee_plan_ids[]" id="fee_plan_ids" multiple size="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('fee_plan_ids') border-red-500 @enderror">
                        @foreach($feePlans as $plan)
                            <option value="{{ $plan->fee_plan_id }}" {{ in_array($plan->fee_plan_id, $assignedFeePlanIds) ? 'selected' : '' }}>
                                {{ $plan->fee_name }} (₹{{ number_format($plan->amount, 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('fee_plan_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">Selected school fee plans will be assigned to this school user.</p>
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
