@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Batch Details: {{ $batch->batch_code }}</h1>
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

    <div class="mb-4">
        <a href="{{ route('accountant.batches.index') }}" class="text-[#00bde0] hover:text-[#0099b8]">
            ← Back to Batch List
        </a>
    </div>

    <!-- Batch Summary -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Batch Summary</h2>
        
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
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-green-900 mb-2">Payment Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-green-800">
                    <div>
                        <strong>Payment Mode:</strong> {{ $batch->paymentMode->paymodename ?? 'N/A' }}
                    </div>
                    <div>
                        <strong>Transaction ID:</strong> {{ $batch->transaction_id }}
                    </div>
                    <div>
                        <strong>Payment Date:</strong> {{ $batch->payment_date ? $batch->payment_date->format('M d, Y') : 'N/A' }}
                    </div>
                    <div>
                        <strong>Verified By:</strong> {{ $batch->verifier->firstname ?? 'N/A' }} {{ $batch->verifier->lastname ?? '' }}
                    </div>
                    <div>
                        <strong>Verified At:</strong> {{ $batch->verified_at ? $batch->verified_at->format('M d, Y h:i A') : 'N/A' }}
                    </div>
                </div>
            </div>
        @endif

        @if($batch->status === 'rejected' && $batch->notes)
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-red-900 mb-2">Rejection Information</h3>
                <p class="text-sm text-red-800 mb-2">
                    <strong>Reason:</strong> {{ $batch->notes }}
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-red-800">
                    <div>
                        <strong>Rejected By:</strong> {{ $batch->verifier->firstname ?? 'N/A' }} {{ $batch->verifier->lastname ?? '' }}
                    </div>
                    <div>
                        <strong>Rejected At:</strong> {{ $batch->verified_at ? $batch->verified_at->format('M d, Y h:i A') : 'N/A' }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Action Buttons -->
    @if($batch->status === 'pending')
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
            <div class="flex gap-2">
                <a href="{{ route('accountant.batches.approve-form', $batch->batch_id) }}" 
                   class="bg-green-500 hover:bg-green-600 text-white font-medium px-6 py-2 rounded-lg transition">
                    Approve Payment
                </a>
                <a href="{{ route('accountant.batches.reject-form', $batch->batch_id) }}" 
                   class="bg-red-500 hover:bg-red-600 text-white font-medium px-6 py-2 rounded-lg transition">
                    Reject Batch
                </a>
            </div>
        </div>
    @endif

    <!-- Student List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Students in Batch ({{ $batch->pendingStudents->count() }})</h2>
        
        @if($batch->pendingStudents->isEmpty())
            <p class="text-gray-500 text-center py-8">No students found in this batch.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Standard</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($batch->pendingStudents as $index => $pendingStudent)
                            @php
                                $studentData = is_array($pendingStudent->student_data) 
                                    ? $pendingStudent->student_data 
                                    : json_decode($pendingStudent->student_data, true);
                                $hasErrors = !empty($pendingStudent->validation_errors);
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $hasErrors ? 'bg-red-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $studentData['firstname'] ?? '' }} 
                                    {{ $studentData['middlename'] ?? '' }} 
                                    {{ $studentData['lastname'] ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $studentData['email'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $studentData['studenttype_id'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $studentData['standard_id'] ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    ₹{{ number_format($pendingStudent->calculated_fee, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($hasErrors)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Has Errors
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Valid
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @if($hasErrors)
                                <tr class="bg-red-50">
                                    <td colspan="7" class="px-6 py-2">
                                        <div class="text-xs text-red-800">
                                            <strong>Validation Errors:</strong>
                                            @php
                                                $errors = is_array($pendingStudent->validation_errors) 
                                                    ? $pendingStudent->validation_errors 
                                                    : json_decode($pendingStudent->validation_errors, true);
                                            @endphp
                                            @if(is_array($errors))
                                                <ul class="list-disc list-inside mt-1">
                                                    @foreach($errors as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
