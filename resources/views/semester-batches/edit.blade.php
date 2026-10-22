@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Semester Batch</h1>
        <p class="text-sm text-gray-600 mt-1">Update semester batch information</p>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('semester-batches.update', $semesterBatch->sembatches_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <!-- Display Name -->
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700 mb-1">
                        Display Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="display_name" 
                        id="display_name" 
                        value="{{ old('display_name', $semesterBatch->display_name) }}"
                        placeholder="e.g., Sem 1, Semester 1, etc."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('display_name') border-red-500 @enderror"
                        required
                    >
                    @error('display_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Semester -->
                <div>
                    <label for="semester" class="block text-sm font-medium text-gray-700 mb-1">
                        Semester <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="semester" 
                        id="semester" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('semester') border-red-500 @enderror"
                        required
                    >
                        <option value="">-- Select Semester --</option>
                        @for($i = 1; $i <= 14; $i++)
                            <option value="{{ $i }}" {{ old('semester', $semesterBatch->semester) == $i ? 'selected' : '' }}>Semester {{ $i }}</option>
                        @endfor
                    </select>
                    @error('semester')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Start Date <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="start_date" 
                        id="start_date" 
                        value="{{ old('start_date', $semesterBatch->start_date ? $semesterBatch->start_date->format('d/m/Y') : '') }}"
                        class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('start_date') border-red-500 @enderror"
                        placeholder="DD/MM/YYYY"
                        required
                    >
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                        End Date <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="end_date" 
                        id="end_date" 
                        value="{{ old('end_date', $semesterBatch->end_date ? $semesterBatch->end_date->format('d/m/Y') : '') }}"
                        class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('end_date') border-red-500 @enderror"
                        placeholder="DD/MM/YYYY"
                        required
                    >
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Capacity -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 mb-1">
                        Capacity <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="capacity" 
                        id="capacity" 
                        value="{{ old('capacity', $semesterBatch->capacity) }}"
                        min="1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('capacity') border-red-500 @enderror"
                        required
                    >
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="status" 
                        id="status" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('status') border-red-500 @enderror"
                        required
                    >
                        <option value="0" {{ old('status', $semesterBatch->status) == 0 ? 'selected' : '' }}>Active</option>
                        <option value="1" {{ old('status', $semesterBatch->status) == 1 ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Next Semester Batch -->
                <div>
                    <label for="next_sembatch_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Next Semester Batch (Optional)
                    </label>
                    <select 
                        name="next_sembatch_id" 
                        id="next_sembatch_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('next_sembatch_id') border-red-500 @enderror"
                    >
                        <option value="">-- Select Next Batch --</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->sembatches_id }}" {{ old('next_sembatch_id', $semesterBatch->next_sembatch_id) == $batch->sembatches_id ? 'selected' : '' }}>
                                {{ $batch->display_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('next_sembatch_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Edmingle Course & Batch -->
                <div class="border-t border-gray-200 pt-4 mt-4">
                    <h3 class="text-md font-medium text-gray-900 mb-4">Edmingle Course & Batch</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edmingle_course_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Edmingle Course
                            </label>
                            <select name="edmingle_course_id" id="edmingle_course_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">-- Select Edmingle Course --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course['id'] }}" data-name="{{ $course['name'] }}" {{ old('edmingle_course_id', $semesterBatch->edmingle_course_id) == $course['id'] ? 'selected' : '' }}>
                                        {{ $course['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="edmingle_course_name" id="edmingle_course_name" value="{{ old('edmingle_course_name', $semesterBatch->edmingle_course_name) }}">
                        </div>

                        <div id="edmingle_batch_container" class="{{ old('edmingle_course_id', $semesterBatch->edmingle_course_id) ? '' : 'hidden' }}">
                            <label for="edmingle_batch_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Edmingle Batch
                            </label>
                            <select name="edmingle_batch_id" id="edmingle_batch_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" data-selected-batch="{{ old('edmingle_batch_id', $semesterBatch->edmingle_batch_id) }}">
                                <option value="">-- Select Edmingle Batch (Optional) --</option>
                            </select>
                            <input type="hidden" name="edmingle_batch_name" id="edmingle_batch_name" value="{{ old('edmingle_batch_name', $semesterBatch->edmingle_batch_name) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
                <a href="{{ route('semester-batches.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Update Semester Batch
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const courseSelect = document.getElementById('edmingle_course_id');
    const courseNameInput = document.getElementById('edmingle_course_name');
    const batchSelect = document.getElementById('edmingle_batch_id');
    const batchNameInput = document.getElementById('edmingle_batch_name');
    const batchContainer = document.getElementById('edmingle_batch_container');
    
    // Save batch name when batch is selected
    if (batchSelect) {
        batchSelect.addEventListener('change', function() {
            if (this.selectedIndex > 0) {
                batchNameInput.value = this.options[this.selectedIndex].text;
            } else {
                batchNameInput.value = '';
            }
        });
    }

    function loadBatches(bundleId, selectedBatchId = null) {
        if (!bundleId) {
            batchContainer.classList.add('hidden');
            return;
        }

        // Show loading state
        batchContainer.classList.remove('hidden');
        batchSelect.innerHTML = '<option value="">Loading batches...</option>';
        batchSelect.disabled = true;

        // Fetch batches dynamically from API
        fetch(`{{ url('admin/registrations/api/batches') }}/${bundleId}`)
            .then(response => response.json())
            .then(data => {
                batchSelect.disabled = false;
                batchSelect.innerHTML = '<option value="">-- Select Edmingle Batch (Optional) --</option>';
                
                if (data.success && data.batches && data.batches.length > 0) {
                    data.batches.forEach(batch => {
                        const option = document.createElement('option');
                        option.value = batch.id;
                        option.textContent = batch.name;
                        if (selectedBatchId && batch.id == selectedBatchId) {
                            option.selected = true;
                        }
                        batchSelect.appendChild(option);
                    });
                    batchContainer.classList.remove('hidden');
                } else {
                    batchContainer.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error fetching batches:', error);
                batchSelect.disabled = false;
                batchSelect.innerHTML = '<option value="">-- Failed to load batches --</option>';
                batchContainer.classList.add('hidden');
            });
    }

    if (courseSelect) {
        // Initial load if course is pre-selected
        if (courseSelect.value) {
            const selectedBatchId = batchSelect.getAttribute('data-selected-batch');
            loadBatches(courseSelect.value, selectedBatchId);
        }

        courseSelect.addEventListener('change', function() {
            const bundleId = this.value;
            
            // Set course name
            if (this.selectedIndex > 0) {
                courseNameInput.value = this.options[this.selectedIndex].getAttribute('data-name');
            } else {
                courseNameInput.value = '';
            }
            
            // Clear old options
            batchSelect.innerHTML = '<option value="">-- Select Edmingle Batch (Optional) --</option>';
            batchNameInput.value = '';
            
            loadBatches(bundleId);
        });
    }
});
</script>
@endpush
@endsection
