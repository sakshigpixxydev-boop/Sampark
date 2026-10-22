@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Write New Blog</h1>
            <p class="text-sm text-gray-600 mt-1">Share your knowledge and experiences</p>
        </div>
        <a href="{{ route('blogs.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">Back to List</a>
    </div>

    <form method="POST" action="{{ route('blogs.store') }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf

        <div class="grid grid-cols-1 gap-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                    Blog Title <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content -->
            <div class="space-y-1">
                <label for="editor" class="block text-sm font-medium text-gray-700">Content <span class="text-red-500">*</span></label>
                <x-rich-text-editor name="content" value="{{ old('content') }}" />
                @error('content') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-medium px-6 py-2 rounded-lg transition shadow-sm">
                Submit Blog
            </button>
        </div>
    </form>
</div>
@endsection
