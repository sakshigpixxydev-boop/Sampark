@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Create Initiator Fee Plan</h1>
        <p class="mt-1 text-sm text-gray-600">Define a reusable fee plan structure for paid initiators.</p>
    </div>

    <!-- Form -->
    <form action="{{ route('initiator-fee-plans.store') }}" method="POST">
        @csrf

        <div class="space-y-6">
            <!-- Basic Plan Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 border-bottom pb-2">Plan Details</h2>
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Plan Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('name') border-red-500 @enderror"
                        placeholder="e.g. standard Plan, High-Volume Plan">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- School Commission Settings -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 border-bottom pb-2">School Registration Commissions</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="new_school_registration_commission" class="block text-sm font-medium text-gray-700 mb-1">
                            New School Reg. (₹) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" id="new_school_registration_commission" name="new_school_registration_commission"
                            value="{{ old('new_school_registration_commission', 0) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('new_school_registration_commission') border-red-500 @enderror">
                        @error('new_school_registration_commission')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="school_count_base" class="block text-sm font-medium text-gray-700 mb-1">
                            Slab Base Count <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="school_count_base" name="school_count_base"
                            value="{{ old('school_count_base', 0) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('school_count_base') border-red-500 @enderror">
                        @error('school_count_base')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="school_count_slab_amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Amt. After Base (₹) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" id="school_count_slab_amount" name="school_count_slab_amount"
                            value="{{ old('school_count_slab_amount', 0) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('school_count_slab_amount') border-red-500 @enderror">
                        @error('school_count_slab_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">Example: First 10 schools = base, After 10 = ₹6000 per school.</p>
            </div>

            <!-- Student Commission Settings Selection -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 border-bottom pb-2">Student Commission Calculation Type</h2>
                <div class="flex gap-6 mt-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="student_calculation_type" value="0" class="form-radio text-[#00bde0] focus:ring-[#00bde0] h-4 w-4"
                            {{ old('student_calculation_type', '0') == '0' ? 'checked' : '' }} onchange="toggleCalculationSections()">
                        <span class="ml-2 text-gray-700">Per Student Commission (%)</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="student_calculation_type" value="1" class="form-radio text-[#00bde0] focus:ring-[#00bde0] h-4 w-4"
                            {{ old('student_calculation_type') == '1' ? 'checked' : '' }} onchange="toggleCalculationSections()">
                        <span class="ml-2 text-gray-700">Student Count Slabs</span>
                    </label>
                </div>
                @error('student_calculation_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Student Commission Settings -->
            <div id="section_per_student" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4 border-bottom pb-2">Per Student Commission (%)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="per_student_commission_first_semester" class="block text-sm font-medium text-gray-700 mb-1">
                            First Semester (%) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" id="per_student_commission_first_semester" name="per_student_commission_first_semester"
                            value="{{ old('per_student_commission_first_semester', 0) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('per_student_commission_first_semester') border-red-500 @enderror">
                        @error('per_student_commission_first_semester')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="per_student_commission_repeat_semester" class="block text-sm font-medium text-gray-700 mb-1">
                            Repeat Semester (%) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" id="per_student_commission_repeat_semester" name="per_student_commission_repeat_semester"
                            value="{{ old('per_student_commission_repeat_semester', 0) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('per_student_commission_repeat_semester') border-red-500 @enderror">
                        @error('per_student_commission_repeat_semester')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Student Count Slabs -->
            <div id="section_slab_based" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hidden">
                <div class="flex justify-between items-center mb-4 border-bottom pb-2">
                    <h2 class="text-lg font-medium text-gray-900">Student Count Slabs</h2>
                    <button type="button" onclick="addSlab()" class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded transition">
                        + Add Slab
                    </button>
                </div>
                @error('slabs')
                    <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div id="slabs_container" class="space-y-4">
                    @php
                        $displaySlabs = old('slabs', []);
                    @endphp
                    @forelse($displaySlabs as $index => $slab)
                        <div class="slab-row grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-gray-50 rounded-lg relative">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Min Students</label>
                                <input type="number" name="slabs[{{ $index }}][min_students]"
                                    value="{{ old("slabs.$index.min_students", $slab['min_students'] ?? '') }}"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error("slabs.$index.min_students") border-red-500 @enderror">
                                @error("slabs.$index.min_students")
                                    <p class="text-[10px] text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Commission (%)</label>
                                <input type="number" step="0.01" name="slabs[{{ $index }}][commission_percentage]"
                                    value="{{ old("slabs.$index.commission_percentage", $slab['commission_percentage'] ?? 0) }}"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error("slabs.$index.commission_percentage") border-red-500 @enderror">
                                @error("slabs.$index.commission_percentage")
                                    <p class="text-[10px] text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Fixed Amount (₹)</label>
                                <input type="number" step="0.01" name="slabs[{{ $index }}][fixed_amount]"
                                    value="{{ old("slabs.$index.fixed_amount", $slab['fixed_amount'] ?? 0) }}"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm @error("slabs.$index.fixed_amount") border-red-500 @enderror">
                                @error("slabs.$index.fixed_amount")
                                    <p class="text-[10px] text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="this.closest('.slab-row').remove()" class="text-red-500 hover:text-red-700 text-sm pb-1">Remove</button>
                            </div>
                        </div>
                    @empty
                        <p id="no_slabs_text" class="text-sm text-gray-500 py-4 text-center">No slabs defined. Click "Add Slab" to create one.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6">
                <a href="{{ route('initiator-fee-plans.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-6 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-6 py-2 rounded-lg transition">
                    Save Plan
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let slabIndex = {{ count($displaySlabs) }};

    function addSlab() {
        const container = document.getElementById('slabs_container');
        const noSlabsText = document.getElementById('no_slabs_text');
        if (noSlabsText) noSlabsText.remove();

        const div = document.createElement('div');
        div.className = 'slab-row grid grid-cols-1 md:grid-cols-4 gap-3 p-3 bg-gray-50 rounded-lg relative';
        div.innerHTML = `
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Min Students</label>
                <input type="number" name="slabs[${slabIndex}][min_students]" required class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Commission (%)</label>
                <input type="number" step="0.01" name="slabs[${slabIndex}][commission_percentage]" value="0" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Fixed Amount (₹)</label>
                <input type="number" step="0.01" name="slabs[${slabIndex}][fixed_amount]" value="0" class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
            </div>
            <div class="flex items-end">
                <button type="button" onclick="this.closest('.slab-row').remove()" class="text-red-500 hover:text-red-700 text-sm pb-1">Remove</button>
            </div>
        `;
        container.appendChild(div);
        slabIndex++;
    }

    function toggleCalculationSections() {
        const typeEl = document.querySelector('input[name="student_calculation_type"]:checked');
        if (!typeEl) return;
        
        const type = typeEl.value;
        const perStudentSection = document.getElementById('section_per_student');
        const slabSection = document.getElementById('section_slab_based');

        if (type === '0') {
            perStudentSection.classList.remove('hidden');
            slabSection.classList.add('hidden');

            // disable slab fields
            slabSection.querySelectorAll('input, select').forEach(el => el.disabled = true);
            // enable per student fields
            perStudentSection.querySelectorAll('input').forEach(el => el.disabled = false);
        } else {
            perStudentSection.classList.add('hidden');
            slabSection.classList.remove('hidden');

            // disable per student fields
            perStudentSection.querySelectorAll('input').forEach(el => el.disabled = true);
            // enable slab fields
            slabSection.querySelectorAll('input, select').forEach(el => el.disabled = false);
        }
    }

    // Call on load to set initial state
    document.addEventListener('DOMContentLoaded', toggleCalculationSections);
</script>
@endpush
@endsection
