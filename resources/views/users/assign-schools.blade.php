@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Assign Schools</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Assign schools to {{ $user->fname }} {{ $user->mname }} {{ $user->lname }} ({{ $user->role->rolename ?? 'N/A' }})
                </p>
            </div>
            <a href="{{ route('users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                Back to Users
            </a>
        </div>
    </div>

    <!-- Assignment Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('users.update-schools', $user) }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Select Schools
                    </label>
                    <div class="flex gap-2">
                        <button type="button" onclick="selectAll()" class="text-sm text-[#00bde0] hover:text-[#0099b8]">
                            Select All
                        </button>
                        <span class="text-gray-300">|</span>
                        <button type="button" onclick="deselectAll()" class="text-sm text-[#00bde0] hover:text-[#0099b8]">
                            Deselect All
                        </button>
                    </div>
                </div>
                
                @if($schools->isEmpty())
                    <p class="text-sm text-gray-500 py-4">No active schools available.</p>
                @else
                    <div class="border border-gray-300 rounded-lg max-h-96 overflow-y-auto">
                        <div class="divide-y divide-gray-200">
                            @foreach($schools as $school)
                                <label class="flex items-center px-4 py-3 hover:bg-gray-50 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        name="schools[]" 
                                        value="{{ $school->school_id }}"
                                        {{ in_array($school->school_id, $assignedSchools) ? 'checked' : '' }}
                                        class="h-4 w-4 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded school-checkbox"
                                    >
                                    <span class="ml-3 text-sm text-gray-900">
                                        {{ $school->schoolname }}
                                        @if($school->city_village)
                                            <span class="text-gray-500">({{ $school->city_village }})</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
                
                @error('schools')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Save Assignments
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectAll() {
    document.querySelectorAll('.school-checkbox').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAll() {
    document.querySelectorAll('.school-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection
