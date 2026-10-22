@extends('layouts.app')

@section('title', 'Preview Bulk Upload')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ $semesterBatch->display_name ?? 'N/A' }} ({{ optional($semesterBatch->start_date)->format('d-m-Y') ?? 'N/A' }} - {{ optional($semesterBatch->end_date)->format('d-m-Y') ?? 'N/A' }})</h2>
        <p class="mt-1 text-sm text-gray-600">Review and edit student data before submission</p>
    </div>

    <!-- Summary Cards -->
    <div id="summary-cards" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Total Students Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Students</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totals['student_count'] }}</p>
                </div>
                <div class="bg-primary-50 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Fee Per Student Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Fee Per Student</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($totals['fee_per_student'], 2) }}</p>
                </div>
                <div class="bg-green-50 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Fees Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Fees</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($totals['total_fees'], 2) }}</p>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Info -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-bold text-blue-900 mb-2">Batch Details</h3>
            <p class="text-sm text-blue-900">
                <span class="font-medium">Semester Batch:</span> {{ $semesterBatch->display_name ?? 'N/A' }} ({{ optional($semesterBatch->start_date)->format('d-m-Y') ?? 'N/A' }} - {{ optional($semesterBatch->end_date)->format('d-m-Y') ?? 'N/A' }})
            </p>
        </div>

        @if(!empty($initiatorData))
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <h3 class="text-sm font-bold text-purple-900 mb-2">Initiator Details</h3>
            <div class="grid grid-cols-2 gap-2 text-sm text-purple-900">
                <div><span class="font-medium">Name:</span> {{ $initiatorData['first_name'] ?? '' }} {{ $initiatorData['last_name'] ?? '' }}</div>
                <div><span class="font-medium">Code:</span> {{ $initiatorData['initiator_code'] ?? 'N/A' }}</div>
                <div><span class="font-medium">School:</span> {{ $initiatorData['enroll_school_name'] ?? 'N/A' }}</div>
                <div><span class="font-medium">Honorarium:</span> ₹{{ number_format($initiatorData['honorarium'] ?? 0, 2) }}</div>
            </div>
        </div>
        @endif
    </div>

    <div id="error-banner-container">
    @if($hasErrors)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Validation Errors Found</h3>
                    <p class="mt-1 text-sm text-red-700">Please fix all validation errors before submitting for verification.</p>
                </div>
            </div>
        </div>
    @endif
    </div>

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Row</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Standard</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guardian Details</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fee</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($students as $student)
                        @php
                            $std = $standards->firstWhere('std_id', $student['data']['std_id']);
                        @endphp
                        <tr id="student-row-{{ $student['row_number'] }}" class="hover:bg-gray-50 {{ !empty($student['errors']) ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $student['row_number'] }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-900">
                                <div class="font-medium text-gray-900">{{ $student['data']['fname'] }} {{ $student['data']['mname'] }} {{ $student['data']['lname'] }}</div>
                                <div class="text-xs text-gray-500">BD: {{ $student['data']['birthdate'] ?? 'N/A' }} | Gen: {{ $student['data']['gender'] ?? 'N/A' }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $std->stdname ?? 'ID: '.$student['data']['std_id'] }}
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ $student['data']['guardian_name'] ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500 flex items-center gap-1 group">
                                    {{ $student['data']['guardian_relation'] ?? '' }} | 
                                    @if(!empty($student['data']['guardian_contact']))
                                        <x-phone-link :number="$student['data']['guardian_contact']" />
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-900">
                                <div class="truncate max-w-xs">{{ $student['data']['address'] ?? '' }}</div>
                                <div class="text-xs text-gray-500">{{ $student['data']['city_village'] ?? '' }} {{ $student['data']['pincode'] ?? '' }}</div>
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                ₹{{ number_format($student['calculated_fee'], 2) }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                @if(empty($student['errors']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Valid
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ count($student['errors']) }} Error(s)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                <button onclick="openEditModal(this)"
                                        data-row="{{ $student['row_number'] }}"
                                        data-fname="{{ $student['data']['fname'] ?? '' }}"
                                        data-mname="{{ $student['data']['mname'] ?? '' }}"
                                        data-lname="{{ $student['data']['lname'] ?? '' }}"
                                        data-birthdate="{{ $student['data']['birthdate'] ?? '' }}"
                                        data-gender="{{ $student['data']['gender'] ?? '' }}"
                                        data-email="{{ $student['data']['email'] ?? '' }}"
                                        data-whatsapp_no="{{ $student['data']['whatsapp_no'] ?? '' }}"
                                        data-guardian_fname="{{ $student['data']['guardian_fname'] ?? '' }}"
                                        data-guardian_mname="{{ $student['data']['guardian_mname'] ?? '' }}"
                                        data-guardian_lname="{{ $student['data']['guardian_lname'] ?? '' }}"
                                        data-guardian_contact="{{ $student['data']['guardian_contact'] ?? '' }}"
                                        data-address="{{ $student['data']['address'] ?? '' }}"
                                        data-city_village="{{ $student['data']['city_village'] ?? '' }}"
                                        data-pincode="{{ $student['data']['pincode'] ?? '' }}"
                                        data-std_id="{{ $student['data']['std_id'] ?? '' }}"
                                        class="text-primary-500 hover:text-primary-600 font-medium">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @if(!empty($student['errors']) || !empty($student['data']['school_address']))
                            <tr id="student-error-row-{{ $student['row_number'] }}" class="bg-gray-50">
                                <td colspan="9" class="px-4 py-2 border-t border-gray-100">
                                    @if(!empty($student['data']['school_address']) || !empty($student['data']['geo_tagging_school_address']))
                                    <div class="text-xs text-gray-600 mb-1">
                                        <span class="font-bold">Address:</span> {{ $student['data']['school_address'] ?? 'N/A' }} 
                                        @if(!empty($student['data']['geo_tagging_school_address']))
                                            | <span class="font-bold">Geo:</span> {{ $student['data']['geo_tagging_school_address'] }}
                                        @endif
                                    </div>
                                    @endif

                                    @if(!empty($student['errors']))
                                    <div class="text-sm text-red-700">
                                        <span class="font-medium">Errors:</span>
                                        <ul class="list-disc list-inside ml-4">
                                            @foreach($student['errors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <div id="submit-action-container" class="mt-6 flex items-center justify-between">
        <a href="{{ route('students.bulk-upload.form') }}"
           class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
            Back to Upload
        </a>
        
        <form action="{{ route('students.bulk-upload.submit') }}" method="POST">
            @csrf
            <button type="submit"
                    {{ $hasErrors ? 'disabled' : '' }}
                    class="px-6 py-2 {{ $hasErrors ? 'bg-gray-400 cursor-not-allowed' : 'bg-primary-500 hover:bg-primary-600' }} text-white font-medium rounded-lg transition">
                Submit for Verification
            </button>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/30 backdrop-blur-[2px] transition-opacity" onclick="closeEditModal()"></div>

        <!-- Center content -->
        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
            <form id="editForm" onsubmit="submitEditForm(event)">
                @csrf
                <input type="hidden" id="edit_row_number" name="row_number">
                
                <div class="bg-gradient-to-r from-[#00bde0] to-[#01a7c7] px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-extrabold text-white" id="modal-title">
                        Edit Student Info <span class="text-white/80 font-medium text-base ml-1">(Row <span id="display_row_number"></span>)</span>
                    </h3>
                    <button type="button" class="text-white hover:bg-white/20 p-1 rounded-lg transition-colors focus:outline-none" onclick="closeEditModal()">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-gray-50 px-6 py-6 max-h-[60vh] overflow-y-auto space-y-6">
                    <!-- Student Details Section -->
                    <div>
                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Student Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">First Name *</label>
                                <input type="text" id="edit_fname" name="fname" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Middle Name *</label>
                                <input type="text" id="edit_mname" name="mname" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Last Name *</label>
                                <input type="text" id="edit_lname" name="lname" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Birthdate</label>
                                <input type="text" id="edit_birthdate" name="birthdate" class="date-picker w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm" placeholder="DD/MM/YYYY">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Gender</label>
                                <select id="edit_gender" name="gender" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                    <option value="O">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Standard</label>
                                <select id="edit_std_id" name="std_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                                    @foreach($standards as $std)
                                        <option value="{{ $std->std_id }}">{{ $std->stdname }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Email *</label>
                                <input type="email" id="edit_email" name="email" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">WhatsApp No.</label>
                                <input type="text" id="edit_whatsapp_no" name="whatsapp_no" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Guardian Details Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Guardian Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Guardian First Name</label>
                                <input type="text" id="edit_guardian_fname" name="guardian_fname" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Guardian Middle Name</label>
                                <input type="text" id="edit_guardian_mname" name="guardian_mname" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Guardian Last Name</label>
                                <input type="text" id="edit_guardian_lname" name="guardian_lname" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Guardian Contact No.</label>
                                <input type="text" id="edit_guardian_contact" name="guardian_contact" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                        </div>
                    </div>

                    <!-- Location Details Section -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Address & Location</h4>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Residential Address</label>
                            <textarea id="edit_address" name="address" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">City / Village</label>
                                <input type="text" id="edit_city_village" name="city_village" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Pincode</label>
                                <input type="text" id="edit_pincode" name="pincode" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring focus:ring-primary-200 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3">
                    <button type="button" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg text-sm transition" onclick="closeEditModal()">
                        Cancel
                    </button>
                    <button type="submit" id="saveChangesBtn" class="px-5 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg text-sm transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Razorpay Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

@push('scripts')
<script>
    function openEditModal(btn) {
        const row = btn.getAttribute('data-row');
        document.getElementById('edit_row_number').value = row;
        document.getElementById('display_row_number').innerText = row;
        
        // Populating fields
        document.getElementById('edit_fname').value = btn.getAttribute('data-fname');
        document.getElementById('edit_mname').value = btn.getAttribute('data-mname');
        document.getElementById('edit_lname').value = btn.getAttribute('data-lname');
        
        // Handle birthdate format (convert Y-m-d to input value)
        let birthdate = btn.getAttribute('data-birthdate');
        let rawDate = birthdate ? birthdate.split(' ')[0] : '';
        const birthdateInput = document.getElementById('edit_birthdate');
        if (birthdateInput._flatpickr) {
            birthdateInput._flatpickr.setDate(rawDate, true);
        } else {
            birthdateInput.value = rawDate;
        }
        
        document.getElementById('edit_gender').value = btn.getAttribute('data-gender') || 'M';
        document.getElementById('edit_std_id').value = btn.getAttribute('data-std_id');
        document.getElementById('edit_email').value = btn.getAttribute('data-email');
        document.getElementById('edit_whatsapp_no').value = btn.getAttribute('data-whatsapp_no');
        
        document.getElementById('edit_guardian_fname').value = btn.getAttribute('data-guardian_fname');
        document.getElementById('edit_guardian_mname').value = btn.getAttribute('data-guardian_mname');
        document.getElementById('edit_guardian_lname').value = btn.getAttribute('data-guardian_lname');
        document.getElementById('edit_guardian_contact').value = btn.getAttribute('data-guardian_contact');
        
        document.getElementById('edit_address').value = btn.getAttribute('data-address');
        document.getElementById('edit_city_village').value = btn.getAttribute('data-city_village');
        document.getElementById('edit_pincode').value = btn.getAttribute('data-pincode');
        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.add('hidden');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function submitEditForm(e) {
        e.preventDefault();
        
        const saveBtn = document.getElementById('saveChangesBtn');
        saveBtn.disabled = true;
        saveBtn.innerText = 'Saving...';
        
        const formData = new FormData(document.getElementById('editForm'));
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });

        fetch("{{ route('students.bulk-upload.update-preview') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                // Fetch the updated page silently to get fresh HTML components
                fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        const rowNum = data.row_number;
                        
                        // 1. Replace the specific student row
                        const oldRow = document.getElementById('student-row-' + rowNum);
                        const newRow = doc.getElementById('student-row-' + rowNum);
                        if (oldRow && newRow) oldRow.outerHTML = newRow.outerHTML;

                        // 2. Replace or remove the error/address row below it
                        const oldErrRow = document.getElementById('student-error-row-' + rowNum);
                        const newErrRow = doc.getElementById('student-error-row-' + rowNum);
                        if (oldErrRow && newErrRow) {
                            oldErrRow.outerHTML = newErrRow.outerHTML;
                        } else if (oldErrRow && !newErrRow) {
                            oldErrRow.remove(); // Error was fixed!
                        } else if (!oldErrRow && newErrRow) {
                            document.getElementById('student-row-' + rowNum).insertAdjacentHTML('afterend', newErrRow.outerHTML);
                        }

                        // 3. Update summary cards (Total students, Total fees)
                        const oldSummary = document.getElementById('summary-cards');
                        const newSummary = doc.getElementById('summary-cards');
                        if (oldSummary && newSummary) oldSummary.innerHTML = newSummary.innerHTML;

                        // 4. Update the red top banner if errors exist/don't exist
                        const oldBanner = document.getElementById('error-banner-container');
                        const newBanner = doc.getElementById('error-banner-container');
                        if (oldBanner && newBanner) oldBanner.innerHTML = newBanner.innerHTML;

                        // 5. Update the submit button enabled/disabled state
                        const oldAction = document.getElementById('submit-action-container');
                        const newAction = doc.getElementById('submit-action-container');
                        if (oldAction && newAction) {
                            oldAction.innerHTML = newAction.innerHTML;
                            initPaymentLogic(res.totals.total_fees);
                        }

                        // Re-enable save button and close modal
                        saveBtn.disabled = false;
                        saveBtn.innerText = 'Save Changes';
                        closeEditModal();
                    })
                    .catch(err => {
                        console.error('Failed to fetch updated DOM', err);
                        window.location.reload(); // Fallback
                    });
            } else {
                alert('Failed to update student.');
                saveBtn.disabled = false;
                saveBtn.innerText = 'Save Changes';
            }
        })
        .catch(err => {
            console.error(err);
            alert('An error occurred while saving.');
            saveBtn.disabled = false;
            saveBtn.innerText = 'Save Changes';
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const scrollPos = sessionStorage.getItem('bulkUploadScrollPosition');
        if (scrollPos) {
            window.scrollTo({ top: parseInt(scrollPos), behavior: 'instant' });
            sessionStorage.removeItem('bulkUploadScrollPosition');
        }

        initPaymentLogic({{ $totals['total_fees'] }});
    });

    function initPaymentLogic(totalFeesAmt) {
        const oldSubmitForm = document.querySelector('form[action="{{ route('students.bulk-upload.submit') }}"]');
        if (!oldSubmitForm) return;
        
        // Remove old event listeners by cloning
        const submitForm = oldSubmitForm.cloneNode(true);
        oldSubmitForm.parentNode.replaceChild(submitForm, oldSubmitForm);
        
        const submitBtn = submitForm.querySelector('button[type="submit"]');
        const paymentType = "{{ session('bulk_upload_payment_type') }}"; // 'online' or 'offline'
        const totalFees = parseFloat(totalFeesAmt);

        if (paymentType === 'online') {
            if (totalFees <= 0) {
                submitBtn.disabled = true;
                submitBtn.innerText = 'Online Payment not available (₹0.00)';
                submitBtn.classList.remove('bg-primary-500', 'hover:bg-primary-600');
                submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                
                // Add a small warning text
                const warningText = document.createElement('p');
                warningText.className = 'text-xs text-red-500 mt-1 text-right';
                warningText.innerText = 'No fee plans or batch fees were found. Online payment requires an amount > 0.';
                submitForm.appendChild(warningText);

                return;
            }

            // Change button text
            submitBtn.innerText = 'Pay & Submit (₹' + totalFees.toLocaleString() + ')';

            submitForm.addEventListener('submit', function (e) {
                e.preventDefault();
                
                submitBtn.disabled = true;
                submitBtn.innerText = 'Initializing Payment...';

                // Create Razorpay Order
                fetch("{{ route('students.bulk-upload.create-razorpay-order') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({})
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        submitBtn.disabled = false;
                        submitBtn.innerText = 'Pay & Submit';
                        return;
                    }

                    var options = {
                        "key": data.key,
                        "amount": data.amount,
                        "currency": data.currency,
                        "name": data.name,
                        "description": data.description,
                        "order_id": data.order_id,
                        "handler": function (response){
                            // Submit verification form
                            const verifyForm = document.createElement('form');
                            verifyForm.method = 'POST';
                            verifyForm.action = "{{ route('students.bulk-upload.verify-payment') }}";
                            
                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = "{{ csrf_token() }}";
                            verifyForm.appendChild(csrfToken);

                            const orderId = document.createElement('input');
                            orderId.type = 'hidden';
                            orderId.name = 'razorpay_order_id';
                            orderId.value = response.razorpay_order_id;
                            verifyForm.appendChild(orderId);

                            const paymentId = document.createElement('input');
                            paymentId.type = 'hidden';
                            paymentId.name = 'razorpay_payment_id';
                            paymentId.value = response.razorpay_payment_id;
                            verifyForm.appendChild(paymentId);
                            
                            const signature = document.createElement('input');
                            signature.type = 'hidden';
                            signature.name = 'razorpay_signature';
                            signature.value = response.razorpay_signature;
                            verifyForm.appendChild(signature);

                            document.body.appendChild(verifyForm);
                            verifyForm.submit();
                            
                            submitBtn.innerText = 'Verifying Payment...';
                        },
                        "prefill": {
                            "name": "{{ Auth::user()->name }}",
                            "email": "{{ Auth::user()->email }}",
                            "contact": "{{ Auth::user()->phone ?? '' }}"
                        },
                        "theme": {
                            "color": "#00bde0"
                        },
                        "modal": {
                            "ondismiss": function() {
                                submitBtn.disabled = false;
                                submitBtn.innerText = 'Pay & Submit';
                            }
                        }
                    };
                    var rzp1 = new Razorpay(options);
                    rzp1.open();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Pay & Submit';
                });
            });
        }
    }
</script>
@endpush
@endsection
