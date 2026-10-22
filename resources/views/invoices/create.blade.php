@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Generate Invoice</h1>
        <p class="text-gray-600 mt-1">Create a new invoice for a student or school</p>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form method="POST" action="{{ route('invoices.store') }}" id="invoiceForm">
            @csrf

            <!-- Invoice Type Selector -->
            <div class="mb-6 bg-gray-50 border border-gray-200 rounded-xl p-4">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Invoice Type</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-sm text-gray-700">
                        <input type="radio" name="invoice_type" value="student" {{ old('invoice_type', 'student') === 'student' ? 'checked' : '' }} onchange="toggleInvoiceType(this.value)" class="text-[#00bde0] focus:ring-[#00bde0]">
                        Student Registration Fee Invoice
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer font-medium text-sm text-gray-700">
                        <input type="radio" name="invoice_type" value="school" {{ old('invoice_type') === 'school' ? 'checked' : '' }} onchange="toggleInvoiceType(this.value)" class="text-[#00bde0] focus:ring-[#00bde0]">
                        School Custom Service Invoice
                    </label>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Group for Student Invoices -->
                <div id="student_fields_group">
                    <!-- Student Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Student <span class="text-red-500">*</span>
                        </label>
                        <select name="student_id" id="student_id_select" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('student_id') border-red-500 @enderror">
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->student_id }}" {{ old('student_id') == $student->student_id ? 'selected' : '' }}>
                                    {{ $student->fname }} {{ $student->mname }} {{ $student->lname }} 
                                    @if($student->school)
                                        - {{ $student->school->schoolname }}
                                    @endif
                                    @if($student->standard)
                                        - {{ $student->standard->stdname }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Semester Batch Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Semester Batch <span class="text-red-500">*</span>
                        </label>
                        <select name="semester_batch_id" id="semester_batch_id_select" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('semester_batch_id') border-red-500 @enderror">
                            <option value="">Select Semester Batch</option>
                            @foreach($semesterBatches as $batch)
                                <option value="{{ $batch->sembatches_id }}" {{ old('semester_batch_id') == $batch->sembatches_id ? 'selected' : '' }}>
                                    {{ $batch->display_name ?? 'N/A' }} ({{ $batch->start_date->format('d/m/Y') }} - {{ $batch->end_date->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('semester_batch_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Group for School Invoices -->
                <div id="school_fields_group" class="hidden">
                    <!-- School Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            School <span class="text-red-500">*</span>
                        </label>
                        <select name="school_id" id="school_id_select" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('school_id') border-red-500 @enderror">
                            <option value="">Select School</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->school_id }}" {{ old('school_id') == $school->school_id ? 'selected' : '' }}>
                                    {{ $school->schoolname }}
                                </option>
                            @endforeach
                        </select>
                        @error('school_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fine/Fee Items details dynamic rows -->
                    <div class="mb-4 bg-gray-50 p-4 border border-gray-200 rounded-xl">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-sm font-semibold text-gray-800">Invoice Items</h3>
                            <button type="button" onclick="addInvoiceItem()" class="text-xs font-semibold text-[#00bde0] hover:text-[#0099b8] flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Add Item
                            </button>
                        </div>
                        <div id="invoice_items_container" class="space-y-3">
                            <div class="flex gap-3 items-center invoice-item-row">
                                <div class="flex-1">
                                    <input type="text" name="items[0][description]" placeholder="Item Description (e.g. Bulk Admission Fee / Custom Charge)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-[#00bde0] item-desc">
                                </div>
                                <div class="w-36">
                                    <input type="number" name="items[0][amount]" placeholder="Amount (₹)" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-[#00bde0] item-amount">
                                </div>
                                <button type="button" onclick="removeInvoiceItem(this)" class="text-red-400 hover:text-red-600 shrink-0 delete-btn">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Due Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Due Date
                    </label>
                    <input type="text" name="due_date" value="{{ old('due_date', now()->addDays(30)->format('d/m/Y')) }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('due_date') border-red-500 @enderror" placeholder="DD/MM/YYYY">
                    @error('due_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status
                    </label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('status') border-red-500 @enderror">
                        <option value="DRAFT" {{ old('status', 'DRAFT') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                        <option value="ISSUED" {{ old('status') == 'ISSUED' ? 'selected' : '' }}>Issued</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Notes
                    </label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('invoices.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Generate Invoice
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let itemIndex = 1;

    function toggleInvoiceType(type) {
        const studentGroup = document.getElementById('student_fields_group');
        const schoolGroup = document.getElementById('school_fields_group');
        
        const studentIdSelect = document.getElementById('student_id_select');
        const semesterBatchSelect = document.getElementById('semester_batch_id_select');
        const schoolIdSelect = document.getElementById('school_id_select');

        if (type === 'school') {
            studentGroup.classList.add('hidden');
            schoolGroup.classList.remove('hidden');

            studentIdSelect.removeAttribute('required');
            semesterBatchSelect.removeAttribute('required');
            schoolIdSelect.setAttribute('required', 'required');

            // Ensure there is at least one item row and that its inputs are required
            document.querySelectorAll('.item-desc').forEach(el => el.setAttribute('required', 'required'));
            document.querySelectorAll('.item-amount').forEach(el => el.setAttribute('required', 'required'));
        } else {
            studentGroup.classList.remove('hidden');
            schoolGroup.classList.add('hidden');

            studentIdSelect.setAttribute('required', 'required');
            semesterBatchSelect.setAttribute('required', 'required');
            schoolIdSelect.removeAttribute('required');

            document.querySelectorAll('.item-desc').forEach(el => el.removeAttribute('required'));
            document.querySelectorAll('.item-amount').forEach(el => el.removeAttribute('required'));
        }
    }

    function addInvoiceItem() {
        const container = document.getElementById('invoice_items_container');
        const isSchool = document.querySelector('input[name="invoice_type"]:checked').value === 'school';
        const requiredAttr = isSchool ? 'required' : '';

        const newRow = document.createElement('div');
        newRow.className = 'flex gap-3 items-center invoice-item-row';
        newRow.innerHTML = `
            <div class="flex-1">
                <input type="text" name="items[\${itemIndex}][description]" \${requiredAttr} placeholder="Item Description" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-[#00bde0] item-desc">
            </div>
            <div class="w-36">
                <input type="number" name="items[\${itemIndex}][amount]" \${requiredAttr} placeholder="Amount (₹)" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-[#00bde0] item-amount">
            </div>
            <button type="button" onclick="removeInvoiceItem(this)" class="text-red-400 hover:text-red-600 shrink-0 delete-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        `;
        container.appendChild(newRow);
        itemIndex++;
    }

    function removeInvoiceItem(button) {
        const rows = document.querySelectorAll('.invoice-item-row');
        if (rows.length > 1) {
            button.closest('.invoice-item-row').remove();
        } else {
            alert('At least one invoice line item is required.');
        }
    }

    // Initialize toggle state on document load
    document.addEventListener('DOMContentLoaded', () => {
        const type = document.querySelector('input[name="invoice_type"]:checked').value;
        toggleInvoiceType(type);
    });
</script>
@endpush
