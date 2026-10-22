@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit School Type</h1>
        <p class="text-sm text-gray-600 mt-1">Update school type information</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('school-types.update', $schoolType->schooltype_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <!-- School Type Name -->
                <div>
                    <label for="schooltypename" class="block text-sm font-medium text-gray-700 mb-1">
                        School Type Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="schooltypename" 
                        id="schooltypename" 
                        value="{{ old('schooltypename', $schoolType->schooltypename) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('schooltypename') border-red-500 @enderror"
                        required
                    >
                    @error('schooltypename')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('school-types.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Update School Type
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
