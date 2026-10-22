@extends('layouts.guest')

@section('title', __('School Registration Payment'))

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/sankalp.png') }}" alt="Logo" class="h-16 mx-auto mb-6">
            <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight mb-2">{{ __('School Registration Payment') }}</h1>
            <p class="text-md text-gray-600 font-bold">{{ $school->schoolname }}</p>
        </div>

        @if($student->school_payment_rejection_reason)
            <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl font-bold flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <span class="text-red-900 font-extrabold uppercase tracking-wide text-xs">Payment Rejected</span>
                    <p class="text-sm font-semibold mt-1">{{ $student->school_payment_rejection_reason }}</p>
                    <p class="text-xs text-red-500 font-medium mt-1">Please review the details, verify your payment transaction, and re-upload the correct details below.</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl font-bold">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $customFees = array_values($school->custom_fees ?? []);
            $selectedIndex = null;
            foreach ($customFees as $i => $fee) {
                if ($student->school_payment_plan_name !== null && ($fee['name'] ?? null) === $student->school_payment_plan_name) {
                    $selectedIndex = (string) $i;
                    break;
                }
            }
            // A school with a single fee plan leaves nothing to choose.
            if ($selectedIndex === null && count($customFees) === 1) {
                $selectedIndex = '0';
            }
            $selectedIndex = old('school_fee_plan_index', $selectedIndex);
            $selectedIndex = ($selectedIndex === null || $selectedIndex === '') ? null : (string) $selectedIndex;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8"
            x-data="{ screenshotPreview: null, selectedFee: @js($selectedIndex), fees: @js($customFees) }">
            <!-- Left Side: Fee Details & Payment Options -->
            <div class="space-y-6">
                <!-- Fee Breakdown -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0099b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Fee Details
                    </h3>
                    
                    @if(count($customFees) > 1)
                        <p class="text-xs font-semibold text-gray-500 mb-3">{{ __('Select the fee plan you are paying for.') }}</p>
                    @endif

                    <div class="space-y-3">
                        @forelse($customFees as $index => $fee)
                            <label class="flex justify-between items-center gap-3 p-4 rounded-2xl border cursor-pointer transition-all"
                                :class="selectedFee === '{{ $index }}' ? 'border-[#0099b8] bg-sky-50 ring-2 ring-[#0099b8]/20' : 'border-gray-200 hover:border-gray-300'">
                                <span class="flex items-center gap-3">
                                    <input type="radio" name="school_fee_plan_index" value="{{ $index }}"
                                        form="school-payment-form" x-model="selectedFee" required
                                        class="w-4 h-4 text-[#0099b8] border-gray-300 focus:ring-[#0099b8]">
                                    <span class="text-sm font-semibold text-gray-700">{{ $fee['name'] ?? '' }}</span>
                                </span>
                                <span class="text-gray-900 font-bold whitespace-nowrap">₹{{ number_format((float) ($fee['amount'] ?? 0), 2) }}</span>
                            </label>
                        @empty
                            <div class="py-3 flex justify-between items-center text-sm font-semibold text-gray-700">
                                <span>Admission & Registration Fee</span>
                                <span class="text-gray-900">₹0.00</span>
                            </div>
                        @endforelse
                    </div>

                    @if(count($customFees) > 0)
                        <div class="mt-4 pt-4 flex justify-between items-center text-base font-black text-gray-900 border-t border-gray-200">
                            <span>Amount Payable</span>
                            <span class="text-[#0099b8] text-lg"
                                x-text="selectedFee !== null && fees[selectedFee] ? '₹' + Number(fees[selectedFee].amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '—'"></span>
                        </div>
                    @endif

                    @if($school->services_features && count($school->services_features) > 0)
                        <div class="mt-6 pt-4 border-t border-gray-100">
                            <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Included Services & Features</h4>
                            <ul class="space-y-1.5">
                                @foreach($school->services_features as $service)
                                    <li class="flex items-center text-xs font-semibold text-gray-600 gap-2">
                                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ $service }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Payment Options -->
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0099b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        How to Pay
                    </h3>

                    @if($school->qr_code_path)
                        <div class="text-center bg-gray-50 rounded-2xl p-4 mb-4 border border-gray-200">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Scan & Pay via UPI / QR Code</p>
                            <img src="{{ Storage::url($school->qr_code_path) }}" alt="Payment QR Code" class="w-40 h-40 mx-auto border border-gray-300 rounded-lg bg-white shadow-sm p-1">
                        </div>
                    @endif

                    @if($school->offline_payment_name || $school->offline_payment_contact)
                        <div class="bg-sky-50 rounded-2xl p-4 text-sky-800 text-xs font-semibold space-y-2 border border-sky-100">
                            @if($school->offline_payment_name)
                                <div class="flex justify-between">
                                    <span>Account / Receiver Name:</span>
                                    <strong class="text-sky-900">{{ $school->offline_payment_name }}</strong>
                                </div>
                            @endif
                            @if($school->offline_payment_contact)
                                <div class="flex justify-between">
                                    <span>Payment Support Contact:</span>
                                    <strong class="text-sky-900">{{ $school->offline_payment_contact }}</strong>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-xs font-medium text-gray-500 text-center">Please contact the school office if you need alternative offline payment instructions.</p>
                    @endif
                </div>
            </div>

            <!-- Right Side: Payment Submission Form -->
            <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-6 md:p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#0099b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Submit Payment Details
                </h3>

                <form id="school-payment-form" action="{{ route('registration.store-school-payment', $encodedId) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Payment Method -->
                    <div class="mb-6">
                        <label for="school_payment_method" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">{{ __('Payment Method') }} <span class="text-red-500">*</span></label>
                        <select name="school_payment_method" id="school_payment_method" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm font-semibold" required>
                            <option value="">Select Method</option>
                            <option value="UPI" {{ old('school_payment_method', $student->school_payment_method) === 'UPI' ? 'selected' : '' }}>UPI / QR Scan</option>
                            <option value="Bank Transfer" {{ old('school_payment_method', $student->school_payment_method) === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer / IMPS</option>
                            <option value="Cash" {{ old('school_payment_method', $student->school_payment_method) === 'Cash' ? 'selected' : '' }}>Cash to school office</option>
                            <option value="Other" {{ old('school_payment_method', $student->school_payment_method) === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- UPI ID / Reference Number -->
                    <div class="mb-6">
                        <label for="school_payment_upi_id" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">{{ __('Transaction ID / UPI Reference') }}</label>
                        <input type="text" name="school_payment_upi_id" id="school_payment_upi_id" value="{{ old('school_payment_upi_id', $student->school_payment_upi_id) }}" placeholder="e.g. UPI Ref / Txn ID" class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] text-sm font-semibold">
                    </div>

                    <!-- Screenshot Upload -->
                    <div class="mb-8">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2 text-center">{{ __('Payment Screenshot / Receipt') }} <span class="text-red-500">*</span></label>
                        
                        <div class="flex flex-col items-center justify-center">
                            <!-- Image Preview -->
                            <div class="relative mb-4">
                                <div class="w-48 h-48 rounded-3xl bg-gray-50 border-4 border-dashed border-gray-200 overflow-hidden flex items-center justify-center relative group">
                                    <template x-if="!screenshotPreview">
                                        @if($student->school_payment_screenshot)
                                            <img src="{{ Storage::url($student->school_payment_screenshot) }}" class="w-full h-full object-cover">
                                        @else
                                            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        @endif
                                    </template>
                                    <template x-if="screenshotPreview">
                                        <img :src="screenshotPreview" class="w-full h-full object-cover">
                                    </template>
                                </div>
                                <label for="payment_screenshot" class="absolute -bottom-4 left-1/2 -translate-x-1/2 w-12 h-12 bg-[#00bde0] text-white rounded-2xl flex items-center justify-center shadow-xl cursor-pointer hover:bg-[#0099b8] transition-all hover:scale-110 active:scale-95">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <input type="file" id="payment_screenshot" name="payment_screenshot" class="hidden" accept="image/*" 
                                        @change="const file = $el.files[0]; if (file) { const reader = new FileReader(); reader.onload = (e) => { screenshotPreview = e.target.result; }; reader.readAsDataURL(file); }">
                                </label>
                            </div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ __('Image allowed (Max 2MB)') }}</p>
                        </div>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" class="group w-full py-4 bg-green-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-green-700 hover:shadow-2xl hover:shadow-green-600/30 transition-all flex items-center justify-center gap-3">
                            {{ __('Submit & Continue') }}
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <p class="mt-8 text-center text-gray-500 text-[10px] font-bold uppercase tracking-widest">
            {{ __('PRAMUKH Academy © 2026') }}
        </p>
    </div>
</div>
@endsection
