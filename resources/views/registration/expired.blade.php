@extends('layouts.guest')

@section('title', __('Link Expired') . ' - ' . ($school->reg_form_name ?? __('Registration Form')))

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 md:p-10 rounded-3xl border border-gray-200 shadow-xl text-center">
        <!-- Logo Section -->
        <div class="flex justify-center mb-6">
            @if($school->logourl)
                <img src="{{ asset('storage/' . $school->logourl) }}" alt="{{ $school->schoolname }}" class="h-20 w-auto object-contain bg-white p-1 rounded-lg border border-gray-100">
            @else
                <img src="{{ asset('images/sankalp.png') }}" alt="{{ __('PRAMUKH SANKALP Programme') }}" class="h-16 w-auto object-contain">
            @endif
        </div>

        <!-- Status Icon -->
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 border border-red-100 text-red-500 mb-6">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <!-- Heading & Subheading -->
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">
            {{ $isFuture ? __('Registration Not Active') : __('Registration Expired') }}
        </h2>
        
        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mt-1">
            {{ $school->reg_form_name ?: __('School Registration') }}
        </p>

        <div class="mt-4 text-gray-600 space-y-3">
            <p class="text-sm">
                {{ __('The registration link for') }} <strong class="text-gray-900">{{ $school->schoolname }}</strong> {{ $isFuture ? __('is not active yet.') : __('has expired.') }}
            </p>
            
            <!-- Dates box -->
            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 text-left mt-6">
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-gray-500 font-medium">{{ __('Start Date:') }}</span>
                        <span class="text-gray-900 font-bold">
                            {{ $school->reg_start_date ? \Carbon\Carbon::parse($school->reg_start_date)->format('d M, Y h:i A') : __('N/A') }}
                        </span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200/60 pt-2">
                        <span class="text-gray-500 font-medium">{{ __('End Date:') }}</span>
                        <span class="text-gray-900 font-bold">
                            {{ $school->reg_end_date ? \Carbon\Carbon::parse($school->reg_end_date)->format('d M, Y h:i A') : __('N/A') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100">
            <p class="text-sm font-semibold text-gray-500">
                {{ __('Please contact your school administrator to obtain an active registration link.') }}
            </p>
        </div>
    </div>
</div>
@endsection
