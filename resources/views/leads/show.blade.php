@extends('layouts.app')

@section('title', 'Lead Details')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Lead Details</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $lead->school_name }}</p>
            </div>
            <div class="flex space-x-3">
                @if($lead->status === 'pending')
                    @canAccess(10202)
                    <a href="{{ route('leads.edit', $lead->lead_id) }}"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Edit
                    </a>
                    @endcanAccess
                    @canAccess(10202)
                    <form method="POST" action="{{ route('leads.regenerate-token', $lead->lead_id) }}" class="inline">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('Are you sure you want to generate a new registration URL? The old URL will no longer work.')"
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Regenerate URL
                        </button>
                    </form>
                    @endcanAccess
                @endif
                <a href="{{ route('leads.index') }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    Back to List
                </a>
            </div>
        </div>

        <!-- Registration URL Card -->
        @if($lead->status === 'pending')
            <div class="bg-gradient-to-r from-[#00bde0] to-[#00a8c9] rounded-lg shadow-lg p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Registration URL</h3>
                <p class="text-sm opacity-90 mb-4">Share this URL with the school to complete registration</p>
                <div class="flex items-center space-x-3">
                    <input type="text" id="registration-url" value="{{ $lead->getRegistrationUrl() }}" readonly
                        class="flex-1 px-4 py-2 rounded-lg bg-white text-gray-900 border-0">
                    <button onclick="copyToClipboard(this)"
                        class="px-6 py-2 bg-white text-[#00bde0] rounded-lg hover:bg-gray-100 transition-colors font-medium">
                        Copy
                    </button>
                </div>
                <p class="text-xs opacity-75 mt-3">
                    Token expires on: {{ $lead->token_expires_at->format('d/m/Y h:i A') }}
                </p>
            </div>
        @elseif($lead->status === 'registered')
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-green-900">School Registered</h3>
                        <p class="text-sm text-green-700">This lead has been successfully converted to a school registration.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-red-900">Token Expired</h3>
                        <p class="text-sm text-red-700">The registration URL has expired. Generate a new one to allow
                            registration.</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Lead Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Lead Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-6 border-b border-gray-100">
                <div>
                    <label class="block text-sm font-medium text-gray-500">School Name</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $lead->school_name }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Status</label>
                    <p class="mt-1">
                        @if($lead->status === 'pending')
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @elseif($lead->status === 'registered')
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Registered</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="mt-1 text-sm text-gray-900"><x-masked-email :email="$lead->email" /></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Phone</label>
                    <div class="mt-1 flex items-center gap-2 group">
                        <x-masked-phone :number="$lead->phone" class="text-sm text-gray-900 font-semibold hover:text-[#00bde0] transition-colors" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Estimated Students</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $lead->estimated_students ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Website</label>
                    <p class="mt-1 text-sm text-gray-900">
                        @if($lead->website)
                            <a href="{{ $lead->website }}" target="_blank"
                                class="text-[#00bde0] hover:underline">{{ $lead->website }}</a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                @if($lead->address)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Address</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $lead->address }}</p>
                    </div>
                @endif
                @if($lead->notes)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Notes</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $lead->notes }}</p>
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-500">Created By</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $lead->initiator->fname }} {{ $lead->initiator->lname }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Created At</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $lead->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>

            <!-- Snapshotted Terms & Conditions -->
            @if($lead->tc_content)
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Snapshotted Terms & Conditions
                    </h4>
                    <div class="bg-gray-50 rounded-lg p-6 border border-gray-100 max-h-96 overflow-y-auto">
                        <div class="prose prose-sm max-w-none text-gray-600">
                            {!! $lead->tc_content !!}
                        </div>
                    </div>
                    @if($lead->tc_expiry_date)
                        <p class="mt-3 text-xs text-red-500 font-medium italic">
                            Agreement Expiry: {{ $lead->tc_expiry_date->format('M d, Y') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @if($lead->school)
            <!-- Registered School Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Registered School</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">School Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $lead->school->schoolname }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Registration Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $lead->token_used_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function copyToClipboard(button) {
                const urlInput = document.getElementById('registration-url');
                urlInput.select();
                urlInput.setSelectionRange(0, 99999); // For mobile devices

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(urlInput.value).then(function () {
                        handleSuccess(button);
                    }, function (err) {
                        fallbackCopy(urlInput, button);
                    });
                } else {
                    fallbackCopy(urlInput, button);
                }
            }

            function fallbackCopy(urlInput, button) {
                try {
                    document.execCommand('copy');
                    handleSuccess(button);
                } catch (err) {
                    alert('Failed to copy URL');
                }
            }

            function handleSuccess(button) {
                const originalText = button.textContent;
                button.textContent = 'Copied!';
                button.classList.add('bg-green-500', 'text-white');
                button.classList.remove('bg-white', 'text-[#00bde0]');

                setTimeout(function () {
                    button.textContent = originalText;
                    button.classList.remove('bg-green-500', 'text-white');
                    button.classList.add('bg-white', 'text-[#00bde0]');
                }, 2000);
            }
        </script>
    @endpush
@endsection