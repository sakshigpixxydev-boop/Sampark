@extends('layouts.app')

@section('title', 'Create Lead')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Page Header -->
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create New Lead</h2>
            <p class="mt-1 text-sm text-gray-600">Add a new school lead and generate registration URL</p>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('leads.store') }}"
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- School Name -->
                <div class="md:col-span-2">
                    <label for="school_name" class="block text-sm font-medium text-gray-700 mb-1">
                        School Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="school_name" id="school_name" value="{{ old('school_name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('school_name') border-red-500 @enderror">
                    @error('school_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" minlength="7" maxlength="15" inputmode="numeric" oninput="this.value=this.value.replace(/\D/g,'').slice(0,15)"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estimated Students -->
                <div>
                    <label for="estimated_students" class="block text-sm font-medium text-gray-700 mb-1">Estimated
                        Students</label>
                    <input type="number" name="estimated_students" id="estimated_students"
                        value="{{ old('estimated_students') }}" min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('estimated_students') border-red-500 @enderror">
                    @error('estimated_students')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website -->
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                    <input type="url" name="website" id="website" value="{{ old('website') }}" placeholder="https://"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('website') border-red-500 @enderror">
                    @error('website')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" id="address" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('address') border-red-500 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Agreement Expiry Date -->
                <div class="md:col-span-2">
                    <label for="tc_expiry_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Agreement Expiry Date
                    </label>
                    <input type="text" name="tc_expiry_date" id="tc_expiry_date" value="{{ old('tc_expiry_date') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('tc_expiry_date') border-red-500 @enderror" placeholder="DD/MM/YYYY">
                    <p class="mt-1 text-xs text-gray-500 italic">Select the date when this agreement will expire and trigger a notification.</p>
                    @error('tc_expiry_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom T&C for this School -->
                <div class="md:col-span-2 space-y-1">
                    <label for="editor" class="block text-sm font-medium text-gray-700">Custom Terms & Conditions</label>
                    <p class="text-xs text-gray-500 mb-2 italic">You can customize the terms for this specific school registration.</p>
                    <x-rich-text-editor name="tc_content" value="{{ old('tc_content', $defaultTemplate->content ?? '') }}" />
                    @error('tc_content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('leads.index') }}"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors">
                    Create Lead
                </button>
            </div>
        </form>
    </div>
@endsection