@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Approve Payment for Batch</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $batch->batch_code }}</p>
        </div>

        <div class="mb-4">
            <a href="{{ route('accountant.batches.show', $batch->batch_id) }}" class="text-[#00bde0] hover:text-[#0099b8]">
                ← Back to Batch Details
            </a>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Batch Summary -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Batch Summary</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Semester Batch</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">{{ $batch->semesterBatch->display_name ?? 'N/A' }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Uploaded By</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">
                        {{ $batch->uploader->firstname }} {{ $batch->uploader->lastname }}
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Students</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">{{ $batch->total_students }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Fee Per Student</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1">₹{{ number_format($batch->fee_per_student, 2) }}</p>
                </div>
            </div>

            <div class="bg-[#00bde0] bg-opacity-10 border border-[#00bde0] rounded-lg p-4">
                <p class="text-sm text-gray-700">Total Amount to be Verified</p>
                <p class="text-3xl font-bold text-[#00bde0] mt-1">₹{{ number_format($batch->total_fees, 2) }}</p>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Details</h2>
            
            <form method="POST" action="{{ route('accountant.batches.approve', $batch->batch_id) }}" class="space-y-4">
                @csrf
                
                

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Transaction ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="transaction_id" value="{{ old('transaction_id') }}" required 
                           maxlength="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                           placeholder="Enter transaction/reference ID">
                    @error('transaction_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Payment Date <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="payment_date" value="{{ old('payment_date', date('d/m/Y')) }}" required 
                           class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
                    @error('payment_date')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Total Amount <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="total_amount" value="{{ old('total_amount', $batch->total_fees) }}" 
                           required step="0.01" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                           placeholder="Enter total amount received">
                    @error('total_amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Expected amount: ₹{{ number_format($batch->total_fees, 2) }}</p>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <strong>Warning:</strong> By approving this batch, you confirm that payment has been received and verified. 
                        All {{ $batch->total_students }} students in this batch will be registered and marked as paid.
                    </p>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="submit" 
                            class="bg-green-500 hover:bg-green-600 text-white font-medium px-6 py-2 rounded-lg transition">
                        Approve Payment & Register Students
                    </button>
                    <a href="{{ route('accountant.batches.show', $batch->batch_id) }}" 
                       class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-6 py-2 rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
