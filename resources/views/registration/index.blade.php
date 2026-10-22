@extends('layouts.guest')

@section('title', __('PRAMUKH SANKALP Programme') . ' - ' . __('Registration Form'))

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col" x-data="registrationForm()" x-cloak>
    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 md:h-20">
                <!-- Logo Section -->
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        @if(session('school_registration_logo_url'))
                            <img src="{{ asset('storage/' . session('school_registration_logo_url')) }}" alt="{{ session('school_registration_school_name') }}" class="h-10 md:h-12 w-auto object-contain">
                        @else
                            <img src="{{ asset('images/sankalp.png') }}" alt="{{ __('PRAMUKH SANKALP Programme') }}" class="h-10 md:h-12 w-auto">
                        @endif
                    </div>
                </div>

                <!-- Language and Contact Section -->
                <div class="flex items-center gap-4 md:gap-8">
                    <!-- Contact - Desktop -->
                    <div class="hidden md:flex items-center gap-3 border-r border-gray-200 pr-8">
                        <div class="w-10 h-10 rounded-full bg-[#00bde0]/10 flex items-center justify-center text-[#00bde0]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h2.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-medium">{{ __('Any queries? Call us now!') }}</p>
                            @php
                                $coordNum = __('Coordinator_Number');
                                $coordClean = str_replace(' ', '', $coordNum);
                            @endphp
                            <a href="tel:{{ $coordClean }}" onclick="navigator.clipboard.writeText('{{ $coordNum }}'); alert('Copied to clipboard');" class="text-sm font-bold text-gray-900 hover:text-[#00bde0] transition-colors inline-block" title="Click to copy and call">
                                {{ $coordNum }}
                            </a>
                        </div>
                    </div>

                    <!-- Language Switcher -->
                    <div class="flex gap-1 bg-gray-100 p-1 rounded-lg">
                        <a href="{{ route('language.switch', 'en') }}" class="px-3 py-1.5 rounded-md text-xs font-bold transition-all {{ App::getLocale() === 'en' ? 'bg-[#00bde0] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">EN</a>
                        <a href="{{ route('language.switch', 'hi') }}" class="px-3 py-1.5 rounded-md text-xs font-bold transition-all {{ App::getLocale() === 'hi' ? 'bg-[#00bde0] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">હિન્</a>
                        <a href="{{ route('language.switch', 'gu') }}" class="px-3 py-1.5 rounded-md text-xs font-bold transition-all {{ App::getLocale() === 'gu' ? 'bg-[#00bde0] text-white shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">ગુજ</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    @if($registrationClosed)
    <!-- Registration closed for the current semester: details are captured as leads -->
    <div class="bg-amber-50 border-b border-amber-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-center">
            <p class="text-sm font-bold text-amber-900">{{ __('registration_closed_banner') }}</p>
            @if($nextBatchNote)
                <p class="text-xs font-medium text-amber-800 mt-1 whitespace-pre-line">{{ $nextBatchNote }}</p>
            @endif
        </div>
    </div>
    @endif

    <!-- Progress Bar Section -->
    <div class="bg-white border-b border-gray-100 py-6 overflow-x-auto custom-scrollbar-hide">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between min-w-[800px] lg:min-w-0">
                <template x-for="(title, index) in stepTitles" :key="index">
                    <div class="flex flex-col items-center relative flex-1 group cursor-pointer" @click="goToStep(index + 1)">
                        <!-- Connecting Line -->
                        <div x-show="index < maxStep - 1" 
                            class="absolute top-5 left-[60%] right-[-40%] h-[3px] transition-all duration-500"
                            :class="step > index + 1 ? 'bg-[#00bde0]' : 'bg-gray-100'">
                        </div>

                        <!-- Step Circle -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 relative z-10"
                            :class="step === index + 1 ? 'border-[#00bde0] bg-white text-[#00bde0] shadow-lg shadow-[#00bde0]/20 scale-110' : (step > index + 1 ? 'border-[#00bde0] bg-[#00bde0] text-white' : 'border-gray-200 bg-white text-gray-400')">
                            <!-- Icon Placeholder -->
                            <div x-html="stepIcons[index]" class="w-5 h-5"></div>
                            
                            <!-- Success Checkmark Overlay -->
                            <div x-show="step > index + 1" class="absolute -right-1 -bottom-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>

                        <!-- Step Title -->
                        <span class="mt-3 text-[10px] font-black uppercase tracking-widest transition-colors duration-300 text-center px-2"
                            :class="step === index + 1 ? 'text-[#00bde0]' : (step > index + 1 ? 'text-gray-900' : 'text-gray-400')"
                            x-text="title"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow py-8 px-4 sm:px-6 lg:px-8">
        @if(session('error'))
            <div class="max-w-4xl mx-auto mb-6">
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm flex items-start gap-3">
                    <div class="flex-shrink-0 text-red-500">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-red-800">{{ __('Notice') }}</h3>
                        <p class="text-sm text-red-700 mt-1">{{ __(session('error')) }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Success/Confirmation Screen -->
        <div x-show="isSubmitted" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="max-w-4xl mx-auto py-12 px-4">
            <div class="text-center mb-12">
                <div class="mb-8 relative inline-block">
                    <div class="w-24 h-24 rounded-full bg-green-100 flex items-center justify-center mx-auto relative z-10">
                        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="absolute inset-0 w-24 h-24 bg-green-500/20 rounded-full animate-ping mx-auto"></div>
                </div>
                
                <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tight mb-4">{{ __('Details Confirmation') }}</h1>
                <p class="text-lg text-gray-600 font-bold mb-8 leading-relaxed">
                    {{ __('Please review your details below before proceeding to plan selection.') }}
                </p>
            </div>

            <!-- Review Details Grid -->
            <div class="bg-white rounded-3xl border border-gray-200 shadow-xl overflow-hidden mb-12">
                <div class="p-8 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">{{ __('Student Information') }}</h3>
                    <button @click="isSubmitted = false; step = 2" class="text-[#00bde0] font-black uppercase tracking-widest text-[10px] hover:underline">{{ __('Edit Details') }}</button>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('Full Name') }}</p>
                        <p class="text-base font-bold text-gray-900" x-text="formData.first_name + ' ' + formData.middle_name + ' ' + formData.surname"></p>
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('Email Address') }}</p>
                        <p class="text-base font-bold text-gray-900" x-text="formData.email"></p>
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('Contact Number') }}</p>
                        <p class="text-base font-bold text-gray-900">
                            <x-phone-link alpineNumber="formData.primary_contact" class="text-gray-900 hover:text-[#00bde0] transition-colors" />
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('WhatsApp Number') }}</p>
                        <p class="text-base font-bold text-gray-900">
                            <x-phone-link alpineNumber="formData.whatsapp_contact" class="text-gray-900 hover:text-[#00bde0] transition-colors" />
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('Date of Birth') }}</p>
                        <p class="text-base font-bold text-gray-900" x-text="formatDobDisplay(formData.dob)"></p>
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('Gender') }}</p>
                        <p class="text-base font-bold text-gray-900" x-text="formData.gender"></p>
                    </div>
                </div>
                
                <!-- Guardian Details -->
                <div class="p-8 bg-gray-50/50 border-t border-gray-200">
                    <h4 class="text-sm font-black text-gray-500 uppercase tracking-widest mb-4">{{ __('Guardian Details') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div>
                            <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('Guardian Name') }}</p>
                            <p class="text-base font-bold text-gray-900" x-text="formData.guardian_full_name"></p>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('Contact') }}</p>
                            <p class="text-base font-bold text-gray-900">
                                <x-phone-link alpineNumber="formData.guardian_contact" class="text-gray-900 hover:text-[#00bde0] transition-colors" />
                            </p>
                        </div>
                        <div class="lg:col-span-2">
                            <p class="text-sm font-black text-gray-500 uppercase tracking-widest mb-1">{{ __('Currier Address') }}</p>
                            <p class="text-base font-bold text-gray-900" x-text="formData.address + ', ' + formData.city_village + ' - ' + formData.pincode"></p>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Email Verification Section (Inline inside Details Confirmation) -->
            <div x-show="showOtpSection" x-transition class="mt-8 bg-white rounded-3xl border border-gray-200 shadow-xl overflow-hidden mb-8">
                <div class="p-8 bg-[#00bde0]/5 border-b border-[#00bde0]/10 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-left">
                        <h3 class="text-xl font-black text-gray-900 uppercase tracking-tight">{{ __('Verify Your Email') }}</h3>
                        <p class="text-sm text-gray-500 font-bold mt-1" x-text="otpMessage || '{{ __('Please enter the OTP sent to your registered email to complete your registration.') }}'"></p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="requestPreRegistrationOtp(true)" :disabled="timer > 0 || isSubmitting" class="py-2.5 px-4 bg-white border border-gray-200 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18"></path></svg>
                            <span x-show="timer === 0">{{ __('Resend OTP') }}</span>
                            <span x-show="timer > 0" x-text="'Resend in ' + timer + 's'"></span>
                        </button>
                    </div>
                </div>
                
                <div class="p-8 max-w-xl mx-auto text-center space-y-6">
                    <div class="flex flex-col sm:flex-row gap-4 items-center justify-center">
                        <div class="w-full sm:w-64">
                            <input type="text" x-model="otp" maxlength="6" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] text-center text-2xl tracking-widest font-mono font-black uppercase text-gray-900 outline-none transition-all" placeholder="------">
                        </div>
                        
                        <button type="button" @click="submitForm" :disabled="otp.length !== 6 || otpVerifying" class="w-full sm:w-auto py-3 px-8 bg-[#00bde0] text-white rounded-xl font-black uppercase tracking-widest text-xs hover:bg-[#00a8c9] transition-all disabled:opacity-50 flex items-center justify-center gap-2 shadow-xl shadow-[#00bde0]/20">
                            <span x-show="!otpVerifying">{{ __('Confirm & Complete') }}</span>
                            <svg x-show="otpVerifying" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>

                    <div x-show="otpError" class="p-3 bg-red-50 text-red-600 border border-red-200 rounded-xl text-sm font-bold text-center animate-shake">
                        <span x-text="otpMessage || 'Invalid OTP entered'"></span>
                    </div>
                </div>
            </div>

            <div class="mt-12 flex justify-between items-center pt-8 border-t border-gray-200">
                <button @click="isSubmitted = false; step = 6" class="px-10 py-4 bg-white border-2 border-gray-200 text-gray-700 rounded-2xl font-black uppercase tracking-widest text-xs hover:bg-gray-50 hover:border-gray-900 transition-all">
                    {{ __('Edit Application') }}
                </button>
            </div>
        </div>
        

        <div x-show="!isSubmitted" class="max-w-7xl mx-auto lg:grid lg:grid-cols-12 lg:gap-8">
            <!-- Sidebar Navigation (Desktop) - Optional/Secondary -->
            <div class="hidden lg:block lg:col-span-3">
                <nav class="space-y-1 sticky top-28">
                    <template x-for="(title, index) in stepTitles" :key="index">
                        <div class="flex items-center group cursor-pointer p-3 rounded-xl transition-all"
                            @click="goToStep(index + 1)"
                            :class="step === (index + 1) ? 'bg-white shadow-sm border border-gray-200' : 'hover:bg-gray-100'">
                            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full border-2 transition-all duration-200"
                                :class="step === (index + 1) ? 'border-[#00bde0] bg-[#00bde0] text-white shadow-md shadow-[#00bde0]/20' : (step > (index + 1) ? 'border-[#00bde0] bg-[#00bde0]/10 text-[#00bde0]' : 'border-gray-300 text-gray-400')">
                                <div x-html="stepIcons[index]" class="w-4 h-4" x-show="step <= index + 1"></div>
                                <svg x-show="step > index + 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-3 overflow-hidden">
                                <p class="text-sm font-bold truncate transition-colors duration-200"
                                    :class="step === (index + 1) ? 'text-gray-900' : (step > (index + 1) ? 'text-gray-700' : 'text-gray-400')"
                                    x-text="title"></p>
                                <p class="text-[10px] uppercase tracking-wider font-semibold"
                                    :class="step === (index + 1) ? 'text-[#00bde0]' : 'text-transparent'">{{ __('Current Step') }}</p>
                            </div>
                        </div>
                    </template>
                </nav>
            </div>

            <!-- Form Content -->
            <div class="lg:col-span-9">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Instruction Header with Video Placeholder -->
                    <div class="p-6 md:p-8 bg-gray-50 border-b border-gray-200" x-show="step > 1 && step !== 5">
                        <div class="flex flex-col md:flex-row gap-6 items-start">
                            <div class="flex-grow">
                                <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight" x-text="stepTitle"></h2>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 md:p-8">
                        <!-- Global Errors Summary -->
                        <template x-if="Object.keys(errors).length > 0">
                            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl shadow-sm animate-shake">
                                <div class="flex items-center mb-2">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="font-black uppercase tracking-widest text-sm">{{ __('Validation Failed') }}</p>
                                </div>
                                <ul class="list-disc list-inside text-xs font-bold space-y-1 ml-7">
                                    <template x-for="(errorMessages, field) in errors" :key="field">
                                        <template x-for="msg in errorMessages" :key="msg">
                                            <li x-text="formatFieldName(field) + ': ' + msg"></li>
                                        </template>
                                    </template>
                                </ul>
                            </div>
                        </template>

                        <form @submit.prevent="submitForm">
                            @csrf

                            <!-- Step 1: Welcome / School Landing Screen -->
                            <div x-show="step === 1" class="py-12 px-4 max-w-2xl mx-auto text-center">
                                @if(session('school_registration_token'))
                                    <!-- School Specific Branding -->
                                    <div class="mb-8">
                                        @if(session('school_registration_logo_url'))
                                            <div class="inline-block p-4 bg-white rounded-2xl border border-gray-100 shadow-md mb-6">
                                                <img src="{{ asset('storage/' . session('school_registration_logo_url')) }}" alt="{{ session('school_registration_school_name') }}" class="h-20 w-auto object-contain mx-auto">
                                            </div>
                                        @else
                                            <div class="w-20 h-20 bg-[#00bde0]/10 rounded-full flex items-center justify-center mx-auto mb-6">
                                                <svg class="w-10 h-10 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                        @endif
                                        
                                        <h2 class="text-3xl font-black text-gray-900 mb-2 uppercase tracking-tight">
                                             {{ session('school_registration_form_name') ?: __('School Registration') }}
                                        </h2>
                                        <p class="text-sm font-bold text-gray-500 uppercase tracking-widest mb-6">
                                            {{ __('Registration Form for') }} <span class="text-[#00bde0]">{{ session('school_registration_school_name') }}</span>
                                        </p>

                                        @if(session('school_registration_notes'))
                                            <div class="mb-8 p-6 bg-[#00bde0]/5 border border-[#00bde0]/20 rounded-2xl text-left shadow-sm">
                                                <h4 class="text-xs font-black text-[#00bde0] uppercase tracking-widest mb-2 flex items-center gap-1.5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ __('Important Notes from School') }}
                                                </h4>
                                                <p class="text-sm font-medium text-gray-700 whitespace-pre-line leading-relaxed">
                                                    {{ session('school_registration_notes') }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <!-- Generic Branding -->
                                    <div class="w-20 h-20 bg-[#00bde0]/10 rounded-full flex items-center justify-center mx-auto mb-6">
                                        <svg class="w-10 h-10 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-black text-gray-900 mb-2 uppercase tracking-tight">{{ __('Welcome to PRAMUKH SANKALP') }}</h3>
                                    <p class="text-gray-500 mb-8">{{ __('Please read and accept the instructions to start') }}</p>
                                @endif

                                <button type="button" @click="showConsentModal = true" class="px-10 py-4 bg-[#00bde0] text-white rounded-xl font-black uppercase tracking-widest text-xs hover:bg-[#0099b8] transition-all shadow-lg shadow-[#00bde0]/20 hover:shadow-xl hover:shadow-[#00bde0]/30 hover:-translate-y-0.5 active:translate-y-0">
                                    {{ __('Review Instructions & Start') }}
                                </button>
                            </div>

                             <!-- Step 2: Personal Information -->
                             <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
                                 <div class="mb-8 p-4 bg-[#00bde0]/5 border border-[#00bde0]/20 rounded-2xl flex items-start gap-3">
                                     <div class="w-8 h-8 rounded-full bg-[#00bde0] text-white flex items-center justify-center flex-shrink-0">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                     </div>
                                     <p class="text-lg font-bold text-gray-700 leading-relaxed">{{ __('Instruction_Step2') }}</p>
                                 </div>
                                 <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                     <div>
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('First Name') }} <span class="text-red-500">*</span></label>
                                         <input type="text" x-model="formData.first_name" id="first_name" placeholder="{{ __('Student First Name') }}"
                                             @input="convertToUpper('first_name'); validateField('first_name')"
                                             @blur="validateField('first_name')"
                                             class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                             :class="{'border-red-500': errors.first_name}">
                                         <template x-if="errors.first_name">
                                             <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.first_name[0]"></p>
                                         </template>
                                     </div>
                                     <div>
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Middle Name') }} <span class="text-red-500">*</span></label>
                                         <input type="text" x-model="formData.middle_name" id="middle_name" placeholder="{{ __('Student Middle Name') }}"
                                             @input="convertToUpper('middle_name'); validateField('middle_name')"
                                             @blur="validateField('middle_name')"
                                             class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                             :class="{'border-red-500': errors.middle_name}">
                                         <template x-if="errors.middle_name">
                                             <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.middle_name[0]"></p>
                                         </template>
                                     </div>
                                     <div class="col-span-1 md:col-span-2">
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Surname/Last Name') }} <span class="text-red-500">*</span></label>
                                         <input type="text" x-model="formData.surname" id="surname" placeholder="{{ __('Family Name') }}"
                                             @input="convertToUpper('surname'); validateField('surname')"
                                             @blur="validateField('surname')"
                                             class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                             :class="{'border-red-500': errors.surname}">
                                         <template x-if="errors.surname">
                                             <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.surname[0]"></p>
                                         </template>
                                     </div>
                                     <div class="col-span-1 md:col-span-2">
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-3">{{ __('Gender') }} <span class="text-red-500">*</span></label>
                                         <div class="flex flex-wrap gap-4">
                                             <template x-for="g in ['Male', 'Female', 'Other']" :key="g">
                                                 <label class="relative flex-1 min-w-[100px]">
                                                     <input type="radio" x-model="formData.gender" :value="g" class="peer sr-only">
                                                     <div class="px-4 py-3 border-2 border-gray-200 rounded-xl text-center bg-gray-50 cursor-pointer peer-checked:border-[#00bde0] peer-checked:bg-[#00bde0]/5 peer-checked:text-[#00bde0] transition-all hover:bg-white">
                                                         <span class="text-sm font-bold" x-text="g === 'Male' ? '{{ __('Male') }}' : (g === 'Female' ? '{{ __('Female') }}' : '{{ __('Other') }}')"></span>
                                                     </div>
                                                 </label>
                                             </template>
                                         </div>
                                     </div>
                                     <div>
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Date of Birth') }} <span class="text-red-500">*</span></label>
                                         @php
                                             $maxDate = now()->subYears(10)->format('Y-m-d');
                                             $minDate = now()->subYears(18)->format('Y-m-d');
                                         @endphp
                                         <input type="text" x-model="formData.dob" id="dob" placeholder="DD/MM/YYYY"
                                             @change="validateField('dob')"
                                             class="date-picker w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                             :class="{'border-red-500': errors.dob}">
                                         <template x-if="errors.dob">
                                             <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.dob[0]"></p>
                                         </template>
                                     </div>
                                 </div>
                             </div>

                            <!-- Step 3: Educational Details -->
                            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                                {{-- <div class="mb-8 p-4 bg-[#00bde0]/5 border border-[#00bde0]/20 rounded-2xl flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#00bde0] text-white flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <p class="text-lg font-bold text-gray-700 leading-relaxed">{{ __('Instruction_Step3') }}</p>
                                </div> --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                    <div>
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Education Level') }} <span class="text-red-500">*</span></label>
                                        <select x-model="formData.education_level" id="education_level"
                                            @change="validateField('education_level'); if(![11, 12, '11', '12'].includes(formData.education_level)) { formData.stream = ''; delete errors.stream; }"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                            :class="{'border-red-500': errors.education_level}">
                                            <option value="">{{ __('Select Standard') }}</option>
                                            @foreach($standards as $std)
                                                <option value="{{ $std->std_id }}">{{ $std->stdname }}</option>
                                            @endforeach
                                        </select>
                                        <template x-if="errors.education_level">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.education_level[0]"></p>
                                        </template>
                                    </div>

                                    <div class="col-span-1 md:col-span-2" x-show="[11, 12, '11', '12'].includes(formData.education_level)" x-transition style="display: none;">
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-3">{{ __('Stream') }} <span class="text-red-500">*</span></label>
                                        <div class="flex flex-wrap gap-4">
                                            <template x-for="s in ['Arts', 'Commerce', 'Science']" :key="s">
                                                <label class="relative flex-1 min-w-[100px]">
                                                    <input type="radio" x-model="formData.stream" :value="s" class="peer sr-only" @change="validateField('stream')">
                                                    <div class="px-4 py-3 border-2 border-gray-200 rounded-xl text-center bg-gray-50 cursor-pointer peer-checked:border-[#00bde0] peer-checked:bg-[#00bde0]/5 peer-checked:text-[#00bde0] transition-all hover:bg-white h-full flex items-center justify-center">
                                                        <span class="text-sm font-bold" x-text="s"></span>
                                                    </div>
                                                </label>
                                            </template>
                                        </div>
                                        <template x-if="errors.stream">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.stream[0]"></p>
                                        </template>
                                    </div>

                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-3">{{ __("Student's Current Educational Board") }} <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                            @foreach($boards as $board)
                                                <label class="relative">
                                                    <input type="radio" x-model="formData.board" value="{{ $board->eduboard_id }}" 
                                                        @change="validateField('board')"
                                                        class="peer sr-only">
                                                    <div class="px-4 py-3 border-2 border-gray-200 rounded-xl text-center bg-gray-50 cursor-pointer peer-checked:border-[#00bde0] peer-checked:bg-[#00bde0]/5 peer-checked:text-[#00bde0] transition-all hover:bg-white h-full flex items-center justify-center"
                                                        :class="{'border-red-500': errors.board}">
                                                        <span class="text-xs font-bold">{{ $board->boardname }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        <template x-if="errors.board">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.board[0]"></p>
                                        </template>
                                    </div>

                                    <!-- Conditional Input for Other Education Board -->
                                    <div class="col-span-1 md:col-span-2" x-show="formData.board == otherBoardId" x-transition style="display: none;">
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Specify Other Educational Board') }} <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="formData.other_eduboard_name" id="other_eduboard_name" placeholder="{{ __('Enter Educational Board Name') }}"
                                            @input="validateField('other_eduboard_name')"
                                            @blur="validateField('other_eduboard_name')"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                            :class="{'border-red-500': errors.other_eduboard_name}">
                                        <template x-if="errors.other_eduboard_name">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.other_eduboard_name[0]"></p>
                                        </template>
                                    </div>

                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-3">{{ __('Medium of Education') }} <span class="text-red-500">*</span></label>
                                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                            @foreach($mediums as $medium)
                                                <label class="relative">
                                                    <input type="radio" x-model="formData.medium" value="{{ $medium->medium_id }}" 
                                                        @change="validateField('medium')"
                                                        class="peer sr-only">
                                                    <div class="px-4 py-3 border-2 border-gray-200 rounded-xl text-center bg-gray-50 cursor-pointer peer-checked:border-[#00bde0] peer-checked:bg-[#00bde0]/5 peer-checked:text-[#00bde0] transition-all hover:bg-white h-full flex items-center justify-center"
                                                        :class="{'border-red-500': errors.medium}">
                                                        <span class="text-xs font-bold">{{ $medium->mediumname }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        <template x-if="errors.medium">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.medium[0]"></p>
                                        </template>
                                    </div>

                                    <div class="col-span-1 md:col-span-2 relative">
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('School / Institute Name') }} <span class="text-red-500">*</span></label>
                                        @if(session('school_registration_school_name'))
                                            <input type="text" x-model="formData.school_name" id="school_name" readonly disabled
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none transition font-bold text-gray-700 bg-gray-100 cursor-not-allowed">
                                        @else
                                            <input type="text" x-model="formData.school_name" id="school_name" placeholder="{{ __('Enter Full School Name') }}"
                                                @input="validateField('school_name'); fetchSuggestions();"
                                                @blur="setTimeout(() => { showSuggestions = false; checkSchoolConnection(formData.school_name); if (!showSchoolWarningModal) validateField('school_name'); }, 300)"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                                :class="{'border-red-500': errors.school_name}">
                                        @endif
                                        
                                        <!-- Suggestions Dropdown -->
                                        <div x-show="showSuggestions && suggestions.length > 0" class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 overflow-hidden max-h-60 overflow-y-auto" style="display: none;">
                                            <template x-for="item in suggestions" :key="item.school_id">
                                                <div @click="selectSuggestion(item)" class="px-4 py-3 hover:bg-gray-100 cursor-pointer font-bold text-sm text-gray-700 flex justify-between items-center">
                                                    <span x-text="item.schoolname"></span>
                                                    <template x-if="item.status == 1">
                                                        <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 bg-red-100 text-red-700 rounded-md">{{ __('Connected') }}</span>
                                                    </template>
                                                </div>
                                            </template>
                                        </div>

                                        <template x-if="errors.school_name">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.school_name[0]"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 4: Satsang Related Details -->
                            <div x-show="step === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                                <div class="mb-8 p-4 bg-[#00bde0]/5 border border-[#00bde0]/20 rounded-2xl flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#00bde0] text-white flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <p class="text-lg font-bold text-gray-700 leading-relaxed">{{ __('Instruction_Step4') }}</p>
                                </div>
                                <div class="bg-gray-50 p-8 rounded-3xl border-2 border-dashed border-gray-200">
                                    <label class="block text-lg font-black text-gray-900 mb-6 text-center">{{ __('Is the student attending the Satsang Sabha in the BAPS organization?') }} <span class="text-red-500">*</span></label>
                                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                        <label class="flex-1 max-w-xs relative">
                                            <input type="radio" x-model="formData.attending_sabha" value="Yes" 
                                                @change="validateField('attending_sabha')"
                                                class="peer sr-only">
                                            <div class="p-4 border-2 border-gray-200 rounded-2xl text-center bg-white cursor-pointer peer-checked:border-[#00bde0] peer-checked:bg-[#00bde0]/5 transition-all"
                                                :class="{'border-red-500': errors.attending_sabha}">
                                                <span class="block text-xl font-black text-gray-400 peer-checked:text-[#00bde0]">{{ __('Yes') }}</span>
                                                <span class="block text-[10px] font-bold text-gray-400 peer-checked:text-[#00bde0]/60 uppercase mt-1">{{ __('Attending Sabha') }}</span>
                                            </div>
                                        </label>
                                        <label class="flex-1 max-w-xs relative">
                                            <input type="radio" x-model="formData.attending_sabha" value="No" 
                                                @change="validateField('attending_sabha')"
                                                class="peer sr-only">
                                            <div class="p-4 border-2 border-gray-200 rounded-2xl text-center bg-white cursor-pointer peer-checked:border-[#00bde0] peer-checked:bg-[#00bde0]/5 transition-all"
                                                :class="{'border-red-500': errors.attending_sabha}">
                                                <span class="block text-xl font-black text-gray-400 peer-checked:text-[#00bde0]">{{ __('No') }}</span>
                                                <span class="block text-[10px] font-bold text-gray-400 peer-checked:text-[#00bde0]/60 uppercase mt-1">{{ __('Not Attending') }}</span>
                                            </div>
                                        </label>
                                    </div>
                                    <template x-if="errors.attending_sabha">
                                        <p class="text-red-500 text-[10px] font-bold mt-1 text-center" x-text="errors.attending_sabha[0]"></p>
                                    </template>
                                </div>

                                <!-- Conditional Fields for 'Yes' -->
                                <div x-show="formData.attending_sabha === 'Yes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="mt-8 space-y-6">
                                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-xl">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-blue-700 font-bold">
                                                    {{ __('Note: These details will be verified by a Karyakar.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                        <div>
                                            <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Satsang Mandal') }} <span class="text-red-500">*</span></label>
                                            <select x-model="formData.satsang_mandal" id="satsang_mandal"
                                                @change="validateField('satsang_mandal')"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                                :class="{'border-red-500': errors.satsang_mandal}">
                                                <option value="">{{ __('Select Mandal') }}</option>
                                                @foreach($mandals as $mandal)
                                                    <option value="{{ $mandal->mandalname }}">{{ $mandal->mandalname }}</option>
                                                @endforeach
                                            </select>
                                            <template x-if="errors.satsang_mandal">
                                                <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.satsang_mandal[0]"></p>
                                            </template>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Kshetra Name') }} <span class="text-red-500">*</span></label>
                                            <p class="text-[15px] md:text-[15px] text-gray-400 mb-1 font-bold">{{ __('Know the Kshetra Name from Karyakar.') }}</p>
                                            <input type="text" x-model="formData.kshetra_name" id="kshetra_name" placeholder="{{ __('Kshetra Name') }}"
                                                @input="validateField('kshetra_name')"
                                                @blur="validateField('kshetra_name')"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                                :class="{'border-red-500': errors.kshetra_name}">
                                            <template x-if="errors.kshetra_name">
                                                <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.kshetra_name[0]"></p>
                                            </template>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Shikharabaddha Mandir') }} <span class="text-red-500">*</span></label>
                                            <select x-model="formData.shikharabaddha_mandir" id="shikharabaddha_mandir"
                                                @change="validateField('shikharabaddha_mandir')"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                                :class="{'border-red-500': errors.shikharabaddha_mandir}">
                                                <option value="">{{ __('Select Mandir') }}</option>
                                                @foreach($mandirs as $mandir)
                                                    <option value="{{ $mandir->mname }}">{{ $mandir->mname }}</option>
                                                @endforeach
                                            </select>
                                            <template x-if="errors.shikharabaddha_mandir">
                                                <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.shikharabaddha_mandir[0]"></p>
                                            </template>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Mandal Sanchalak / Karyakar Name') }} <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="formData.mandal_sanchalak_name" id="mandal_sanchalak_name" placeholder="{{ __('Mandal Sanchalak / Karyakar Name') }}"
                                                @input="validateField('mandal_sanchalak_name')"
                                                @blur="validateField('mandal_sanchalak_name')"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                                :class="{'border-red-500': errors.mandal_sanchalak_name}">
                                            <template x-if="errors.mandal_sanchalak_name">
                                                <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.mandal_sanchalak_name[0]"></p>
                                            </template>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Mandal Sanchalak / Karyakar Contact No.') }} <span class="text-red-500">*</span></label>
                                            <input type="text" x-model="formData.mandal_sanchalak_contact" id="mandal_sanchalak_contact" placeholder="{{ __('Mandal Sanchalak / Karyakar Contact No.') }}"
                                                @input="validatePhone('mandal_sanchalak_contact'); validateField('mandal_sanchalak_contact')"
                                                @blur="validateField('mandal_sanchalak_contact')"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                                :class="{'border-red-500': errors.mandal_sanchalak_contact}">
                                            <template x-if="errors.mandal_sanchalak_contact">
                                                <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.mandal_sanchalak_contact[0]"></p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                             <!-- Step 5: SANKALP Guardian Information -->
                             <div x-show="step === 5" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                                 <!-- Section Header -->
                                 <div class="text-center mb-7">
                                     <h2 class="text-2xl font-black text-gray-900 uppercase tracking-tight mb-2">{{ __('SANKALP Guardian Related Information') }}</h2>
                                     {{-- <p class="text-gray-500 font-bold text-base">{{ __('સંકલ્પ ગાર્ડિયન વિષયક માહિતી') }}</p> --}}
                                 </div>
 
                                 <!-- PDF Guide Link Styled Prominently -->
                                 <div class="mb-10 w-full max-w-4xl mx-auto text-center">
                                     @php
                                         $guardianPdfPage = (app()->getLocale() == 'en') ? 1 : 2;
                                     @endphp
                                     <button type="button" @click.prevent="window.open('https://admissions.pramukh.academy/files/Submitted-What-is-a-Sankalp-Guardian.pdf#page={{ $guardianPdfPage }}', 'SankalpGuardian', 'width=800,height=800,scrollbars=yes')" 
                                        class="inline-flex items-center justify-center gap-3 px-8 py-5 bg-yellow-500/10 hover:bg-yellow-500/20 text-yellow-600 border-2 border-dashed border-yellow-500/30 rounded-2xl text-lg font-black uppercase tracking-wider transition-all w-full md:w-auto cursor-pointer">
                                         <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                         {{ __('What is SANKALP Guardian ? Click Here') }}
                                     </button>
                                 </div>
 
                                 <div class="mb-8 p-4 bg-[#00bde0]/5 border border-[#00bde0]/20 rounded-2xl flex items-start gap-3">
                                     <div class="w-8 h-8 rounded-full bg-[#00bde0] text-white flex items-center justify-center flex-shrink-0">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                     </div>
                                     <p class="text-lg font-bold text-gray-700 leading-relaxed">{{ __('Instruction_Step5') }}</p>
                                 </div>
                                 <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 mb-12">
                                     <div class="col-span-1 md:col-span-2">
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __("SANKALP Guardian's Full Name") }} <span class="text-red-500">*</span></label>
                                         <!-- <p class="text-[15px] text-gray-400 mb-2 font-bold leading-tight">{{ __('Guardian_Name_Note') }}</p> -->
                                         <input type="text" x-model="formData.guardian_full_name" id="guardian_full_name" placeholder="{{ __('e.g. FIRSTNAME MIDDLENAME LASTNAME') }}"
                                             @input="convertToUpper('guardian_full_name'); splitGuardianName(); validateField('guardian_full_name')"
                                             @blur="validateField('guardian_full_name')"
                                             class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                             :class="{'border-red-500': errors.guardian_full_name}">
                                         <template x-if="errors.guardian_full_name">
                                             <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.guardian_full_name[0]"></p>
                                         </template>
                                     </div>
                                     <div class="col-span-1 md:col-span-2">
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-3">{{ __('Guardians Gender') }} <span class="text-red-500">*</span></label>
                                         <div class="flex flex-wrap gap-4">
                                             <template x-for="g in ['Male', 'Female', 'Other']" :key="g">
                                                 <label class="relative flex-1 min-w-[100px]">
                                                     <input type="radio" x-model="formData.guardian_gender" :value="g" class="peer sr-only">
                                                     <div class="px-4 py-3 border-2 border-gray-200 rounded-xl text-center bg-gray-50 cursor-pointer peer-checked:border-[#00bde0] peer-checked:bg-[#00bde0]/5 peer-checked:text-[#00bde0] transition-all hover:bg-white h-full flex items-center justify-center">
                                                         <span class="text-sm font-bold" x-text="g === 'Male' ? '{{ __('Male') }}' : (g === 'Female' ? '{{ __('Female') }}' : '{{ __('Other') }}')"></span>
                                                     </div>
                                                 </label>
                                             </template>
                                         </div>
                                     </div>
                                     <div class="col-span-1 md:col-span-2">
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Email') }} <span class="text-red-500">*</span></label>
                                         <p class="text-[15px] md:text-[15px] text-gray-400 mb-2 font-bold leading-tight">{{ __('Email_Note') }}</p>
                                         <input type="email" x-model="formData.email" id="email" placeholder="{{ __('example@email.com') }}"
                                             @input="validateField('email')"
                                             @blur="validateField('email')"
                                             class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                             :class="{'border-red-500': errors.email}">
                                         <template x-if="errors.email">
                                             <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.email[0]"></p>
                                         </template>
                                         <p class="text-[12px] text-[#00bde0] mt-1.5 font-bold uppercase tracking-wider">{{ __('This Email Will Be Verified Through OTP.') }}</p>
                                     </div>
                                     <div class="col-span-1 md:col-span-2">
                                         <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Primary Contact Number') }} <span class="text-red-500">*</span></label>
                                         <input type="text" x-model="formData.primary_contact" id="primary_contact" placeholder="{{ __('10 Digit Mobile No') }}"
                                             @input="formData.primary_contact = formData.primary_contact.replace(/[^0-9]/g, '').replace(/^[^6-9]+/, ''); validatePhone('primary_contact'); validateField('primary_contact'); if(formData.whatsapp_same_as_primary) formData.whatsapp_contact = formData.primary_contact"
                                             @blur="validateField('primary_contact')"
                                             class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                             :class="{'border-red-500': errors.primary_contact}">
                                         <template x-if="errors.primary_contact">
                                             <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.primary_contact[0]"></p>
                                         </template>
                                         
                                         <!-- Important Contact Sub-note styled prominent/big -->
                                         <div class="mt-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                                             <p class="text-sm text-amber-800 font-bold leading-relaxed">
                                                 ⚠️ {{ __('Guardian_Contact_Note') }}
                                             </p>
                                         </div>
                                     </div>
                                     <div class="col-span-1 md:col-span-2">
                                         <div class="flex items-center justify-between mb-1.5">
                                             <label class="block text-sm font-black text-gray-500 uppercase tracking-widest">{{ __('Primary WhatsApp Number') }} <span class="text-red-500">*</span></label>
                                             <label class="flex items-center gap-2 cursor-pointer group">
                                                 <input type="checkbox" x-model="formData.whatsapp_same_as_primary" 
                                                     @change="if($el.checked) formData.whatsapp_contact = formData.primary_contact"
                                                     class="w-4 h-4 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded">
                                                 <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest group-hover:text-[#00bde0] transition-colors">{{ __('Same as Contact') }}</span>
                                             </label>
                                         </div>
                                         <input type="text" x-model="formData.whatsapp_contact" id="whatsapp_contact" placeholder="{{ __('For important updates & notifications') }}"
                                             :disabled="formData.whatsapp_same_as_primary"
                                             @input="validatePhone('whatsapp_contact'); validateField('whatsapp_contact')"
                                             @blur="validateField('whatsapp_contact')"
                                             class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 disabled:bg-gray-100 disabled:text-gray-500 bg-gray-50 focus:bg-white"
                                             :class="{'border-red-500': errors.whatsapp_contact}">
                                         <template x-if="errors.whatsapp_contact">
                                             <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.whatsapp_contact[0]"></p>
                                         </template>
                                     </div>
                                 </div>
                             </div>

                            <!-- Step 6: Address Details -->
                            <div x-show="step === 6" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
                                <!-- Study Material Preference (Moved from Step 5) -->
                                <div class="bg-white p-6 rounded-2xl border-2 border-[#00bde0]/20 mb-8">
                                    <label class="block text-base font-black text-gray-900 mb-2 text-center">{{ __('Study Material Preference') }} <span class="text-red-500">*</span></label>
                                    <p class="text-[15px] md:text-[15px] text-gray-400 mb-4 text-center font-bold leading-tight">{{ __('Study_Material_Preference_Note') }}</p>
                                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                        @foreach($mediums->whereIn('mediumname', ['Gujarati', 'Hindi', 'English']) as $m)
                                            <label class="flex-1 max-w-xs relative">
                                                <input type="radio" x-model="formData.handout_language" value="{{ $m->medium_id }}" 
                                                    @change="validateField('handout_language')"
                                                    class="peer sr-only">
                                                <div class="p-4 border-2 border-gray-200 rounded-2xl text-center bg-white cursor-pointer peer-checked:border-[#00bde0] peer-checked:bg-[#00bde0]/5 transition-all"
                                                    :class="{'border-red-500': errors.handout_language}">
                                                    <span class="block text-lg font-black text-gray-400 peer-checked:text-[#00bde0]">{{ __($m->mediumname) }}</span>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    <template x-if="errors.handout_language">
                                        <p class="text-red-500 text-[10px] font-bold mt-1 text-center" x-text="errors.handout_language[0]"></p>
                                    </template>
                                </div>

                                <div class="mb-8 p-4 bg-[#00bde0]/5 border border-[#00bde0]/20 rounded-2xl flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#00bde0] text-white flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <p class="text-lg font-bold text-gray-700 leading-relaxed">{{ __('Instruction_Step6') }}</p>
                                        {{-- <p class="text-xs text-gray-400 font-bold leading-tight">{{ __('Postal_Courier_Address_Note') }}</p> --}}
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('PIN Code') }} <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <input type="text" x-model="formData.pincode" id="pincode" placeholder="{{ __('6 Digit PIN code') }}" maxlength="6"
                                                @input="validateZipcode('pincode'); validateField('pincode'); if(formData.pincode.length === 6) fetchAddressByPincode();"
                                                @blur="validateField('pincode')"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                                :class="{'border-red-500': errors.pincode}">
                                            <div x-show="isPincodeLoading" class="absolute right-3 top-3" style="display: none;">
                                                <svg class="animate-spin h-6 w-6 text-[#00bde0]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                            </div>
                                        </div>
                                        <template x-if="errors.pincode">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.pincode[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Country') }} <span class="text-red-500">*</span></label>
                                        <select x-model="formData.country" id="country" @change="loadStates(); validateField('country')"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                            :class="{'border-red-500': errors.country}">
                                            <option value="">{{ __('Select Country') }}</option>
                                            <template x-for="c in countries" :key="c.country_id">
                                                <option :value="c.country_id" x-text="c.cname"></option>
                                            </template>
                                        </select>
                                        <template x-if="errors.country">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.country[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('State') }} <span class="text-red-500">*</span></label>
                                        <select x-model="formData.state" id="state" @change="loadDistricts(); validateField('state')" :disabled="!formData.country"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white disabled:opacity-50"
                                            :class="{'border-red-500': errors.state}">
                                            <option value="">{{ __('Select State') }}</option>
                                            <template x-for="s in states" :key="s.state_id">
                                                <option :value="s.state_id" x-text="s.sname"></option>
                                            </template>
                                        </select>
                                        <template x-if="errors.state">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.state[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('District') }} <span class="text-red-500">*</span></label>
                                        <select x-model="formData.district" id="district" @change="loadTalukos(); validateField('district')" :disabled="!formData.state"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white disabled:opacity-50"
                                            :class="{'border-red-500': errors.district}">
                                            <option value="">{{ __('Select District') }}</option>
                                            <template x-for="d in districts" :key="d.dist_id">
                                                <option :value="d.dist_id" x-text="d.dname"></option>
                                            </template>
                                        </select>
                                        <template x-if="errors.district">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.district[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Taluka') }} <span class="text-red-500">*</span></label>
                                        <select x-model="formData.taluka" id="taluka" :disabled="!formData.district"
                                            @change="validateField('taluka')"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white disabled:opacity-50"
                                            :class="{'border-red-500': errors.taluka}">
                                            <option value="">{{ __('Select Taluka') }}</option>
                                            <template x-for="t in talukos" :key="t.takula_id">
                                                <option :value="t.takula_id" x-text="t.tname"></option>
                                            </template>
                                        </select>
                                        <template x-if="errors.taluka">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.taluka[0]"></p>
                                        </template>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('City / Village') }} <span class="text-red-500">*</span></label>
                                        <input type="text" x-model="formData.city_village" id="city_village" placeholder="{{ __('Enter City or Village Name') }}"
                                            @input="validateField('city_village')"
                                            @blur="validateField('city_village')"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white"
                                            :class="{'border-red-500': errors.city_village}">
                                        <template x-if="errors.city_village">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.city_village[0]"></p>
                                        </template>
                                    </div>
                                    <div class="col-span-1 md:col-span-2">
                                        <label class="block text-sm font-black text-gray-500 uppercase tracking-widest mb-1.5">{{ __('Courier Address') }} <span class="text-red-500">*</span></label>
                                        <textarea x-model="formData.address" id="address" placeholder="{{ __('Enter Full Courier Address (House No, Building, Street, Area)') }}" rows="3"
                                            @input="validateField('address')"
                                            @blur="validateField('address')"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-4 focus:ring-[#00bde0]/20 focus:border-[#00bde0] outline-none transition font-bold text-gray-900 bg-gray-50 focus:bg-white resize-none"
                                            :class="{'border-red-500': errors.address}"></textarea>
                                        <p class="text-[15px] md:text-[15px] text-gray-500 font-bold mt-1.5">{{ __('Courier_Address_Note') }}</p>
                                        <template x-if="errors.address">
                                            <p class="text-red-500 text-[10px] font-bold mt-1" x-text="errors.address[0]"></p>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Navigation Buttons (Bottom) -->
                            <div class="mt-12 flex justify-between items-center pt-8 border-t-2 border-gray-100" x-show="step > 1">
                                <button type="button" x-show="step > 2" @click="prevStep"
                                    class="group px-6 py-3 bg-white border-2 border-gray-200 rounded-2xl font-black text-gray-700 hover:border-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200 transition-all shadow-sm flex items-center gap-2">
                                    <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7"></path></svg>
                                    {{ __('Previous') }}
                                </button>
                                <div x-show="step === 2"></div>

                                <button type="button" x-show="step < maxStep" @click="nextStep"
                                    class="group px-10 py-3 bg-[#00bde0] border-2 border-transparent rounded-2xl font-black text-white hover:bg-[#0099b8] hover:shadow-xl hover:shadow-[#00bde0]/20 focus:outline-none focus:ring-4 focus:ring-[#00bde0]/30 transition-all shadow-lg flex items-center gap-2">
                                    {{ __('Next') }}
                                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                                </button>

                                <button type="button" x-show="step === maxStep" @click="registrationClosed ? submitLead() : requestPreRegistrationOtp(false)" :disabled="isSubmitting"
                                    class="px-12 py-3 bg-green-600 border-2 border-transparent rounded-2xl font-black text-white hover:bg-green-700 hover:shadow-xl hover:shadow-green-600/20 focus:outline-none focus:ring-4 focus:ring-green-500/30 transition-all shadow-lg flex items-center gap-2 disabled:opacity-50">
                                    <span x-show="!isSubmitting">{{ $registrationClosed ? __('Submit Details') : __('Submit Application') }}</span>
                                    <span x-show="isSubmitting">{{ $registrationClosed ? __('Saving...') : __('Sending OTP...') }}</span>
                                    <svg x-show="!isSubmitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    <svg x-show="isSubmitting" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        

    </main>

    <!-- Consent Modal Overlay -->
    <div x-show="showConsentModal" 
        class="fixed inset-0 z-[100] overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-4xl w-full overflow-hidden" 
                @click.away="if(formData.consent) showConsentModal = false">
                
                <div class="p-6 md:p-8 bg-gray-50 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center gap-4">
                        @if(session('school_registration_logo_url'))
                            <img src="{{ asset('storage/' . session('school_registration_logo_url')) }}" alt="{{ session('school_registration_school_name') }}" class="h-12 w-auto object-contain bg-white p-1 rounded-lg border border-gray-200">
                        @endif
                        <div>
                            <h2 class="text-3xl font-black text-gray-900 uppercase tracking-tight">
                                {{ session('school_registration_form_name') ?? __('Important Instructions') }}
                            </h2>
                            <p class="text-gray-500 font-medium">
                                @if(session('school_registration_school_name'))
                                    {{ __('Registration for') }} {{ session('school_registration_school_name') }}
                                @else
                                    {{ __('Please read carefully before proceeding') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Language Dropdown in Consent Modal -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="flex items-center gap-1.5 px-3 py-1.5 bg-white hover:bg-gray-100 rounded-lg text-xs font-bold text-gray-700 transition-all border border-gray-200 focus:outline-none shadow-sm">
                                <span>{{ App::getLocale() === 'en' ? 'English' : (App::getLocale() === 'gu' ? 'ગુજરાતી' : 'हिंदी') }}</span>
                                <svg class="w-3 h-3 text-gray-500 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1.5 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-50 py-1" style="display: none;" x-transition>
                                <a href="{{ route('language.switch', 'en') }}" class="block px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-100 {{ App::getLocale() === 'en' ? 'text-[#00bde0]' : '' }}">English</a>
                                <a href="{{ route('language.switch', 'gu') }}" class="block px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-100 {{ App::getLocale() === 'gu' ? 'text-[#00bde0]' : '' }}">ગુજરાતી</a>
                                <a href="{{ route('language.switch', 'hi') }}" class="block px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-100 {{ App::getLocale() === 'hi' ? 'text-[#00bde0]' : '' }}">हिंदी</a>
                            </div>
                        </div>
                        <button @click="showConsentModal = false" x-show="formData.consent" class="text-gray-400 hover:text-gray-900 transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 md:p-8 max-h-[60vh] overflow-y-auto custom-scrollbar shadow-inner bg-white">
                    <div class="prose prose-sm max-w-none text-gray-700">
                        @php
                            // Defensive: any other code path that renders this view.
                            $schoolCustomFees = $schoolCustomFees ?? [];

                            $feePlansHtml = '<ul class="list-none p-0 flex flex-wrap gap-4">';

                            if (!empty($schoolCustomFees)) {
                                // Registering through a school link: show that school's
                                // own fees, which is what the student will actually pay.
                                foreach($schoolCustomFees as $fee) {
                                    $feePlansHtml .= '<li class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex-1 min-w-[140px] text-center">';
                                    $feePlansHtml .= '<span class="block text-2xl font-black text-gray-900">₹' . number_format((float)($fee['amount'] ?? 0), 0) . '</span>';
                                    $feePlansHtml .= '<span class="text-[10px] font-bold text-[#00bde0] uppercase tracking-widest">' . e($fee['name'] ?? '') . '</span>';
                                    $feePlansHtml .= '</li>';
                                }
                                $feePlansHtml .= '</ul>';
                            } else {
                                foreach($feePlans as $plan) {
                                    $feePlansHtml .= '<li class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex-1 min-w-[140px] text-center">';
                                    if ($plan->strike_through_amount && $plan->strike_through_amount > 0) {
                                        $feePlansHtml .= '<span class="block text-sm font-bold text-gray-400 line-through">₹' . number_format($plan->strike_through_amount, 0) . '</span>';
                                    }
                                    $feePlansHtml .= '<span class="block text-2xl font-black text-gray-900">₹' . number_format($plan->amount, 0) . '</span>';
                                    $feePlansHtml .= '<span class="text-[10px] font-bold text-[#00bde0] uppercase tracking-widest">' . e(__($plan->fee_name)) . '</span>';
                                    $feePlansHtml .= '</li>';
                                }
                                if($feePlans->isEmpty()) {
                                    $feePlansHtml .= '<li class="bg-gray-50 p-4 rounded-2xl border border-gray-100 flex-1 min-w-[140px] text-center">';
                                    $feePlansHtml .= '<span class="block text-lg font-bold text-gray-400">' . __('No active plans available') . '</span>';
                                    $feePlansHtml .= '</li>';
                                }
                                $feePlansHtml .= '</ul>';
                            }

                            $templateContent = $agreementTemplate ? ($agreementTemplate->{'content_' . App::getLocale()} ?? $agreementTemplate->content_en) : null;
                            if ($templateContent) {
                                $templateContent = str_replace('{{FEE_PLANS}}', $feePlansHtml, $templateContent);
                            }
                        @endphp

                        @if(session('school_registration_notes'))
                            <div class="mb-6 p-5 bg-[#00bde0]/10 border border-[#00bde0]/20 rounded-2xl">
                                <h3 class="text-lg font-black text-gray-900 mb-2 uppercase tracking-tight">{{ __('Important Note from School') }}</h3>
                                <p class="text-sm font-medium text-gray-700 whitespace-pre-line">{{ session('school_registration_notes') }}</p>
                            </div>
                        @endif

                        @if($templateContent)
                            {!! $templateContent !!}
                        @else
                            <h3 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('What is the SANKALP Programme?') }}</h3>
                            <p>{{ __('SANKALP_Intro') }}</p>

                            <h3 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('1. Purpose of This Form') }}</h3>
                            <p>{{ __('Purpose_Desc') }}</p>

                            <h3 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('2. Voluntary Participation') }}</h3>
                            <p>{{ __('Voluntary_Desc') }}</p>
                            
                            <h3 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('3. Programme Orientation') }}</h3>
                            <p>{{ __('Orientation_Desc') }}</p>
                            
                            <h3 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('4. Programme Fee (Online Mode)') }}</h3>
                            {!! $feePlansHtml !!}
                            <p class="text-xs text-gray-500 italic mt-2">{{ __('Fee_Note') }}</p>
                            
                            <h3 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('5. Study Material & Access') }}</h3>
                            <p>{{ __('After successful enrolment:') }}</p>
                            <ul>
                                <li>{{ __('Access_Item1') }}</li>
                                <li>{{ __('Access_Item2') }}</li>
                            </ul>

                            <h3 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('6. Understanding Before Applying') }}</h3>
                            <p>{{ __('Understanding_Desc') }}</p>

                            <p>{{ __('Website_Note') }} <a href="https://pramukh.academy/pramukh-sankalp-programme/" class="text-[#00bde0] font-bold">https://pramukh.academy/pramukh-sankalp-programme/</a></p>

                            <h3 class="text-red-600 font-black border-l-4 border-red-600 pl-4">{{ __('7. School Students Already Enrolled') }}</h3>
                            <p class="font-bold text-red-600">{{ __('Enrolled_Note') }}</p>

                            <h4 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('Parent / Guardian Responsibility') }}</h4>
                            <ul>
                                <li>{{ __('Responsibility_Item1') }}</li>
                                <li>{{ __('Responsibility_Item2') }}</li>
                                <li>{{ __('Responsibility_Item3') }}</li>
                                <li>{{ __('Responsibility_Item4') }}</li>
                                <li>{{ __('Responsibility_Item5') }}</li>
                            </ul>

                            <h4 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('Need Help?') }}</h4>
                            <p>{{ __('For any queries or clarification, please contact:') }}</p>

                            <h4 class="text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">{{ __('Programme Coordinator') }}</h4>
                            <p class="text-lg font-bold flex items-center gap-2">
                                @php
                                    $coordNum = __('Coordinator_Number');
                                    $coordClean = str_replace(' ', '', $coordNum);
                                @endphp
                                📞 <a href="tel:{{ $coordClean }}" onclick="navigator.clipboard.writeText('{{ $coordNum }}'); alert('Copied to clipboard');" class="hover:text-[#00bde0] transition-colors" title="Click to copy and call">{{ $coordNum }}</a>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="p-8 bg-gray-50 border-t border-gray-200">
                    <label class="flex items-center p-6 border-2 border-gray-200 rounded-2xl cursor-pointer hover:border-[#00bde0] hover:bg-[#00bde0]/5 transition-all group bg-white">
                        <div class="flex items-center h-5">
                            <input type="checkbox" x-model="formData.consent" class="w-6 h-6 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded-lg">
                        </div>
                        <div class="ml-4 flex-1">
                            <span class="text-lg font-black text-gray-900 uppercase tracking-tight group-hover:text-[#00bde0] transition-colors">{{ __('I agree to the Terms & Conditions') }}</span>
                            <p class="text-sm font-black text-gray-500 uppercase tracking-widest mt-0.5">{{ __('I have read and understood all instructions') }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm font-bold uppercase tracking-wider">
                                <a href="https://pramukh.academy/terms/" target="_blank" class="text-[#00bde0] hover:text-[#0099b8] hover:underline transition-all" @click.stop>{{ __('Terms & Conditions') }}</a>
                                <span class="text-gray-300">|</span>
                                <a href="https://pramukh.academy/privacy-policy/" target="_blank" class="text-[#00bde0] hover:text-[#0099b8] hover:underline transition-all" @click.stop>{{ __('Privacy Policy') }}</a>
                                <span class="text-gray-300">|</span>
                                <a href="https://pramukh.academy/refund-policy/" target="_blank" class="text-[#00bde0] hover:text-[#0099b8] hover:underline transition-all" @click.stop>{{ __('Refund Policy') }}</a>
                                {{-- <span class="text-gray-300">|</span> --}}
                                {{-- <a href="https://pramukh.academy/wp-content/uploads/2026/03/pramukh-academy-brochure-x13.pdf" target="_blank" class="text-[#00bde0] hover:text-[#0099b8] hover:underline transition-all" @click.stop>{{ __('Brochure') }}</a> --}}
                            </div>
                        </div>
                    </label>

                    <div class="mt-8 flex justify-end">
                        <button type="button" @click="acceptConsent" :disabled="!formData.consent"
                            class="px-12 py-4 bg-[#00bde0] text-white rounded-2xl font-black uppercase tracking-widest hover:bg-[#0099b8] hover:shadow-xl hover:shadow-[#00bde0]/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ __('Start Registration') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- School Warning Modal Overlay -->
    <div x-show="showSchoolWarningModal" 
        class="fixed inset-0 z-[110] overflow-y-auto"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-red-100">
                <div class="p-6 bg-red-50 border-b border-red-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-red-900 uppercase tracking-tight">{{ __('Registration Not Available') }}</h3>
                        <p class="text-xs text-red-500 font-bold">{{ __('School Already Connected') }}</p>
                    </div>
                </div>

                <div class="p-6 bg-white text-gray-700 leading-relaxed text-sm">
                    <p class="mb-4">
                        {{ __('Your school,') }} <strong class="text-gray-900" x-text="warningSchoolName"></strong>{{ __(', is already connected with the Pramukh SANKALP Programme, and SANKALP is available there in offline classroom mode.') }}
                    </p>
                    <p class="mb-4">
                        {{ __('Online individual registration is not available for students of your school. Please contact your School Admin or SANKALP Coordinator to join through your school.') }}
                    </p>
                    <p class="font-bold text-gray-900">
                        {{ __('Please note: the charges may vary at school level depending on offline facilities, school resources and educator support.') }}
                    </p>
                </div>

                <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end">
                    <button type="button" @click="showSchoolWarningModal = false; formData.school_name = '';" class="px-6 py-2.5 bg-gray-900 text-white rounded-xl font-bold uppercase tracking-wider text-xs hover:bg-gray-800 transition-all">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://www.google.com/recaptcha/api.js?render=6Lf_qp4qAAAAAPcPMnVEfUXra1xegOhNNZuaq5PX"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('registrationForm', () => ({
            step: @json($savedData['step'] ?? 1),
            maxStep: 6,
            showConsentModal: !@json($savedData['consent'] ?? false),
            isSubmitting: false,
            // Regular registration closed for the semester: capture a lead
            // instead of running the OTP / plan / payment flow.
            registrationClosed: @json($registrationClosed ?? false),
            // Which wizard step each field lives on, for error navigation.
            fieldSteps: {
                first_name: 2, middle_name: 2, surname: 2, gender: 2, dob: 2,
                education_level: 3, board: 3, medium: 3, school_name: 3, other_eduboard_name: 3,
                attending_sabha: 4, satsang_mandal: 4, kshetra_name: 4, shikharabaddha_mandir: 4, mandal_sanchalak_name: 4, mandal_sanchalak_contact: 4,
                guardian_full_name: 5, guardian_gender: 5, email: 5, primary_contact: 5, whatsapp_contact: 5,
                country: 6, state: 6, district: 6, taluka: 6, address: 6, pincode: 6, handout_language: 6
            },
            isSubmitted: @json($savedData['isSubmitted'] ?? false),
            studentId: @json($savedData['studentId'] ?? null),
            redirectUrl: '',
            showOtpSection: @json(($savedData['studentId'] ?? null) ? true : false),
            otpSending: false,
            otpVerifying: false,
            otp: '',
            otpMessage: '',
            otpError: false,
            timer: 0,
            timerInterval: null,
            errors: {},
            countries: [{country_id: 1, cname: 'India'}], // Default to India
            states: [],
            districts: [],
            talukos: [],
            otherBoardId: {{ DB::table('ms_eductionalboard')->where('boardname', 'Other')->value('eduboard_id') ?? 11 }},
            isPincodeLoading: false,
            showSchoolWarningModal: false,
            warningSchoolName: '',
            suggestions: [],
            showSuggestions: false,
            stepTitles: [
                '{!! __('Consent') !!}',
                '{!! __('Personal Information') !!}',
                '{!! __('Educational Details') !!}',
                '{!! __('Satsang Related Details') !!}',
                '{!! __('SANKALP Guardian Information') !!}',
                '{!! __('Study Material & courier address') !!}'
            ],
            stepIcons: [
                `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>`,
                `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>`,
                `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.382-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>`,
                `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>`,
                `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>`
            ],
            formData: {
                consent: false,
                whatsapp_same_as_primary: false,
                first_name: '',
                middle_name: '',
                surname: '',
                gender: '',
                email: '',
                dob: '',
                primary_contact: '',
                whatsapp_contact: '',
                education_level: '',
                stream: '',
                board: '',
                other_eduboard_name: '',
                medium: '',
                school_name: '{{ session('school_registration_school_name', '') }}',
                attending_sabha: '',
                satsang_mandal: '',
                kshetra_name: '',
                shikharabaddha_mandir: '',
                mandal_sanchalak_name: '',
                mandal_sanchalak_contact: '',
                guardian_full_name: '',
                guardian_first_name: '',
                guardian_middle_name: '',
                guardian_last_name: '',
                guardian_gender: '',
                guardian_contact: '',
                handout_language: '',
                country: '', // Default to empty 'Select Country'
                state: '',
                district: '',
                taluka: '',
                city_village: '',
                address: '',
                pincode: '',
                ...(@json($savedData ?? []))
            },
            
            get stepTitle() {
                return this.stepTitles[this.step - 1];
            },

            get stepHasVideo() {
                return this.step === 5;
            },

            init() {
                // Normalize any numeric strings from saved progress to Numbers for strict option binding
                if (this.formData.country && !isNaN(this.formData.country)) {
                    this.formData.country = Number(this.formData.country);
                }
                if (this.formData.state && !isNaN(this.formData.state)) {
                    this.formData.state = Number(this.formData.state);
                }
                if (this.formData.district && !isNaN(this.formData.district)) {
                    this.formData.district = Number(this.formData.district);
                }
                if (this.formData.taluka && !isNaN(this.formData.taluka)) {
                    this.formData.taluka = Number(this.formData.taluka);
                }

                // Convert YYYY-MM-DD to DD/MM/YYYY for display (from saved data)
                if (this.formData.dob && this.formData.dob.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const parts = this.formData.dob.split('-');
                    this.formData.dob = `${parts[2]}/${parts[1]}/${parts[0]}`;
                }

                // Combine guardian names into guardian_full_name if empty but parts exist
                if (!this.formData.guardian_full_name) {
                    const parts = [];
                    if (this.formData.guardian_first_name) parts.push(this.formData.guardian_first_name);
                    if (this.formData.guardian_middle_name) parts.push(this.formData.guardian_middle_name);
                    if (this.formData.guardian_last_name) parts.push(this.formData.guardian_last_name);
                    if (parts.length > 0) {
                        this.formData.guardian_full_name = parts.join(' ');
                    }
                }

                // Load initial data without clearing progress values
                if (this.formData.country) {
                    this.loadStates(false);
                }
                if (this.formData.state) {
                    this.loadDistricts(false);
                }
                if (this.formData.district) {
                    this.loadTalukos(false);
                }
            },

            acceptConsent() {
                if (this.formData.consent) {
                    this.showConsentModal = false;
                    this.step = 2;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },

            goToStep(targetStep) {
                if (targetStep < this.step) {
                    this.step = targetStep;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    return;
                }
                
                if (!this.formData.consent) {
                    this.showConsentModal = true;
                    return;
                }
                
                if (this.validateStep(this.step)) {
                    if (targetStep <= this.step + 1) {
                        this.step = targetStep;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                }
            },

            nextStep() {
                if (!this.formData.consent) {
                    this.showConsentModal = true;
                    return;
                }
                
                if (this.validateStep(this.step)) {
                    if (this.step < this.maxStep) {
                        this.step++;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        this.persistProgress();
                    }
                }
            },

            persistProgress() {
                const payload = { ...this.formData, step: this.step };
                // Convert DD/MM/YYYY to YYYY-MM-DD for storage
                if (payload.dob) {
                    payload.dob = this.convertDobForDb(payload.dob);
                }

                fetch('{{ route('registration.save-progress') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
            },

            requestPreRegistrationOtp(isResend = false) {
                if (this.isSubmitting) return;
                if (!this.validateStep(this.maxStep)) return;

                this.isSubmitting = true;
                this.otpMessage = '';
                this.otpError = false;

                const payload = { ...this.formData, student_id: this.studentId };

                fetch('{{ route('registration.send-pre-registration-otp') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(async res => {
                    const text = await res.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        data = { success: false, message: text || 'Raw server error: ' + res.statusText };
                    }
                    return { ok: res.ok && data.success, data };
                })
                .then(({ ok, data }) => {
                    if(ok) {
                        this.isSubmitting = false;
                        this.isSubmitted = true;
                        this.showOtpSection = true;
                        this.otpMessage = data.message;
                        if (data.student_id) {
                            this.studentId = data.student_id;
                        }
                        this.startTimer();
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        // Probably validation failure like unique email
                        if (data.errors && data.errors.email) {
                            this.isSubmitting = false;
                            this.errors = { email: data.errors.email };
                            this.step = 5; // Go back to the email field step
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            alert('This email is already registered. Please change your email.');
                        } else {
                            console.log('OTP Send Request Failed. Response data coming from curl:', data.curl_response || data);
                            this.isSubmitting = false;
                            alert(data.message || 'Failed to send OTP.');
                        }
                    }
                }).catch(err => {
                    this.isSubmitting = false;
                    alert('An error occurred while sending OTP.');
                });
            },

            // Regular registration is closed: save the details as a Student Lead.
            // No OTP — the address is confirmed later by the payment-link email.
            submitLead() {
                if (this.isSubmitting) return;
                if (!this.validateStep(this.maxStep)) return;

                const email = this.formData.email || '';
                if (!confirm('{{ __('We will email you at') }} ' + email + '\n\n{{ __('Is this correct?') }}')) return;

                this.isSubmitting = true;

                fetch('{{ route('registration.lead-submit') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ...this.formData })
                })
                .then(async res => {
                    const text = await res.text();
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        data = { success: false, message: text || 'Raw server error: ' + res.statusText };
                    }
                    return { ok: res.ok && data.success, data };
                })
                .then(({ ok, data }) => {
                    if (ok && data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }

                    this.isSubmitting = false;

                    if (data.errors) {
                        this.errors = data.errors;
                        const firstError = Object.keys(this.errors)[0];
                        if (firstError) {
                            if (this.fieldSteps[firstError]) {
                                this.step = this.fieldSteps[firstError];
                            }
                            this.$nextTick(() => {
                                const el = document.getElementById(firstError);
                                if (el) {
                                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    el.focus();
                                }
                            });
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        alert(data.message || '{{ __('Something went wrong. Please try again.') }}');
                    }
                })
                .catch(() => {
                    this.isSubmitting = false;
                    alert('{{ __('Something went wrong. Please try again.') }}');
                });
            },

            startTimer() {
                this.timer = 60;
                clearInterval(this.timerInterval);
                this.timerInterval = setInterval(() => {
                    if (this.timer > 0) {
                        this.timer--;
                    } else {
                        clearInterval(this.timerInterval);
                    }
                }, 1000);
            },

            prevStep() {
                if (this.step > 2) {
                    this.step--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },

            convertToUpper(field) {
                if (this.formData[field]) {
                    this.formData[field] = this.formData[field].toUpperCase().replace(/[^a-zA-Z\s]/g, '');
                }
            },

            splitGuardianName() {
                const fullName = (this.formData.guardian_full_name || '').trim();
                const parts = fullName.split(/\s+/);
                if (parts.length > 0) {
                    this.formData.guardian_first_name = parts[0];
                    if (parts.length > 2) {
                        this.formData.guardian_middle_name = parts.slice(1, -1).join(' ');
                        this.formData.guardian_last_name = parts[parts.length - 1];
                    } else if (parts.length === 2) {
                        this.formData.guardian_middle_name = '';
                        this.formData.guardian_last_name = parts[1];
                    } else {
                        this.formData.guardian_middle_name = '';
                        this.formData.guardian_last_name = '';
                    }
                } else {
                    this.formData.guardian_first_name = '';
                    this.formData.guardian_middle_name = '';
                    this.formData.guardian_last_name = '';
                }
            },

            validatePhone(field) {
                if (this.formData[field]) {
                    this.formData[field] = this.formData[field].replace(/[^0-9]/g, '').slice(0, 10);
                }
            },

            validateZipcode(field) {
                if (this.formData[field]) {
                    this.formData[field] = this.formData[field].replace(/[^0-9]/g, '').slice(0, 6);
                }
            },

            validateStep(stepNum) {
                this.errors = {};
                let isValid = true;

                if (stepNum === 2) { // Personal Information
                    const fields = ['first_name', 'middle_name', 'surname', 'gender', 'dob'];
                    fields.forEach(f => {
                        if (!this.validateField(f)) isValid = false;
                    });
                } else if (stepNum === 3) { // Educational Details
                    const fields = ['board', 'medium', 'school_name'];
                    if (['11', '12', 11, 12].includes(this.formData.education_level)) {
                        fields.push('stream');
                    }
                    if (this.formData.board == this.otherBoardId) {
                        fields.push('other_eduboard_name');
                    }
                    fields.forEach(f => {
                        if (!this.validateField(f)) isValid = false;
                    });
                } else if (stepNum === 4) { // Satsang Related Details
                    if (!this.validateField('attending_sabha')) isValid = false;
                    if (this.formData.attending_sabha === 'Yes') {
                        const fields = ['satsang_mandal', 'kshetra_name', 'shikharabaddha_mandir', 'mandal_sanchalak_name', 'mandal_sanchalak_contact'];
                        fields.forEach(f => {
                            if (!this.validateField(f)) isValid = false;
                        });
                    }
                } else if (stepNum === 5) { // Guardian Information
                    this.formData.guardian_contact = this.formData.primary_contact;
                    const fields = ['guardian_full_name', 'guardian_gender', 'email', 'primary_contact', 'whatsapp_contact'];
                    fields.forEach(f => {
                        if (!this.validateField(f)) isValid = false;
                    });
                } else if (stepNum === 6) { // Address Details
                    const fields = ['country', 'state', 'district', 'taluka', 'city_village', 'address', 'pincode', 'handout_language'];
                    fields.forEach(f => {
                        if (!this.validateField(f)) isValid = false;
                    });
                }

                if (!isValid) {
                    // Optionally scroll to first error
                    const firstError = Object.keys(this.errors)[0];
                    if (firstError) {
                        const el = document.getElementById(firstError);
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                return isValid;
            },

            validateField(field) {
                let isValid = true;
                this.errors[field] = null;

                const val = this.formData[field];

                if (!val || (typeof val === 'string' && val.trim() === '')) {
                    this.errors[field] = ['{{ __("This field is required") }}'];
                    isValid = false;
                } else {
                    if (field === 'email') {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(val)) {
                            this.errors[field] = ['{{ __("Invalid email address") }}'];
                            isValid = false;
                        }
                    } else if (field === 'dob') {
                        // Handle both DD/MM/YYYY and YYYY-MM-DD formats
                        let dateObj;
                        let day, month, year;

                        if (val.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                            // DD/MM/YYYY format (from Flatpickr)
                            const parts = val.split('/');
                            day = parseInt(parts[0], 10);
                            month = parseInt(parts[1], 10);
                            year = parseInt(parts[2], 10);
                            dateObj = new Date(year, month - 1, day);
                        } else if (val.match(/^\d{4}-\d{2}-\d{2}$/)) {
                            // YYYY-MM-DD format (from HTML5 date input)
                            const parts = val.split('-');
                            day = parseInt(parts[2], 10);
                            month = parseInt(parts[1], 10);
                            year = parseInt(parts[0], 10);
                            dateObj = new Date(year, month - 1, day);
                        } else {
                            this.errors[field] = ['{{ __("Invalid date format. Use DD/MM/YYYY") }}'];
                            isValid = false;
                            return isValid;
                        }

                        // Validate date components
                        if (day < 1 || day > 31 || month < 1 || month > 12 || year < 1900 || year > 2100) {
                            this.errors[field] = ['{{ __("Invalid date. Please enter a valid date.") }}'];
                            isValid = false;
                        } else {
                            // Check if the date is valid (e.g., not 31/02/2020)
                            if (dateObj.getDate() !== day || dateObj.getMonth() !== month - 1) {
                                this.errors[field] = ['{{ __("Invalid date. Please enter a valid date.") }}'];
                                isValid = false;
                            } else {
                                const today = new Date();
                                let age = today.getFullYear() - dateObj.getFullYear();
                                const m = today.getMonth() - dateObj.getMonth();
                                if (m < 0 || (m === 0 && today.getDate() < dateObj.getDate())) {
                                    age--;
                                }
                                if (age < 10 || age > 18) {
                                    this.errors[field] = ['{{ __("Age must be between 10 and 18 years.") }}'];
                                    isValid = false;
                                }
                            }
                        }
                    } else if (['primary_contact', 'whatsapp_contact', 'mandal_sanchalak_contact', 'guardian_contact'].includes(field)) {
                        if (val.length < 10) {
                            this.errors[field] = ['{{ __("Must be 10 digits") }}'];
                            isValid = false;
                        }
                    } else if (field === 'pincode') {
                        if (val.length < 6) {
                            this.errors[field] = ['{{ __("Must be 6 digits") }}'];
                            isValid = false;
                        }
                    } else if (field === 'guardian_full_name') {
                        const parts = val.trim().split(/\s+/);
                        if (parts.length < 2) {
                            this.errors[field] = ['{{ __("Please enter the complete name (First Name + Middle Name + Last Name).") }}'];
                            isValid = false;
                        }
                    }
                }

                if (isValid) delete this.errors[field];
                return isValid;
            },

            formatFieldName(field) {
                const map = {
                    first_name: '{{ __("First Name") }}',
                    middle_name: '{{ __("Middle Name") }}',
                    surname: '{{ __("Surname/Last Name") }}',
                    gender: '{{ __("Gender") }}',
                    dob: '{{ __("Date of Birth") }}',
                    email: '{{ __("Email Address") }}',
                    primary_contact: '{{ __("Primary Contact Number") }}',
                    whatsapp_contact: '{{ __("WhatsApp Number") }}',
                    education_level: '{{ __("Education Level") }}',
                    stream: '{{ __("Stream") }}',
                    board: '{{ __("Board") }}',
                    medium: '{{ __("Medium") }}',
                    school_name: '{{ __("School Name") }}',
                    attending_sabha: '{{ __("Attending Sabha") }}',
                    satsang_mandal: '{{ __("Satsang Mandal") }}',
                    kshetra_name: '{{ __("Kshetra Name") }}',
                    shikharabaddha_mandir: '{{ __("Shikharabaddha Mandir") }}',
                    mandal_sanchalak_name: '{{ __("Sanchalak Name") }}',
                    mandal_sanchalak_contact: '{{ __("Sanchalak Contact") }}',
                    guardian_first_name: '{{ __("Guardian First Name") }}',
                    guardian_middle_name: '{{ __("Guardian Middle Name") }}',
                    guardian_last_name: '{{ __("Guardian Last Name") }}',
                    guardian_gender: '{{ __("Guardian Gender") }}',
                    guardian_contact: '{{ __("Guardian Contact") }}',
                    handout_language: '{{ __("Handout Language") }}',
                    country: '{{ __("Country") }}',
                    state: '{{ __("State") }}',
                    district: '{{ __("District") }}',
                    taluka: '{{ __("Taluka") }}',
                    city_village: '{{ __("City / Village") }}',
                    address: '{{ __("Courier Address") }}',
                    pincode: '{{ __("Pincode") }}'
                };
                return map[field] || field.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
            },

            formatDobDisplay(dateString) {
                if (!dateString) return '';
                // If already in DD/MM/YYYY format, return as is
                if (dateString.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                    return dateString;
                }
                // Convert YYYY-MM-DD to DD/MM/YYYY
                if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const parts = dateString.split('-');
                    return `${parts[2]}/${parts[1]}/${parts[0]}`;
                }
                // Try to parse and format
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return dateString;
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}/${month}/${year}`;
            },

            convertDobForDb(dateString) {
                if (!dateString) return null;
                // If already in YYYY-MM-DD format, return as is
                if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    return dateString;
                }
                // Convert DD/MM/YYYY to YYYY-MM-DD
                if (dateString.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                    const parts = dateString.split('/');
                    return `${parts[2]}-${parts[1]}-${parts[0]}`;
                }
                return dateString;
            },

            loadStates(clearChild = true) {
                if (!this.formData.country) {
                    this.states = [];
                    if (clearChild) {
                        this.formData.state = '';
                        this.formData.district = '';
                        this.formData.taluka = '';
                    }
                    this.districts = [];
                    this.talukos = [];
                    return;
                }
                fetch("{{ route('locations.api.states', ['country' => ':id']) }}".replace(':id', this.formData.country))
                    .then(res => res.json())
                    .then(data => {
                        this.states = data;
                        if (clearChild) {
                            this.formData.state = '';
                            this.formData.district = '';
                            this.formData.taluka = '';
                            this.districts = [];
                            this.talukos = [];
                        }
                    });
            },

            loadDistricts(clearChild = true) {
                if (!this.formData.state) {
                    this.districts = [];
                    if (clearChild) {
                        this.formData.district = '';
                        this.formData.taluka = '';
                    }
                    this.talukos = [];
                    return;
                }
                fetch("{{ route('locations.api.districts', ['state' => ':id']) }}".replace(':id', this.formData.state))
                    .then(res => res.json())
                    .then(data => {
                        this.districts = data;
                        if (clearChild) {
                            this.formData.district = '';
                            this.formData.taluka = '';
                            this.talukos = [];
                        }
                    });
            },

            loadTalukos(clearChild = true) {
                if (!this.formData.district) {
                    this.talukos = [];
                    if (clearChild) {
                        this.formData.taluka = '';
                    }
                    return;
                }
                fetch("{{ route('locations.api.talukos', ['district' => ':id']) }}".replace(':id', this.formData.district))
                    .then(res => res.json())
                    .then(data => {
                        this.talukos = data;
                        if (clearChild) {
                            this.formData.taluka = '';
                        }
                    });
            },

            async fetchAddressByPincode() {
                const pin = this.formData.pincode;
                if (!pin || pin.length !== 6 || isNaN(pin)) return;
                
                this.isPincodeLoading = true;
                try {
                    // 1. Ensure Country is set to India (1) and load states
                    this.formData.country = 1;
                    if (this.states.length === 0) {
                        await new Promise((resolve) => {
                            fetch("{{ route('locations.api.states', ['country' => 1]) }}")
                                .then(res => res.json())
                                .then(data => {
                                    this.states = data;
                                    resolve();
                                });
                        });
                    }
                    
                    // 2. Fetch Pincode Details via Proxy to avoid SSL errors
                    const res = await fetch(`{{ url('/api/pincode') }}/${pin}`);
                    const data = await res.json();
                    if (data && data[0] && data[0].Status === 'Success' && data[0].PostOffice) {
                        const postOffices = data[0].PostOffice;
                        const stateName = postOffices[0].State;
                        const districtName = postOffices[0].District;
                        
                        // Match State
                        const matchedState = this.states.find(s => s.sname.toLowerCase().trim() === stateName.toLowerCase().trim());
                        if (matchedState) {
                            this.formData.state = matchedState.state_id;
                            
                            // Match District
                            await new Promise((resolve) => {
                                fetch("{{ route('locations.api.districts', ['state' => ':id']) }}".replace(':id', matchedState.state_id))
                                    .then(res => res.json())
                                    .then(distData => {
                                        this.districts = distData;
                                        const matchedDist = this.districts.find(d => d.dname.toLowerCase().trim() === districtName.toLowerCase().trim());
                                        if (matchedDist) {
                                            this.formData.district = matchedDist.dist_id;
                                            
                                            // Fetch Talukos (Populate dropdown, but user selects manually)
                                            fetch("{{ route('locations.api.talukos', ['district' => ':id']) }}".replace(':id', matchedDist.dist_id))
                                                .then(res => res.json())
                                                .then(talukaData => {
                                                    this.talukos = talukaData;
                                                    this.formData.taluka = '';
                                                    
                                                    // Validate fields that have changed
                                                    this.validateField('country');
                                                    this.validateField('state');
                                                    this.validateField('district');
                                                    if (this.errors.taluka) {
                                                        delete this.errors.taluka;
                                                    }
                                                    resolve();
                                                });
                                        } else {
                                            // Clear district and taluka if district not matched
                                            this.formData.district = '';
                                            this.formData.taluka = '';
                                            this.talukos = [];
                                            this.validateField('country');
                                            this.validateField('state');
                                            resolve();
                                        }
                                    });
                            });
                        }
                    }
                } catch (err) {
                    console.error('Pincode lookup error:', err);
                } finally {
                    this.isPincodeLoading = false;
                }
            },

            checkSchoolConnection(name) {
                if (!name || name.trim() === '') return;
                fetch("{{ route('api.schools.check') }}?name=" + encodeURIComponent(name))
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.status == 1) {
                            this.warningSchoolName = name;
                            this.showSchoolWarningModal = true;
                            delete this.errors['school_name'];
                        }
                    });
            },

            fetchSuggestions() {
                if (this.formData.school_name.length < 2) {
                    this.suggestions = [];
                    return;
                }
                fetch('{{ route('api.schools.suggest') }}?query=' + encodeURIComponent(this.formData.school_name))
                    .then(res => res.json())
                    .then(data => {
                        this.suggestions = data;
                        this.showSuggestions = true;
                    });
            },

            selectSuggestion(school) {
                this.formData.school_name = school.schoolname;
                this.showSuggestions = false;
                this.suggestions = [];
                this.checkSchoolConnection(school.schoolname);
            },

            submitForm() {
                if (this.otpVerifying) return;

                this.otpVerifying = true;

                try {
                    // Recaptcha Integration
                    if (typeof grecaptcha === 'undefined') {
                        throw new Error('reCAPTCHA not loaded');
                    }

                    grecaptcha.ready(() => {
                        grecaptcha.execute('6Lf_qp4qAAAAAPcPMnVEfUXra1xegOhNNZuaq5PX', { action: 'submit' }).then((token) => {
                            // Convert DD/MM/YYYY to YYYY-MM-DD for database
                            const payload = { ...this.formData, student_id: this.studentId, recaptchaToken: token, otp: this.otp };
                            if (payload.dob) {
                                payload.dob = this.convertDobForDb(payload.dob);
                            }

                            fetch('{{ route('registration.submit') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(payload)
                            })
                            .then(async res => {
                                const data = await res.json();
                                 if (data.success) {
                                     this.showOtpSection = false;
                                     this.otpVerifying = false;
                                     if (data.student_id) {
                                         this.studentId = data.student_id;
                                     }
                                     window.location.href = data.redirect_url;
                                 } else {
                                    this.otpVerifying = false;
                                    
                                    // If OTP error specifically
                                    if (data.errors && data.errors.otp) {
                                        this.otpError = true;
                                        this.otpMessage = data.errors.otp[0];
                                        return;
                                    }
                                    
                                    this.showOtpSection = false;
                                    this.errors = data.errors || { global: [data.message || 'Validation failed'] };
                                    
                                    // Map fields to their respective steps for navigation
                                    const fieldSteps = this.fieldSteps;

                                    const firstError = Object.keys(this.errors)[0];
                                    if (firstError) {
                                        // Change to the step containing the first error
                                        if (fieldSteps[firstError]) {
                                            this.step = fieldSteps[firstError];
                                        }
                                        
                                        // Scroll to the error element
                                        this.$nextTick(() => {
                                            const el = document.getElementById(firstError);
                                            if (el) {
                                                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                                el.focus();
                                            }
                                        });
                                    }
                                }
                            })
                            .catch(err => {
                                console.error('Submission error:', err);
                                this.isSubmitting = false;
                                alert('{{ __("Something went wrong. Please try again.") }}');
                            });
                        }).catch(err => {
                            console.error('reCAPTCHA execution error:', err);
                            this.isSubmitting = false;
                            alert('{{ __("reCAPTCHA failed. Please refresh and try again.") }}');
                        });
                    });
                } catch (err) {
                    console.error('Submit preparation error:', err);
                    this.isSubmitting = false;
                    alert('{{ __("Failed to initialize submission. Please refresh the page.") }}');
                }
            }
        }));
    });
</script>

<style>
    [x-cloak] { display: none !important; }
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }

    .custom-scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .custom-scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    .animate-shake {
        animation: shake 0.4s ease-in-out 0s 2;
    }
</style>
@endpush
@endsection
