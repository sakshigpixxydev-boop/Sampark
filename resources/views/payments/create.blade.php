@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Add Payment</h1>
        <p class="text-sm text-gray-600 mt-1">Record a new payment transaction</p>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('payments.store') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label for="invoice_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Invoice <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="invoice_id" 
                        id="invoice_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('invoice_id') border-red-500 @enderror"
                        required
                    >
                        <option value="">Select Invoice</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->invoice_id }}" 
                                {{ old('invoice_id', $selectedInvoice?->invoice_id) == $invoice->invoice_id ? 'selected' : '' }}
                                data-amount="{{ $invoice->total_due }}">
                                Invoice #{{ $invoice->invoice_id }} - {{ $invoice->student->firstname ?? '' }} {{ $invoice->student->lastname ?? '' }} - ₹{{ number_format($invoice->total_due, 2) }}
                            </option>
                        @endforeach
                    </select>
                    @error('invoice_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                @if($selectedInvoice)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="text-sm font-medium text-blue-900 mb-2">Invoice Details</h3>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-blue-700">Student:</span>
                                <span class="text-blue-900 font-medium">{{ $selectedInvoice->student->firstname }} {{ $selectedInvoice->student->lastname }}</span>
                            </div>
                            <div>
                                <span class="text-blue-700">School:</span>
                                <span class="text-blue-900 font-medium">{{ $selectedInvoice->school->schoolname ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-blue-700">Total Due:</span>
                                <span class="text-blue-900 font-medium">₹{{ number_format($selectedInvoice->total_due, 2) }}</span>
                            </div>
                            <div>
                                <span class="text-blue-700">Status:</span>
                                <span class="text-blue-900 font-medium">{{ $selectedInvoice->status }}</span>
                            </div>
                        </div>
                    </div>
                @endif
                
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
                            <option value="{{ $mode->paymode_id }}" {{ old('paymode_id') == $mode->paymode_id ? 'selected' : '' }}>
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
                            value="{{ old('processdatetime', now()->format('d/m/Y')) }}"
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
                            value="{{ old('txnid') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('txnid') border-red-500 @enderror"
                            placeholder="Enter transaction reference ID"
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
                            value="{{ old('amount', $selectedInvoice?->total_due) }}"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('amount') border-red-500 @enderror"
                            placeholder="0.00"
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
                            value="{{ old('discount', 0) }}"
                            step="0.01"
                            min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('discount') border-red-500 @enderror"
                            placeholder="0.00"
                        >
                        @error('discount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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
                        placeholder="Store complete payment gateway response here"
                    >{{ old('orgresponse') }}</textarea>
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
                    Create Payment
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-fill amount when invoice is selected
    document.getElementById('invoice_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const amount = selectedOption.getAttribute('data-amount');
        if (amount) {
            document.getElementById('amount').value = amount;
        }
    });
</script>
@endsection
