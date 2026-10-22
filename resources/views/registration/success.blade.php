@extends('layouts.guest')

@section('title', $student->is_email_verified ? __('Registration Successful') : __('Verify Email'))

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto w-full">
        @if(!$student->is_email_verified)
        <div class="bg-white border-2 border-red-500/20 rounded-3xl p-10 text-center shadow-xl shadow-red-500/10">
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tight mb-4">{{ __('Verification Pending') }}</h1>
            <p class="text-lg text-gray-600 font-bold mb-8">
                {{ __('Your email address has not been verified yet.') }}
            </p>
            <p class="text-sm text-gray-500 mb-8">
                {{ __('Please complete the registration process properly or contact support for assistance.') }}
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-10 py-4 bg-gray-900 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-gray-800 transition-all">
                {{ __('Return to Home') }}
            </a>
        </div>
        @else
        <div class="bg-white border-2 border-green-500/20 rounded-3xl p-10 text-center shadow-xl shadow-green-500/10 hover:-translate-y-1 transition-transform duration-300">
            
            <!-- Success Icon -->
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-8 animate-[bounce_1s_ease-in-out]">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tight mb-4">{{ __('Registration Complete!') }}</h1>
            <p class="text-lg text-gray-600 font-bold mb-8">
                {{ __('Thank you, :name. Your registration for the PRAMUKH SANKALP Programme is now complete.', ['name' => $student->fname]) }}
            </p>

            <!-- Registration Details Card -->
            <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 mb-8 text-left">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('Registration ID') }}</p>
                        <p class="text-lg font-black text-gray-900">#{{ $student->student_id }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('Status') }}</p>
                        @if($student->registered_via_school_link)
                            <p class="text-sm font-bold text-blue-500 uppercase tracking-widest">{{ __('Pending School Approval') }}</p>
                        @elseif($student->fees_status === 'Pending')
                            <p class="text-sm font-bold text-orange-500 uppercase tracking-widest">{{ __('Verified & Pending Payment') }}</p>
                        @elseif($student->fees_status === 'In Progress')
                            <p class="text-sm font-bold text-blue-500 uppercase tracking-widest">{{ __('Payment In Progress') }}</p>
                        @elseif($student->fees_status === 'Paid')
                            <p class="text-sm font-bold text-green-600 uppercase tracking-widest">{{ __('Verified & Paid') }}</p>
                        @else
                            <p class="text-sm font-bold text-gray-500 uppercase tracking-widest">{{ $student->fees_status }}</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('Subscription') }}</p>
                        <p class="text-sm font-bold text-gray-900">{{ $student->registered_via_school_link ? __('School Sponsored') : ($student->subscription ?? __('Not Selected')) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">{{ __('Document') }}</p>
                        <p class="text-sm font-bold text-green-600 uppercase tracking-widest">{{ __('Uploaded') }}</p>
                    </div>
                </div>
            </div>

            @if($student->registered_via_school_link)
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 mb-8 text-left">
                <p class="text-sm text-blue-800 font-bold mb-2">
                    {{ __('School Registration Flow:') }}
                </p>
                <p class="text-sm text-blue-700">
                    {{ __('Your registration has been successfully received. Your school administration will review and approve your registration shortly. Once approved, you will be enrolled into the appropriate batch.') }}
                </p>
            </div>
            @elseif($student->fees_status === 'Pending')
            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-6 mb-8 text-left">
                <p class="text-sm text-orange-800 font-bold mb-2">
                    {{ __('Payment Pending:') }}
                </p>
                <p class="text-sm text-orange-700">
                    {{ __('You have opted to pay later. A payment link has been sent to your registered email address. You can complete the payment via the email link or by logging into your student dashboard. Your enrolment will be fully processed once the payment is completed.') }}
                </p>
            </div>
            @else
            <p class="text-sm text-gray-500">
                {{ __('A confirmation email has been sent to your registered email address.') }}
            </p>
            <p class="text-sm text-gray-500">
                {{ __('Your course will be assigned to you on our mobile app shortly. You will receive an email containing your login credentials (user ID and password) to access the platform.') }}
            </p>
            <p class="text-sm text-gray-500 mb-8">
                {{ __('Furthermore, your physical study materials will be despatched soon. You will receive a formal notification as soon as they have been sent out.') }}
            </p>
            @endif

            @if($student->registered_via_school_link)
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-10 py-4 bg-[#00bde0] text-white rounded-2xl font-black uppercase tracking-widest hover:bg-[#0099b8] hover:shadow-lg hover:shadow-[#00bde0]/20 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                {{ __('Return to Home') }}
            </a>
            @elseif($student->fees_status === 'Pending')
            <div class="mt-8 flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="{{ route('registration.plans', $encodedId) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-10 py-4 bg-[#00bde0] text-white rounded-2xl font-black uppercase tracking-widest hover:bg-[#0099b8] hover:shadow-lg hover:shadow-[#00bde0]/20 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    {{ __('Pay Now') }}
                </a>
                <a href="{{ route('home') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-10 py-4 bg-white border-2 border-gray-200 text-gray-700 rounded-2xl font-black uppercase tracking-widest hover:bg-gray-50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    {{ __('Return to Home') }}
                </a>
            </div>
            @else
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-10 py-4 bg-[#00bde0] text-white rounded-2xl font-black uppercase tracking-widest hover:bg-[#0099b8] hover:shadow-lg hover:shadow-[#00bde0]/20 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                {{ __('Return to Home') }}
            </a>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
