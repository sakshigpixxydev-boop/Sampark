@extends('layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Create New Permission</h2>
    <p class="text-sm text-gray-600 mt-1">Add a new permission to the system</p>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('permissions.store') }}" method="POST">
        @csrf
        
        <div class="space-y-6">
            <!-- Permission Key -->
            <div>
                <label for="permkey" class="block text-sm font-medium text-gray-700 mb-1">
                    Permission Key <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="permkey" 
                       id="permkey" 
                       value="{{ old('permkey') }}"
                       placeholder="e.g., 1000"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('permkey') border-red-500 @enderror"
                       required>
                <p class="mt-1 text-xs text-gray-500">Enter a unique numeric key for this permission</p>
                @error('permkey')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Permission Name -->
            <div>
                <label for="permname" class="block text-sm font-medium text-gray-700 mb-1">
                    Permission Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="permname" 
                       id="permname" 
                       value="{{ old('permname') }}"
                       placeholder="e.g., Manage Users"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('permname') border-red-500 @enderror"
                       required>
                @error('permname')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('permissions.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                Cancel
            </a>
            <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                Create Permission
            </button>
        </div>
    </form>
</div>
@endsection
