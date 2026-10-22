@extends('layouts.guest')

@section('title', __('Select Subscription Plan'))

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto w-full">
        <!-- Header -->
        <div class="text-center mb-12">
            <img src="{{ asset('images/sankalp.png') }}" alt="Logo" class="h-16 mx-auto mb-6">
            <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tight mb-4">{{ __('Choose Your Plan') }}</h1>
            <p class="text-lg text-gray-600 font-bold">{{ __('Select a subscription plan to proceed with your application.') }}</p>
        </div>

        <!-- Student Info Summary -->
        <div class="bg-[#00bde0]/5 border border-[#00bde0]/20 rounded-3xl p-6 mb-12 flex items-center gap-6">
            <div class="w-16 h-16 rounded-full bg-[#00bde0] text-white flex items-center justify-center text-2xl font-black">
                {{ substr($student->fname, 0, 1) }}
            </div>
            <div>
                <h3 class="text-xl font-black text-gray-900">{{ $student->fname }} {{ $student->lname }}</h3>
                <p class="text-gray-500 font-bold uppercase tracking-widest text-xs">{{ __('Registration ID') }}: #{{ $student->student_id }}</p>
            </div>
        </div>

        <form action="{{ route('registration.save-plan') }}" method="POST">
            @csrf
            <input type="hidden" name="student_id" value="{{ $encodedId }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
                @foreach($feePlans as $plan)
                    <label class="relative group cursor-pointer">
                        <input type="radio" name="plan_id" value="{{ $plan->fee_plan_id }}" class="peer sr-only" required>
                        <div class="h-full bg-white border-2 border-gray-200 rounded-3xl p-8 transition-all duration-300 peer-checked:border-[#00bde0] peer-checked:ring-4 peer-checked:ring-[#00bde0]/10 hover:shadow-xl hover:-translate-y-1">
                            <div class="flex justify-between items-start mb-6">
                                <div class="p-3 rounded-2xl bg-gray-50 group-hover:bg-[#00bde0]/10 transition-colors">
                                    <svg class="w-8 h-8 text-gray-400 group-hover:text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="peer-checked:flex hidden items-center justify-center w-6 h-6 rounded-full bg-[#00bde0] text-white">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                            
                            <h3 class="text-2xl font-black text-gray-900 uppercase tracking-tight mb-2">{{ $plan->fee_name }}</h3>
                            @if($plan->strike_through_amount && $plan->strike_through_amount > 0)
                                <div class="mb-1">
                                    <span class="text-lg font-bold text-gray-400 line-through">₹{{ number_format($plan->strike_through_amount, 0) }}</span>
                                </div>
                            @endif
                            <div class="flex items-baseline gap-1 mb-6">
                                <span class="text-4xl font-black text-[#00bde0]">₹{{ number_format($plan->amount, 0) }}</span>
                                <span class="text-gray-400 font-bold uppercase tracking-widest text-[10px]">{{ __('One Time') }}</span>
                            </div>

                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center gap-2 text-sm font-bold text-gray-600">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    {{ __('Full Access to Programme') }}
                                </li>
                                <li class="flex items-center gap-2 text-sm font-bold text-gray-600">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    {{ __('Study Materials Included') }}
                                </li>
                            </ul>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                <button type="submit" class="group px-10 py-4 bg-[#00bde0] text-white rounded-2xl font-black uppercase tracking-widest hover:bg-[#0099b8] hover:shadow-2xl hover:shadow-[#00bde0]/30 transition-all flex items-center gap-3 w-full sm:w-auto justify-center">
                    {{ __('Proceed to Payment') }}
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                </button>
                <button type="submit" formaction="{{ route('registration.pay-later') }}" class="group px-10 py-4 bg-white border-2 border-gray-200 text-gray-700 rounded-2xl font-black uppercase tracking-widest hover:bg-gray-50 hover:border-gray-900 transition-all flex items-center gap-3 w-full sm:w-auto justify-center">
                    {{ __('Save / Pay Later') }}
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-900 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
