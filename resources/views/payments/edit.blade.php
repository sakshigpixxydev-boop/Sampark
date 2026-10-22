@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Payment</h1>
        <p class="text-sm text-gray-600 mt-1">Update payment transaction details</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('payments.update', $payment->payment_id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-2">Invoice Details</h3>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-gray-700">Invoice ID:</span>
                            <span class="text-gray-900 font-medium">#{{ $payment->invoice_id }}</span>
                        </div>
                        <div>
                            <span class="text-gray-700">Student:</span>
                            <span class="text-gray-900 font-medium">{{ $payment->invoice->student->firstname }} {{ $payment->invoice->student->lastname }}</span>
                        </div>
                        <div>
                            <span class="text-gray-700">School:</span>
                            <span class="text-gray-900 font-medium">{{ $payment->invoice->school->schoolname ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-700">Total Due:</span>
                            <span class="text-gray-900 font-medium">₹{{ number_format($payment->invoice->total_due, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="paymode_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Mode <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="paymode_id" 
                        id="paymode_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('paymode_id') border-red-500 @enderror"
                        required
                    >
                        <option value="">Select Payment Mode</option>
                        @foreach($paymentModes as $mode)
                            <option value="{{ $mode->paymode_id }}" {{ old('paymode_id', $payment->paymode_id) == $mode->paymode_id ? 'selected' : '' }}>
                                {{ $mode->paymodename }}
                            </option>
                        @endforeach
                    </select>
                    @error('paymode_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="processdatetime" class="block text-sm font-medium text-gray-700 mb-1">
                            Payment Date & Time <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="processdatetime" 
                            id="processdatetime" 
                            value="{{ old('processdatetime', $payment->processdatetime ? $payment->processdatetime->format('d/m/Y') : '') }}"
                            class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('processdatetime') border-red-500 @enderror"
                            placeholder="DD/MM/YYYY"
                            required
                        >
                        @error('processdatetime')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="txnid" class="block text-sm font-medium text-gray-700 mb-1">
                            Transaction ID <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="txnid" 
                            id="txnid" 
                            value="{{ old('txnid', $payment->txnid) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('txnid') border-red-500 @enderror"
                            required
                        >
                        @error('txnid')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="amount" 
                            id="amount" 
                            value="{{ old('amount', $payment->amount) }}"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('amount') border-red-500 @enderror"
                            required
                        >
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="discount" class="block text-sm font-medium text-gray-700 mb-1">
                            Discount
                        </label>
                        <input 
                            type="number" 
                            name="discount" 
                            id="discount" 
                            value="{{ old('discount', $payment->discount) }}"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('discount') border-red-500 @enderror"
                        >
                        @error('discount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="status" 
                        id="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('status') border-red-500 @enderror"
                        required
                    >
                        <option value="0" {{ old('status', $payment->status) == '0' ? 'selected' : '' }}>Pending</option>
                        <option value="1" {{ old('status', $payment->status) == '1' ? 'selected' : '' }}>Authorized</option>
                        <option value="2" {{ old('status', $payment->status) == '2' ? 'selected' : '' }}>Refunded</option>
                        <option value="3" {{ old('status', $payment->status) == '3' ? 'selected' : '' }}>Failed</option>
                        <option value="4" {{ old('status', $payment->status) == '4' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="orgresponse" class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Gateway Response
                    </label>
                    <textarea 
                        name="orgresponse" 
                        id="orgresponse" 
                        rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('orgresponse') border-red-500 @enderror"
                    >{{ old('orgresponse', $payment->orgresponse) }}</textarea>
                    @error('orgresponse')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('payments.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Update Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
