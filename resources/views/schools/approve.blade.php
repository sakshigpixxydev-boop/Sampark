@extends('layouts.app')

@section('title', 'Approve School')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Approve School</h2>
            <p class="mt-1 text-sm text-gray-600">Review school details and set fee structure</p>
        </div>

        <!-- School Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">School Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-500">School Name</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $school->schoolname }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">School Type</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $school->schoolType->schooltypename }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Educational Board</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $school->educationalBoard->boardname }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Admin Name</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $school->adminname }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Contact</label>
                    <div class="mt-1 text-sm text-gray-900 flex items-center gap-2 group">
                        @if($school->admincontact)
                            <x-phone-link :number="$school->admincontact" />
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $school->email ?? 'N/A' }}</p>
                </div>
                @if($school->lead)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Initiated By</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $school->initiator->fname }} {{ $school->initiator->lname }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Estimated Students</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $school->lead->estimated_students ?? 'N/A' }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Fee Structure Form -->
        <form method="POST" action="{{ route('schools.approve', $school->school_id) }}"
            class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf

            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Set Fee Structure</h3>
                <p class="text-sm text-gray-600 mb-4">Define the fee amount for each standard</p>
            </div>

            <div id="fee-plans-container" class="space-y-4">
                <!-- Fee Plan Template -->
                <div class="fee-plan-row grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Standard</label>
                        <select name="fee_plans[0][standard_id]" required
                            data-preserve-order="true"
                            class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            <option value="">Select Standard</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->std_id }}">{{ $standard->stdname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₹)</label>
                        <input type="number" name="fee_plans[0][amount]" step="0.01" min="0" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>
                </div>
            </div>

            <button type="button" onclick="addFeePlan()" class="text-sm text-[#00bde0] hover:text-[#00a8c9] font-medium">
                + Add Another Standard
            </button>

            @error('fee_plans')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('schools.index') }}"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    Approve School
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let feePlanIndex = 1;

            function addFeePlan() {
                const container = document.getElementById('fee-plans-container');
                const newRow = document.createElement('div');
                newRow.className = 'fee-plan-row grid grid-cols-1 md:grid-cols-3 gap-4 items-end';
                const selectId = `fee_plan_select_${feePlanIndex}`;
                newRow.innerHTML = `
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Standard</label>
                            <select name="fee_plans[${feePlanIndex}][standard_id]" id="${selectId}" required
                                class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">Select Standard</option>
                                @foreach($standards as $standard)
                                    <option value="{{ $standard->std_id }}">{{ $standard->stdname }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₹)</label>
                            <div class="flex space-x-2">
                                <input type="number" name="fee_plans[${feePlanIndex}][amount]" step="0.01" min="0" required
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <button type="button" onclick="this.closest('.fee-plan-row').remove()"
                                    class="px-3 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    `;
                container.appendChild(newRow);
                
                // Initialize TomSelect on the new select
                new TomSelect(`#${selectId}`, {
                    create: false
                });

                feePlanIndex++;
            }
        </script>
    @endpush
@endsection