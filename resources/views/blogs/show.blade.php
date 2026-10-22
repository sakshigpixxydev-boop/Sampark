@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Blog Details</h1>
        </div>
        <a href="{{ route('blogs.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">Back to List</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 space-y-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $blog->title }}</h1>
            <div class="flex items-center text-sm text-gray-500 gap-4 mb-6 pb-6 border-b">
                <span>By: {{ $blog->student->fname }} {{ $blog->student->lname }}</span>
                <span>•</span>
                <span>{{ $blog->created_at->format('F d, Y') }}</span>
                <span>•</span>
                @if($blog->status == 'Approved')
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ $blog->status }}</span>
                @elseif($blog->status == 'Rejected')
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ $blog->status }}</span>
                @elseif($blog->status == 'Pending')
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ $blog->status }}</span>
                @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">{{ $blog->status }}</span>
                @endif
            </div>
            
            <div class="prose max-w-none text-gray-800">
                {!! $blog->content !!}
            </div>
        </div>
    </div>
    
    @if($blog->review_comment)
    <div class="mt-8 bg-gray-50 border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Reviewer Feedback</h3>
        <p class="text-gray-700">{{ $blog->review_comment }}</p>
    </div>
    @endif
</div>
@endsection
