@extends('layouts.guest')

@section('title', 'Token Expired')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
        <div class="max-w-md w-full text-center">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-2">Registration Link Expired</h2>
                <p class="text-gray-600 mb-6">
                    This registration link has expired or has already been used.
                </p>

                @if(isset($lead))
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                        <p class="text-sm text-gray-600 mb-2">School: <span
                                class="font-medium text-gray-900">{{ $lead->school_name }}</span></p>
                        @if($lead->token_expires_at)
                            <p class="text-sm text-gray-600">Expired on: <span
                                    class="font-medium text-gray-900">{{ $lead->token_expires_at->format('M d, Y') }}</span></p>
                        @endif
                    </div>
                @endif

                <p class="text-sm text-gray-600">
                    Please contact the administrator who sent you this link to request a new registration URL.
                </p>
            </div>
        </div>
    </div>
@endsection