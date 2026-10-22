@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Fee Plan</h1>
        <p class="mt-1 text-sm text-gray-600">Update fee plan details</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('fee-plans.update', $feePlan->fee_plan_id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="fee_name" class="block text-sm font-medium text-gray-700 mb-1">
                    Fee Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="fee_name" id="fee_name" value="{{ old('fee_name', $feePlan->fee_name) }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('fee_name') border-red-500 @enderror">
                @error('fee_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                    Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">₹</span>
                    <input type="number" name="amount" id="amount" value="{{ old('amount', $feePlan->amount) }}" step="0.01" min="0" required
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('amount') border-red-500 @enderror">
                </div>
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="strike_through_amount" class="block text-sm font-medium text-gray-700 mb-1">
                    Strike Through Amount (Optional)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-2 text-gray-500">₹</span>
                    <input type="number" name="strike_through_amount" id="strike_through_amount" value="{{ old('strike_through_amount', $feePlan->strike_through_amount) }}" step="0.01" min="0"
                        class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('strike_through_amount') border-red-500 @enderror">
                </div>
                @error('strike_through_amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Fee Plan Scope</h3>
                <p class="text-sm text-gray-600 mb-4">Select at least one semester cover scope for this plan</p>

                <div class="space-y-6">
                    <x-multi-select 
                        name="sembatches_id"
                        label="Semester Covers"
                        :options="$semesterBatches"
                        :selected="old('sembatches_id', $feePlan->semesterBatches->pluck('sembatches_id')->toArray())"
                        valueKey="sembatches_id"
                        labelKey="display_name"
                        placeholder="Select semester covers..."
                    />
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Validity Period</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-1">
                            Valid From (Auto)
                        </label>
                        <input type="text" name="valid_from" id="valid_from" value="{{ old('valid_from', $feePlan->valid_from->format('d/m/Y')) }}" disabled class="bg-gray-100 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('valid_from') border-red-500 @enderror" placeholder="DD/MM/YYYY">
                        @error('valid_from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="valid_to" class="block text-sm font-medium text-gray-700 mb-1">
                            Valid To (Auto)
                        </label>
                        <input type="text" name="valid_to" id="valid_to" value="{{ old('valid_to', $feePlan->valid_to ? $feePlan->valid_to->format('d/m/Y') : '') }}" disabled class="bg-gray-100 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('valid_to') border-red-500 @enderror" placeholder="DD/MM/YYYY">
                        @error('valid_to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="plan_auto_close_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Plan Auto Close Date
                        </label>
                        <input type="text" name="plan_auto_close_date" id="plan_auto_close_date" value="{{ old('plan_auto_close_date', $feePlan->plan_auto_close_date ? $feePlan->plan_auto_close_date->format('d/m/Y') : '') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent @error('plan_auto_close_date') border-red-500 @enderror" placeholder="DD/MM/YYYY">
                        <p class="mt-1 text-xs text-gray-500">Date when this fee plan should automatically stop accepting new enrollments.</p>
                        @error('plan_auto_close_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>



            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active_on_form" id="is_active_on_form" value="1" {{ old('is_active_on_form', $feePlan->is_active_on_form) ? 'checked' : '' }} class="h-4 w-4 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded">
                    <label for="is_active_on_form" class="ml-2 block text-sm font-medium text-gray-700">
                        Show on Registration Form
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('fee-plans.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                    Cancel
                </a>
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                    Update Fee Plan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const semesterBatchesData = @json($semesterBatches->map(function($b) {
        return [
            'id' => (string)$b->sembatches_id,
            'start' => $b->start_date ? $b->start_date->format('Y-m-d') : null,
            'end' => $b->end_date ? $b->end_date->format('Y-m-d') : null
        ];
    }));

    function updateValidityDates(selectedIds) {
        const validFromInput = document.getElementById('valid_from');
        const validToInput = document.getElementById('valid_to');
        
        if (!selectedIds || selectedIds.length === 0) {
            validFromInput.value = '';
            validToInput.value = '';
            return;
        }

        let minStart = null;
        let maxEnd = null;

        selectedIds.forEach(id => {
            const batch = semesterBatchesData.find(b => b.id === id);
            if (batch) {
                if (batch.start) {
                    const startDate = new Date(batch.start);
                    if (!minStart || startDate < minStart) {
                        minStart = startDate;
                    }
                }
                if (batch.end) {
                    const endDate = new Date(batch.end);
                    if (!maxEnd || endDate > maxEnd) {
                        maxEnd = endDate;
                    }
                }
            }
        });

        const formatDate = (date) => {
            if (!date) return '';
            const d = String(date.getDate()).padStart(2, '0');
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const y = date.getFullYear();
            return `${d}/${m}/${y}`;
        };

        validFromInput.value = formatDate(minStart);
        validToInput.value = formatDate(maxEnd);
    }

    document.addEventListener('multi-select-changed', function(e) {
        if (e.detail && e.detail.name === 'sembatches_id') {
            updateValidityDates(e.detail.values);
        }
    });

    // Run once on load for pre-selected options
    document.addEventListener('DOMContentLoaded', function() {
        const initialSelected = @json(old('sembatches_id', $feePlan->semesterBatches->pluck('sembatches_id')->toArray()));
        if (initialSelected && initialSelected.length > 0) {
            updateValidityDates(initialSelected.map(String));
        }
    });
</script>
@endpush
@endsection
