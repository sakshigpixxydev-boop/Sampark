@extends('layouts.app')

@section('title', 'Bulk Upload Students')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Bulk Upload Students</h2>
                <p class="mt-1 text-sm text-gray-600">Upload multiple students via CSV file</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('students.bulk-upload.physical-form') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Download Form
                </a>
                <a href="{{ route('students.bulk-upload.sample') }}"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download Sample Excel
                </a>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-medium text-blue-900 mb-2">CSV Upload Instructions</h3>
            <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                <li>Maximum 500 students per upload</li>
                <li>Required columns as per new 16-column format:
                    Sr no, First Name, Middle Name, Surname/Last Name, Birthdate, Email, Standard (Optional), Medium of Education, Education Board, Student Gender, SANKALP Gurdian Name, Gurdian Relation with Student, SANKALP Gurdian Contact No., Student Address, City Village, Pincode
                </li>
                <li>School details and Initiator details will be automatically fetched based on your account.</li>
                <li>Standard will be automatically assigned from the selected Semester Batch if not provided in the sheet.</li>
                <li>Email is now REQUIRED for every student in the sheet.</li>
                <li>Enrolment No will be automatically generated as per your school/profile.</li>
                <li>Date format: DD-MM-YYYY (e.g., 15-01-2000)</li>
                <li>Gender values: Male, Female, or Other (M/F/O)</li>
            </ul>
        </div>

        <!-- Upload Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Upload Errors</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('students.bulk-upload.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Semester Batch Selection -->
                    <div>
                        <label for="sembatches_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Semester Batch <span class="text-red-500">*</span>
                        </label>
                        <select name="sembatches_id" id="sembatches_id" required
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Semester Batch</option>
                            @foreach($semesterBatches as $batch)
                                <option value="{{ $batch->sembatches_id }}" {{ old('sembatches_id') == $batch->sembatches_id ? 'selected' : '' }}>
                                    {{ $batch->display_name ?? 'N/A' }} ({{ optional($batch->start_date)->format('d-m-Y') ?? 'N/A' }} - {{ optional($batch->end_date)->format('d-m-Y') ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(!empty($schools))
                        <!-- School Selection (For Admin/HQ Users) -->
                        <div>
                            <label for="school_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Target School <span class="text-red-500">*</span>
                            </label>
                            <select name="school_id" id="school_id" required
                                class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="">Select School</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->school_id }}" {{ old('school_id') == $school->school_id ? 'selected' : '' }}>
                                        {{ $school->schoolname }}
                                        @if($school->taluka && $school->taluka->district)
                                            ({{ $school->taluka->district->dname }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif


                    <!-- Fee Plan Selection (Optional) -->
                    <div class="md:col-span-2">
                        <label for="fee_plan_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Specific Fee Plan (Optional)
                        </label>
                        <select name="fee_plan_id" id="fee_plan_id"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:bg-gray-100 disabled:text-gray-500"
                            disabled>
                            <option value="">Auto-calculate based on student data</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Select a semester batch to load available fee plans.</p>
                    </div>

                    <!-- Payment Type -->
                    <div>
                        <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Payment Type <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_type" id="payment_type" required
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="offline" {{ old('payment_type') == 'offline' ? 'selected' : '' }}>Offline Payment
                            </option>
                            <option value="online" {{ old('payment_type') == 'online' ? 'selected' : '' }}>Online Payment
                                (Razorpay)</option>
                        </select>
                    </div>

                    <!-- Offline Payment Method (for Offline Payment Type) -->
                    <div id="offline_payment_method_container">
                        <label for="offline_payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                            Offline Payment Method <span class="text-red-500">*</span>
                        </label>
                        <select name="offline_payment_method" id="offline_payment_method"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Select Method</option>
                            <option value="bank_transfer" {{ old('offline_payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="cheque" {{ old('offline_payment_method') == 'cheque' ? 'selected' : '' }}>Cheque
                            </option>
                            <option value="other" {{ old('offline_payment_method') == 'other' ? 'selected' : '' }}>Other
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Select the offline payment method used</p>
                    </div>



                    <!-- File Upload -->
                    <div class="md:col-span-2">
                        <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-1">
                            Upload File (CSV or Excel) <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-primary-500 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48" aria-hidden="true">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="csv_file"
                                        class="relative cursor-pointer bg-white rounded-md font-medium text-primary-500 hover:text-primary-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                        <span>Upload a file</span>
                                        <input id="csv_file" name="csv_file" type="file" accept=".csv, .xlsx, .xls"
                                            class="sr-only" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">CSV/Excel up to 10MB</p>
                                <p id="file-name" class="text-sm text-gray-900 font-medium mt-2"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('students.index') }}"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg transition">
                        Upload and Preview
                    </button>
                </div>
            </form>
        </div>

        <!-- Offline Payment Confirmation Modal -->
        <div id="confirmationModal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 text-center mt-4">Confirm Offline Payment</h3>
                    <div class="mt-4 px-4 py-3 bg-gray-50 rounded-lg">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Students:</span>
                                <span id="modal_total_students" class="font-semibold text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Standard:</span>
                                <span id="modal_standard" class="font-semibold text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Payment Method:</span>
                                <span id="modal_payment_method" class="font-semibold text-gray-900">-</span>
                            </div>
                            <div class="flex justify-between border-t pt-2 mt-2">
                                <span class="text-gray-600">Estimated Total:</span>
                                <span id="modal_total_amount" class="font-bold text-primary-500 text-lg">-</span>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3 text-center">
                        Please verify the details before proceeding. This will create a payment batch pending verification.
                    </p>
                    <div class="flex gap-3 mt-5">
                        <button id="cancelBtn" type="button"
                            class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                            Cancel
                        </button>
                        <button id="confirmBtn" type="button"
                            class="flex-1 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg transition">
                            Confirm & Upload
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Initialize TomSelect for Standard dropdown to preserve DB order

                // File upload name display
                const fileInput = document.getElementById('csv_file');
                const fileNameDisplay = document.getElementById('file-name');

                fileInput.addEventListener('change', function () {
                    if (this.files && this.files.length > 0) {
                        fileNameDisplay.textContent = this.files[0].name;
                    } else {
                        fileNameDisplay.textContent = '';
                    }
                });

                // Payment type toggle
                const paymentTypeSelect = document.getElementById('payment_type');
                const offlinePaymentMethodContainer = document.getElementById('offline_payment_method_container');
                const offlinePaymentMethodSelect = document.getElementById('offline_payment_method');

                function togglePaymentMode() {
                    if (paymentTypeSelect.value === 'online') {
                        offlinePaymentMethodContainer.style.display = 'none';
                        offlinePaymentMethodSelect.required = false;
                    } else {
                        offlinePaymentMethodContainer.style.display = 'block';
                        offlinePaymentMethodSelect.required = true;
                    }
                }

                paymentTypeSelect.addEventListener('change', togglePaymentMode);

                // Helper to attach listeners to both native and TomSelect inputs
                function attachChangeListener(elementId, callback) {
                    const el = document.getElementById(elementId);
                    if (!el) return;

                    if (el.classList.contains('searchable-select')) {
                        // Wait for TomSelect instance to be ready
                        const checkInterval = setInterval(() => {
                            if (el.tomselect) {
                                clearInterval(checkInterval);
                                el.tomselect.on('change', (value) => {
                                    callback();
                                });
                            }
                        }, 100);
                    } else {
                        el.addEventListener('change', callback);
                    }
                }

                // Attach listeners
                attachChangeListener('payment_type', togglePaymentMode);
                togglePaymentMode(); // Initial state

                // Fee Plan loading
                const semBatchSelect = document.getElementById('sembatches_id');
                const feePlanSelect = document.getElementById('fee_plan_id');

                function loadFeePlans() {
                    const semBatchId = semBatchSelect.value;

                    if (semBatchId) {
                        feePlanSelect.disabled = true;
                        feePlanSelect.innerHTML = '<option value="">Loading...</option>';

                        fetch(`{{ route('students.bulk-upload.fee-plans') }}?semester_batch_id=${semBatchId}`)
                            .then(response => response.json())
                            .then(data => {
                                if (feePlanSelect.tomselect) {
                                    feePlanSelect.tomselect.clear();
                                    feePlanSelect.tomselect.clearOptions();
                                    feePlanSelect.tomselect.addOption({ value: '', text: 'Auto-calculate based on student data' });
                                    feePlanSelect.tomselect.addItem('');
                                } else {
                                    feePlanSelect.innerHTML = '<option value="">Auto-calculate based on student data</option>';
                                }

                                if (data.length > 0) {
                                    data.forEach(plan => {
                                        if (feePlanSelect.tomselect) {
                                            feePlanSelect.tomselect.addOption({ value: plan.fee_plan_id, text: `${plan.fee_name} (₹${plan.amount})` });
                                        } else {
                                            const option = new Option(`${plan.fee_name} (₹${plan.amount})`, plan.fee_plan_id);
                                            feePlanSelect.add(option);
                                        }
                                    });
                                    if (feePlanSelect.tomselect) {
                                        feePlanSelect.tomselect.enable();
                                    } else {
                                        feePlanSelect.disabled = false;
                                    }
                                } else {
                                    if (feePlanSelect.tomselect) {
                                        feePlanSelect.tomselect.addOption({ value: '', text: 'No specific fee plans found' });
                                    } else {
                                        const option = new Option('No specific fee plans found', '');
                                        feePlanSelect.add(option);
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error loading fee plans:', error);
                                if (feePlanSelect.tomselect) {
                                    feePlanSelect.tomselect.clearOptions();
                                    feePlanSelect.tomselect.addOption({ value: '', text: 'Error loading fee plans' });
                                    feePlanSelect.tomselect.addItem('');
                                } else {
                                    feePlanSelect.innerHTML = '<option value="">Error loading fee plans</option>';
                                }
                            });
                    } else {
                        if (feePlanSelect.tomselect) {
                            feePlanSelect.tomselect.clear();
                            feePlanSelect.tomselect.clearOptions();
                            feePlanSelect.tomselect.addOption({ value: '', text: 'Auto-calculate based on student data' });
                            feePlanSelect.tomselect.addItem('');
                            feePlanSelect.tomselect.disable();
                        } else {
                            feePlanSelect.disabled = true;
                            feePlanSelect.innerHTML = '<option value="">Auto-calculate based on student data</option>';
                        }
                    }
                }

                attachChangeListener('sembatches_id', loadFeePlans);

                // Confirmation Modal Logic
                const uploadForm = document.querySelector('form[action="{{ route('students.bulk-upload.process') }}"]');
                const confirmationModal = document.getElementById('confirmationModal');
                const cancelBtn = document.getElementById('cancelBtn');
                const confirmBtn = document.getElementById('confirmBtn');
                let formSubmitAllowed = false;

                uploadForm.addEventListener('submit', function (e) {
                    // Only show modal for offline payments
                    if (paymentTypeSelect.value === 'offline' && !formSubmitAllowed) {
                        e.preventDefault();

                        // Validate required fields before showing modal
                        if (!uploadForm.checkValidity()) {
                            uploadForm.reportValidity();
                            return;
                        }

                        // Get form data
                        const fileInput = document.getElementById('csv_file');
                        const paymentMethodText = offlinePaymentMethodSelect.options[offlinePaymentMethodSelect.selectedIndex]?.text || 'Not selected';

                        document.getElementById('modal_total_students').textContent = 'From CSV file';
                        document.getElementById('modal_standard').textContent = 'From CSV/Batch';
                        document.getElementById('modal_payment_method').textContent = paymentMethodText;
                        document.getElementById('modal_total_amount').textContent = 'Will be calculated';

                        // Show modal
                        confirmationModal.classList.remove('hidden');
                    }
                });

                // Cancel button - hide modal
                cancelBtn.addEventListener('click', function () {
                    confirmationModal.classList.add('hidden');
                    formSubmitAllowed = false;
                });

                // Confirm button - allow form submission
                confirmBtn.addEventListener('click', function () {
                    confirmationModal.classList.add('hidden');
                    formSubmitAllowed = true;
                    uploadForm.submit();
                });

                // Close modal on outside click
                confirmationModal.addEventListener('click', function (e) {
                    if (e.target === confirmationModal) {
                        confirmationModal.classList.add('hidden');
                        formSubmitAllowed = false;
                    }
                });
            });

        </script>
    @endpush
@endsection