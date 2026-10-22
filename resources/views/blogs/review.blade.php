@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Review Blog</h1>
        </div>
        <a href="{{ route('blogs.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">Back to List</a>
    </div>

    <!-- Blog Content -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 space-y-6 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $blog->title }}</h1>
            <div class="flex items-center text-sm text-gray-500 gap-4 mb-6 pb-6 border-b">
                <span>By: {{ $blog->student->fname }} {{ $blog->student->lname }}</span>
                <span>•</span>
                <span>{{ $blog->created_at->format('F d, Y') }}</span>
                <span>•</span>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $blog->status }}</span>
            </div>
            
            <div class="prose max-w-none text-gray-800">
                {!! $blog->content !!}
            </div>
        </div>
    </div>
    
    <!-- Review Form -->
    <form method="POST" action="{{ route('blogs.process-review', $blog->id) }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        
        <div>
            <h2 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Review Decision</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Status -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Action <span class="text-red-500">*</span></label>
                    <div class="flex items-center space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="status" value="Approved" class="text-green-600 focus:ring-green-500 focus:border-green-500" {{ old('status') == 'Approved' ? 'checked' : '' }} required>
                            <span class="ml-2 text-sm text-gray-700">Approve</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="status" value="Rejected" class="text-red-600 focus:ring-red-500 focus:border-red-500" {{ old('status') == 'Rejected' ? 'checked' : '' }} required>
                            <span class="ml-2 text-sm text-gray-700">Reject</span>
                        </label>
                    </div>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Review Comment -->
            <div class="mb-6">
                <label for="review_comment" class="block text-sm font-medium text-gray-700 mb-1">
                    Review Comment (Optional)
                </label>
                <textarea name="review_comment" id="review_comment" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('review_comment') border-red-500 @enderror">{{ old('review_comment', $blog->review_comment) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Provide feedback if rejecting or suggesting improvements.</p>
                @error('review_comment')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t gap-3">
            <a href="{{ route('blogs.index') }}" class="bg-white border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-6 py-2 rounded-lg transition border">
                Cancel
            </a>
            <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-6 py-2 rounded-lg transition">
                Submit Review
            </button>
        </div>
    </form>
</div>
@endsection
