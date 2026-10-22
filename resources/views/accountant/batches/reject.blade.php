@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">Reject Batch</h1>
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
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                    <p class="text-sm text-gray-600">Total Fees</p>
                    <p class="text-lg font-semibold text-[#00bde0] mt-1">₹{{ number_format($batch->total_fees, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Rejection Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Rejection Details</h2>
            
            <form method="POST" action="{{ route('accountant.batches.reject', $batch->batch_id) }}" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Reason for Rejection <span class="text-red-500">*</span>
                    </label>
                    <textarea name="reason" rows="6" required maxlength="1000"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                              placeholder="Please provide a detailed reason for rejecting this batch. This will be sent to the uploader.">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Maximum 1000 characters</p>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-red-800">
                        <strong>Warning:</strong> By rejecting this batch, you are indicating that there are issues that need to be corrected. 
                        The uploader will be notified and can make corrections and re-submit the batch.
                    </p>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-sm text-yellow-800">
                        <strong>Note:</strong> Common reasons for rejection include:
                    </p>
                    <ul class="list-disc list-inside text-sm text-yellow-800 mt-2 ml-2">
                        <li>Incorrect or missing payment information</li>
                        <li>Validation errors in student data</li>
                        <li>Duplicate student entries</li>
                        <li>Incorrect fee calculations</li>
                        <li>Missing required documents</li>
                    </ul>
                </div>

                <div class="flex gap-2 pt-4">
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-600 text-white font-medium px-6 py-2 rounded-lg transition">
                        Reject Batch
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
