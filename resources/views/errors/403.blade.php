@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="text-center">
        <div class="mb-6">
            <svg class="mx-auto h-24 w-24 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        
        <h1 class="text-4xl font-bold text-gray-900 mb-2">403</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Access Denied</h2>
        <p class="text-gray-600 mb-8 max-w-md mx-auto">
            You don't have permission to access this resource. Please contact your administrator if you believe this is an error.
        </p>
        
        <div class="space-x-4">
            <a href="{{ url()->previous() }}" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-6 py-3 rounded-lg transition">
                Go Back
            </a>
            <a href="{{ route('dashboard') }}" class="inline-block bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-6 py-3 rounded-lg transition">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
