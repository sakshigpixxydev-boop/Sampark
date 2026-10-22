@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Upload Batch Verification</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('accountant.batches.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Semester Batch</label>
                    <select name="sembatches_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        <option value="">All Batches</option>
                        @foreach($semesterBatches as $semBatch)
                            <option value="{{ $semBatch->sembatches_id }}" {{ request('sembatches_id') == $semBatch->sembatches_id ? 'selected' : '' }}>
                                {{ $semBatch->display_name ?? 'N/A' }} ({{ $semBatch->start_date ? $semBatch->start_date->format('M Y') : '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="text" name="date_from" value="{{ request('date_from') }}" 
                           class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="text" name="date_to" value="{{ request('date_to') }}" 
                           class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Apply Filters
                </button>
                <a href="{{ route('accountant.batches.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Batch List -->
    @if($batches->isEmpty())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <p class="text-gray-500">No upload batches found.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($batches as $batch)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $batch->batch_code }}</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Uploaded by {{ $batch->uploader->firstname }} {{ $batch->uploader->lastname }} 
                                on {{ $batch->upload_date->format('M d, Y h:i A') }}
                            </p>
                        </div>
                        <div>
                            @if($batch->status === 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($batch->status === 'approved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @elseif($batch->status === 'rejected')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Semester Batch</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">{{ $batch->semesterBatch->display_name ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Total Students</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">{{ $batch->total_students }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Fee Per Student</p>
                            <p class="text-lg font-semibold text-gray-900 mt-1">₹{{ number_format($batch->fee_per_student, 2) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-600">Total Fees</p>
                            <p class="text-lg font-semibold text-[#00bde0] mt-1">₹{{ number_format($batch->total_fees, 2) }}</p>
                        </div>
                    </div>

                    @if($batch->status === 'approved' && $batch->payment_verified)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-green-800">
                                <strong>Payment Verified:</strong>
                                @if($batch->payment_type === 'online') Online @elseif($batch->payment_type === 'offline') Offline ({{ $batch->offline_payment_method ?? 'N/A' }}) @else N/A @endif
                                - Transaction ID: {{ $batch->transaction_id ?? 'N/A' }} - 
                                Date: {{ optional($batch->payment_date)->format('M d, Y') ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-green-800 mt-1">
                                Verified by {{ $batch->verifier->firstname ?? 'N/A' }} {{ $batch->verifier->lastname ?? '' }} 
                                on {{ optional($batch->verified_at)->format('M d, Y h:i A') ?? 'N/A' }}
                            </p>
                        </div>
                    @endif

                    @if($batch->status === 'rejected' && $batch->notes)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-red-800">
                                <strong>Rejection Reason:</strong> {{ $batch->notes }}
                            </p>
                            <p class="text-sm text-red-800 mt-1">
                                Rejected by {{ $batch->verifier->firstname ?? 'N/A' }} {{ $batch->verifier->lastname ?? '' }} 
                                on {{ $batch->verified_at ? $batch->verified_at->format('M d, Y h:i A') : 'N/A' }}
                            </p>
                        </div>
                    @endif

                    <div class="flex gap-2">
                        <a href="{{ route('accountant.batches.show', $batch->batch_id) }}" 
                           class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                            View Details
                        </a>
                        
                        @if($batch->status === 'pending')
                            <a href="{{ route('accountant.batches.approve-form', $batch->batch_id) }}" 
                               class="bg-green-500 hover:bg-green-600 text-white font-medium px-4 py-2 rounded-lg transition">
                                Approve Payment
                            </a>
                            <a href="{{ route('accountant.batches.reject-form', $batch->batch_id) }}" 
                               class="bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2 rounded-lg transition">
                                Reject
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $batches->links() }}
        </div>
    @endif
</div>
@endsection
