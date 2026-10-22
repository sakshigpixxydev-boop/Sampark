@extends('layouts.guest')

@section('title', __('Secure Payment'))

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
        <div class="p-8 bg-[#00bde0] text-white text-center">
            <img src="{{ asset('images/sankalp.png') }}" alt="Logo" class="h-12 mx-auto mb-4 brightness-0 invert">
            <h2 class="text-2xl font-black uppercase tracking-tight">{{ __('Complete Payment') }}</h2>
            <p class="text-[#00bde0]/80 font-bold uppercase tracking-widest text-[10px] mt-1">{{ __('Order ID') }}: {{ $razorpayData['order_id'] }}</p>
        </div>

        <div class="p-8">
            <div class="flex justify-between items-center mb-8 pb-8 border-b border-gray-100">
                <div>
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">{{ __('Subscription Plan') }}</h3>
                    <p class="text-lg font-black text-gray-900">{{ $plan->fee_name }}</p>
                </div>
                <div class="text-right">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">{{ __('Amount') }}</h3>
                    <p class="text-3xl font-black text-[#00bde0]">₹{{ number_format($plan->amount, 0) }}</p>
                </div>
            </div>

            <div class="space-y-4 mb-8">
                <div class="flex justify-between text-sm font-bold">
                    <span class="text-gray-500">{{ __('Student Name') }}</span>
                    <span class="text-gray-900">{{ $student->fname }} {{ $student->lname }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold">
                    <span class="text-gray-500">{{ __('Email') }}</span>
                    <span class="text-gray-900">{{ $student->email }}</span>
                </div>
            </div>

            <button id="rzp-button1" class="w-full py-4 bg-[#00bde0] text-white rounded-2xl font-black uppercase tracking-widest hover:bg-[#0099b8] transition-all shadow-xl shadow-[#00bde0]/20 flex items-center justify-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                {{ __('Pay Online Now') }}
            </button>
            
            <p class="mt-6 text-center text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-relaxed">
                {{ __('Secure payment processed by Razorpay') }}<br>
                {{ __('Do not refresh or close this window') }}
            </p>
        </div>
    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    var options = @json($razorpayData);
    
    options.handler = function (response){
        // Success handler
        var btn = document.getElementById('rzp-button1');
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.style.cursor = 'not-allowed';
        btn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg> Payment Process Working, Please Wait...`;

        window.location.href = options.callback_url + 
            '&razorpay_payment_id=' + response.razorpay_payment_id + 
            '&razorpay_order_id=' + response.razorpay_order_id + 
            '&razorpay_signature=' + response.razorpay_signature;
    };

    options.modal = {
        "onformsubmit": function(data) {
            console.log("Form Submitted", data);
        },
        "ondismiss": function(){
            console.log("Payment Dismissed");
            var btn = document.getElementById('rzp-button1');
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg> {{ __('Pay Online Now') }}`;
        },
        "escape": false,
        "backdropclose": false
    };

    var rzp1 = new Razorpay(options);
    
    document.getElementById('rzp-button1').onclick = function(e){
        e.preventDefault();
        var btn = document.getElementById('rzp-button1');
        btn.disabled = true;
        btn.style.opacity = '0.7';
        btn.style.cursor = 'not-allowed';
        btn.innerHTML = `<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg> Opening Payment Gateway...`;
        
        rzp1.open();
    }
</script>
@endsection
