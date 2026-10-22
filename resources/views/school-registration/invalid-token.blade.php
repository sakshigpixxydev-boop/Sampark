@extends('layouts.guest')

@section('title', 'Invalid Link')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-2">Invalid Registration Link</h2>
                <p class="text-gray-600 mb-6">
                    The registration link you're trying to access is invalid or doesn't exist.
                </p>

                <p class="text-sm text-gray-600">
                    Please verify the link and try again, or contact the administrator for assistance.
                </p>

                <div class="mt-6">
                    <a href="{{ route('login') }}"
                        class="inline-block px-6 py-2 bg-[#00bde0] text-white rounded-lg hover:bg-[#00a8c9] transition-colors">
                        Go to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection