@extends('layouts.app')

@section('title', 'Batch Payment')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Alerts -->
        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg flex items-center justify-between shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium text-red-800">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Header Banner -->
            <div class="bg-gradient-to-br from-[#00bde0] to-[#0099b8] px-8 py-10 text-white">
                <h2 class="text-3xl font-extrabold tracking-tight">Complete Batch Registration Payment</h2>
                <p class="mt-2 text-white text-opacity-90">Please verify the batch details below and proceed with online payment via Razorpay.</p>
            </div>

            <!-- Details Section -->
            <div class="p-8 space-y-8">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Batch Details</h3>
                    <div class="grid grid-cols-2 gap-y-4 gap-x-6">
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase">Batch Code</span>
                            <span class="text-lg font-bold text-gray-800 font-mono">{{ $batch->batch_code }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase">School Name</span>
                            <span class="text-lg font-bold text-gray-800">{{ $batch->school->schoolname ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase">Target Semester Batch</span>
                            <span class="text-lg font-bold text-gray-800">{{ $batch->semesterBatch->display_name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase">Total Students</span>
                            <span class="text-lg font-bold text-gray-800">{{ $batch->total_students }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <span class="block text-xs font-semibold text-gray-500 uppercase">Total Fee Due</span>
                        <span class="text-3xl font-extrabold text-gray-900">₹{{ number_format($batch->total_fees, 2) }}</span>
                    </div>
                    <div>
                        <button id="pay-button" class="w-full md:w-auto inline-flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-bold rounded-xl shadow-lg text-white bg-gradient-to-br from-[#00bde0] to-[#0099b8] hover:shadow-xl focus:outline-none focus:ring-4 focus:ring-[#00bde0] focus:ring-opacity-50 transition-all duration-150 transform hover:-translate-y-0.5">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Pay via Razorpay
                        </button>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-6 border-t border-gray-100 text-sm">
                    <a href="{{ route('admin.registrations.index') }}" class="text-gray-500 hover:text-gray-700 font-semibold flex items-center transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancel & Return
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Verification Form -->
<form id="payment-form" action="{{ route('admin.registrations.verify-batch-payment', $batch->batch_id) }}" method="POST">
    @csrf
    <input type="hidden" name="razorpay_order_id" value="{{ $razorpayOrder['order_id'] }}">
    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
    <input type="hidden" name="razorpay_signature" id="razorpay_signature">
</form>

@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var options = {
            "key": "{{ $razorpayOrder['key'] }}",
            "amount": "{{ $razorpayOrder['amount'] }}",
            "currency": "{{ $razorpayOrder['currency'] }}",
            "name": "{{ $razorpayOrder['name'] }}",
            "description": "{{ $razorpayOrder['description'] }}",
            "order_id": "{{ $razorpayOrder['order_id'] }}",
            "handler": function (response){
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.getElementById('payment-form').submit();
            },
            "prefill": {
                "name": "{{ auth()->user()->fname }} {{ auth()->user()->lname }}",
                "email": "{{ auth()->user()->email }}",
                "contact": "{{ auth()->user()->phone }}"
            },
            "theme": {
                "color": "#00bde0"
            }
        };
        var rzp1 = new Razorpay(options);
        rzp1.on('payment.failed', function (response){
            alert("Payment failed: " + response.error.description);
        });
        document.getElementById('pay-button').onclick = function(e){
            rzp1.open();
            e.preventDefault();
        }
    });
</script>
@endpush
