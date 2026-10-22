@extends('layouts.app')

@section('title', 'Batch Details')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Batch Details</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $batch->batch_code }}</p>
            </div>
            <a href="{{ route('batch-verification.index') }}"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Back to List
            </a>
        </div>

        <!-- Batch Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Batch Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">School</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $batch->school->schoolname ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Uploaded By</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $batch->uploader->fname }} {{ $batch->uploader->lname }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Upload Date</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $batch->upload_date->format('M d, Y h:i A') }}</p>
                </div>
                <!-- Metrics Cards -->
                <div class="col-span-full grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                    <!-- Total Students Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Students</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $batch->total_students }}</p>
                            </div>
                            <div class="bg-primary-50 p-2.5 rounded-lg">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Per Student Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Fee Per Student</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">₹{{ number_format($batch->fee_per_student, 2) }}</p>
                            </div>
                            <div class="bg-green-50 p-2.5 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Fees Card -->
                    <div class="bg-white rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Fees</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">₹{{ number_format($batch->total_fees, 2) }}</p>
                            </div>
                            <div class="bg-blue-50 p-2.5 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Payment Type</label>
                    <p class="mt-1">
                        @if($batch->payment_type === 'online')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Online
                                (Razorpay)</span>
                        @elseif($batch->payment_type === 'offline')
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Offline</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Not Set</span>
                        @endif
                    </p>
                </div>
                @if($batch->payment_type === 'offline')
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Offline Method</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $batch->offline_payment_method ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Payment Document</label>
                        @if(!empty($batch->payment_document_path))
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mt-1.5">
                                <a href="{{ asset('storage/' . $batch->payment_document_path) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg border border-cyan-100 bg-[#e0f7fc] text-[#008ca6] hover:bg-[#00bde0] hover:text-white transition-all shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    View Document
                                </a>
                                @if(!$batch->payment_verified)
                                    <form action="{{ route('batch-verification.upload-document', $batch->batch_id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 border border-gray-200 rounded-lg p-1 bg-white">
                                        @csrf
                                        <input type="file" name="payment_document" required class="block w-full text-xs text-gray-500 file:mr-2 file:py-0.5 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                                        <button type="submit" class="px-2.5 py-1 bg-gray-700 hover:bg-gray-800 text-white rounded text-[10px] font-bold transition-all shadow-sm">Update</button>
                                    </form>
                                @endif
                            </div>
                        @else
                            @if(!$batch->payment_verified)
                                <form action="{{ route('batch-verification.upload-document', $batch->batch_id) }}" method="POST" enctype="multipart/form-data" class="mt-1 flex items-center gap-2 border border-gray-200 rounded-lg p-1.5 bg-white max-w-sm">
                                    @csrf
                                    <input type="file" name="payment_document" required class="block w-full text-xs text-gray-500 file:mr-3 file:py-1 file:px-3.5 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-[#00bde0] file:text-white hover:file:bg-[#0099b8] transition-all">
                                    <button type="submit" class="px-3.5 py-1.5 bg-gradient-to-br from-[#00bde0] to-[#0099b8] text-white rounded-md text-xs font-bold hover:shadow transition-all whitespace-nowrap shrink-0">
                                        Upload Proof
                                    </button>
                                </form>
                            @else
                                <p class="mt-1 text-sm text-gray-550">No document uploaded</p>
                            @endif
                        @endif
                    </div>
                @endif
                <div>
                    <label class="block text-sm font-medium text-gray-500">Status</label>
                    <p class="mt-1">
                        @if($batch->payment_verified)
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending
                                Verification</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if(!$batch->payment_verified && $batch->payment_type === 'offline')
            <!-- Verification Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Verification</h3>

                <form method="POST" action="{{ route('batch-verification.verify', $batch->batch_id) }}" class="space-y-4" id="verificationForm">
                    @csrf
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="selected_students" id="selected_students_input">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Semester Batch Selection (includes dynamically resolved Fee Plan) -->
                        <div class="md:col-span-2">
                            <label for="sembatches_id" class="block text-sm font-medium text-gray-700 mb-1">
                                 Semester Batch & Fee Plan <span class="text-red-500">*</span>
                            </label>
                            <select name="sembatches_id" id="sembatches_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                @foreach($semesterBatches ?? [] as $sb)
                                    @php
                                        $matchedPlan = $sb->feePlans->where('school_id', $batch->school_id)->first()
                                                    ?? $sb->feePlans->whereNull('school_id')->first();
                                        $planName = $matchedPlan ? $matchedPlan->fee_name : 'No Fee Plan';
                                        $planAmt = $matchedPlan ? '₹' . number_format($matchedPlan->amount, 2) : 'N/A';
                                    @endphp
                                    <option value="{{ $sb->sembatches_id }}" {{ old('sembatches_id', $batch->sembatches_id) == $sb->sembatches_id ? 'selected' : '' }}>
                                        {{ $sb->display_name ?? 'N/A' }} ({{ $sb->start_date ? $sb->start_date->format('d/m/Y') : '' }} - {{ $sb->end_date ? $sb->end_date->format('d/m/Y') : '' }}) — Plan: {{ $planName }} ({{ $planAmt }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="transaction_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Transaction ID <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="transaction_id" id="transaction_id" required
                                placeholder="Enter transaction reference number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        </div>

                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name"
                                placeholder="Optional: Bank name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        </div>

                        <div>
                            <label for="payment_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Payment Date & Time <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="payment_date" id="payment_date" required
                                value="{{ now()->format('d/m/Y') }}"
                                class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
                        </div>

                        <div>
                            <label for="amount_paid" class="block text-sm font-medium text-gray-700 mb-1">
                                Amount Received (₹) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="amount_paid" id="amount_paid" required step="0.01"
                                    min="0" max="{{ $batch->total_fees - $batch->paid_amount }}"
                                    value="{{ $batch->total_fees - $batch->paid_amount }}"
                                    class="w-full pl-3 pr-40 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 pointer-events-none">
                                    / {{ number_format($batch->total_fees, 2) }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-blue-600" id="calculation_info">
                                Covers approx. <span id="student_count_calc">0</span> students
                            </p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="verified_by" class="block text-sm font-medium text-gray-700 mb-1">
                                Verified By
                            </label>
                            <input type="text" id="verified_by" readonly
                                value="{{ Auth::user()->fname }} {{ Auth::user()->lname }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 cursor-not-allowed">
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button type="button" onclick="showRejectForm()"
                            class="px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors">
                            Reject
                        </button>
                        <button type="submit" onclick="prepareSubmission(event)"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            Approve & Enroll Selected
                        </button>
                    </div>
                </form>
            </div>
        @elseif($batch->payment_verified)
            <!-- Verification Details -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-900 mb-4">Payment Verified</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-green-700">Transaction ID</label>
                        <p class="mt-1 text-sm text-green-900">{{ $batch->transaction_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-green-700">Payment Date</label>
                        <p class="mt-1 text-sm text-green-900">
                            {{ $batch->payment_date ? $batch->payment_date->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-green-700">Verified By</label>
                        <p class="mt-1 text-sm text-green-900">{{ $batch->verifier->fname ?? 'System' }}
                            {{ $batch->verifier->lname ?? '' }}
                        </p>
                    </div>
                    @if($batch->semesterBatch)
                    <div>
                        <label class="block text-sm font-medium text-green-700">Semester Batch</label>
                        <p class="mt-1 text-sm text-green-900">{{ $batch->semesterBatch->display_name ?? 'N/A' }}</p>
                    </div>
                    @endif
                    @if($batch->feePlan)
                    <div>
                        <label class="block text-sm font-medium text-green-700">Fee Plan</label>
                        <p class="mt-1 text-sm text-green-900">{{ $batch->feePlan->fee_name ?? 'N/A' }}</p>
                    </div>
                    @endif

                </div>
            </div>
        @endif

        <!-- Students List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Students ({{ $batch->total_students }})</h3>
                <div class="text-sm text-gray-600">
                    Already Paid: ₹{{ number_format($batch->paid_amount, 2) }}
                    <span class="mx-2">|</span>
                    Selected: <span id="selected_count_display" class="font-bold">0</span>
                </div>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            @if(!$batch->payment_verified && $batch->payment_type === 'offline')
                                <input type="checkbox" id="select_all" onclick="toggleSelectAll()"
                                    class="rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]">
                            @else
                                #
                            @endif
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Standard</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($students as $index => $student)
                        @php
                            $data = $student->student_data;
                            if (is_string($data)) {
                                $data = json_decode($data, true);
                            }
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if(!$student->is_registered && !$batch->payment_verified && $batch->payment_type === 'offline')
                                    <input type="checkbox" name="students[]" value="{{ $student->pending_id }}"
                                        data-fee="{{ $student->calculated_fee }}"
                                        class="student-checkbox rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]"
                                        onclick="updateSelectionCount()">
                                @else
                                    {{ $students->firstItem() + $index }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $data['fname'] ?? '' }} {{ $data['lname'] ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $data['standard_name'] ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹{{ number_format($student->calculated_fee, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($student->is_registered)
                                     <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Registered
                                    </span>
                                @elseif($batch->payment_type === 'online' && $batch->payment_verified)
                                     <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        Verified
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                No students found in this batch
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
            <div class="mt-4">
                {{ $students->links() }}
            </div>
        @endif

        @if($batch->initiator)
            <!-- Initiator Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Initiator Information
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Profile details -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Name</label>
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $batch->initiator->first_name }} {{ $batch->initiator->last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Code</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $batch->initiator->initiator_code }}</p>
                        </div>
                    </div>

                    <!-- Contact details -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</label>
                            <div class="mt-1 flex items-center gap-2 group">
                                <x-phone-link :number="$batch->initiator->contact_no" class="text-sm text-gray-900 hover:text-[#00bde0] transition-colors" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $batch->initiator->email }}</p>
                        </div>
                    </div>

                    <!-- Enrollment details -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">School Enrolled</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $batch->initiator->enroll_school_name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Total Students</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $batch->initiator->total_enroll_student }}</p>
                        </div>
                    </div>

                    <!-- Financial details in a highlighted sub-box -->
                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-100 col-span-1 md:col-span-2 lg:col-span-1 h-full flex flex-col justify-center space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-purple-700 uppercase tracking-wider">Honorarium</label>
                            <p class="mt-1 text-lg font-bold text-purple-900">₹{{ number_format($batch->initiator->honorarium, 2) }}</p>
                        </div>
                        <div class="pt-3 border-t border-purple-200">
                            <label class="block text-xs font-medium text-purple-700 uppercase tracking-wider">Payment Pending</label>
                            <p class="mt-1 text-sm font-semibold text-purple-900">₹{{ number_format($batch->initiator->payment_pending, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        const feePerStudent = {{ $batch->fee_per_student > 0 ? $batch->fee_per_student : '0' }};
        const totalFees = parseFloat('{{ $batch->total_fees }}') || 0;
        const paidSoFar = parseFloat('{{ $batch->paid_amount }}') || 0;

        // Initial setup
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount_paid');
            if(amountInput) {
                amountInput.addEventListener('input', updateCalculatedStudents);
                updateCalculatedStudents(); // Run once on load
            }
        });

        function updateCalculatedStudents() {
            const amount = parseFloat(document.getElementById('amount_paid').value) || 0;
            const checkboxes = document.querySelectorAll('.student-checkbox');
            let currentSum = 0;
            let count = 0;
            
            checkboxes.forEach((cb) => {
                const fee = parseFloat(cb.getAttribute('data-fee') || 0);
                if (currentSum + fee <= amount + 0.01) { // 0.01 for float tolerance
                    cb.checked = true;
                    currentSum += fee;
                    count++;
                } else {
                    cb.checked = false;
                }
            });
            
            document.getElementById('student_count_calc').innerText = count;
            updateSelectionCount();
        }

        function toggleSelectAll() {
            const selectAllData = document.getElementById('select_all').checked;
            document.querySelectorAll('.student-checkbox').forEach(cb => {
                cb.checked = selectAllData;
            });
            updateSelectionCount();
        }

        function updateSelectionCount() {
            const selected = document.querySelectorAll('.student-checkbox:checked').length;
            document.getElementById('selected_count_display').innerText = selected;
        }

        function prepareSubmission(event) {
            const selected = [];
            document.querySelectorAll('.student-checkbox:checked').forEach(cb => {
                selected.push(cb.value);
            });
            
            if (selected.length === 0) {
                alert('Please select at least one student to approve.');
                event.preventDefault();
                return;
            }
            
            const currentAmount = parseFloat(document.getElementById('amount_paid').value) || 0;
            const totalAvailable = paidSoFar + currentAmount;
            
            let totalSelectedFee = 0;
            document.querySelectorAll('.student-checkbox:checked').forEach(cb => {
                totalSelectedFee += parseFloat(cb.getAttribute('data-fee') || 0);
            });
            
            // Allow if total payment (previous + current) completes the batch
            if (totalAvailable >= (totalFees - 1)) {
                 document.getElementById('selected_students_input').value = JSON.stringify(selected);
                 return;
            }
            
            // Otherwise, validate that current payment covers selected students
            if (totalSelectedFee > currentAmount + 1) { 
               alert('Selected students fee (₹' + totalSelectedFee + ') exceeds the paid amount (₹' + currentAmount + '). Please deselect some students or increase the amount.');
               event.preventDefault();
               return;
            }

            document.getElementById('selected_students_input').value = JSON.stringify(selected);
        }
    </script>
    @endpush

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Reject Batch</h3>
                <button onclick="closeRejectForm()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('batch-verification.verify', $batch->batch_id) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="reject">

                <div>
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason</label>
                    <textarea name="reason" id="reason" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRejectForm()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Reject Batch
                    </button>
                </div>
            </form>
        </div>
    </div>



    @push('scripts')
        <script>
            function showRejectForm() {
                document.getElementById('rejectModal').classList.remove('hidden');
            }

            function closeRejectForm() {
                document.getElementById('rejectModal').classList.add('hidden');
            }

            document.getElementById('rejectModal')?.addEventListener('click', function (e) {
                if (e.target === this) {
                    closeRejectForm();
                }
            });
        </script>
    @endpush
@endsection