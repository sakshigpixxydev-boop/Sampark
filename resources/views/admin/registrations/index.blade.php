@extends('layouts.app')

@section('title', 'Registered Students')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Registered Students</h2>
                <p class="mt-2 text-sm text-gray-600">Students who have submitted their photo and are awaiting approval</p>
            </div>
            <div class="flex-shrink-0 flex flex-col sm:flex-row items-center gap-2">
                @if(auth()->user()->hasPermission(11404) || auth()->user()->hasPermission(11604))
                    <a href="{{ route('migrated-students.upload.form') }}" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold shadow-sm transition-all flex items-center gap-1.5">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Migrated Student Import
                    </a>
                @endif
                @if(auth()->user()->canRunRegistrationOps())
                    @if(\App\Models\Student::where('fees_status', '!=', 'Paid')->exists())
                        <form action="{{ route('admin.registrations.sync-all-inprogress') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-gradient-to-br from-[#00bde0] to-[#0099b8] hover:shadow-md text-white rounded-xl font-semibold shadow-sm transition-all flex items-center gap-1.5">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H17v5h5M11 12H9m2 0h2m-2 0v2m0-2v-2"></path></svg>
                                Sync Payments
                            </button>
                        </form>
                    @endif
                @endif
            </div>
        </div>

        @if(auth()->user()->hasRole('School') || (auth()->user()->hasRole('Educator') && auth()->user()->hasPermission(11602)))
            @php
                $user = auth()->user();
                $schoolIds = $user->getAssignedSchoolIds();
                $schoolId = !empty($schoolIds) ? $schoolIds[0] : 0;
                
                $pendingCount = \App\Models\Student::where('school_id', $schoolId)
                    ->where('registered_via_school_link', true)
                    ->where('school_approval_status', 'Pending')
                    ->count();
                    
                $approvedCount = \App\Models\Student::where('school_id', $schoolId)
                    ->where('registered_via_school_link', true)
                    ->where('school_approval_status', 'Approved')
                    ->whereNull('batch_id')
                    ->count();
                    
                $rejectedCount = \App\Models\Student::where('school_id', $schoolId)
                    ->where('registered_via_school_link', true)
                    ->where('school_approval_status', 'Rejected')
                    ->count();
            @endphp
            
            <div class="mb-8 bg-white border border-gray-200 rounded-2xl shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">School Registration Batch Management</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-yellow-50 border border-yellow-100 rounded-xl">
                        <span class="block text-xs font-semibold text-yellow-600 uppercase">Pending Review</span>
                        <span class="text-2xl font-bold text-yellow-800">{{ $pendingCount }}</span>
                    </div>
                    <div class="p-4 bg-green-50 border border-green-100 rounded-xl">
                        <span class="block text-xs font-semibold text-green-600 uppercase">Approved (Unpublished)</span>
                        <span class="text-2xl font-bold text-green-800">{{ $approvedCount }}</span>
                    </div>
                    <div class="p-4 bg-red-50 border border-red-100 rounded-xl">
                        <span class="block text-xs font-semibold text-red-600 uppercase">Rejected</span>
                        <span class="text-2xl font-bold text-red-800">{{ $rejectedCount }}</span>
                    </div>
                </div>
                
                @if($pendingCount > 0)
                    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
                        <strong>Note:</strong> You have pending registrations to review. Please approve or reject all of them before you can publish a batch.
                    </div>
                @elseif($approvedCount > 0)
                    <form action="{{ route('admin.registrations.publish-batch') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                            <div>
                                <label for="publish_sembatches_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Target Semester Batch <span class="text-red-500">*</span>
                                </label>
                                <select name="sembatches_id" id="publish_sembatches_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select Batch</option>
                                    @foreach($batches as $b)
                                        @php
                                            $planNames = $b->feePlans->pluck('fee_name')->implode(' / ');
                                            $label = !empty($planNames) ? $planNames : ($b->display_name ?? 'Batch');
                                        @endphp
                                        <option value="{{ $b->sembatches_id }}">
                                            {{ $label }} ({{ optional($b->start_date)->format('d/m/Y') ?? 'N/A' }} - {{ optional($b->end_date)->format('d/m/Y') ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label for="publish_payment_type" class="block text-sm font-medium text-gray-700 mb-1">
                                    Payment Type <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_type" id="publish_payment_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="offline">Offline Payment</option>
                                    <option value="online">Online Payment (Razorpay)</option>
                                </select>
                            </div>
                            
                            <div id="publish_offline_method_container">
                                <label for="publish_offline_payment_method" class="block text-sm font-medium text-gray-700 mb-1">
                                    Offline Payment Method <span class="text-red-500">*</span>
                                </label>
                                <select name="offline_payment_method" id="publish_offline_payment_method" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select Method</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t border-gray-100">
                            <button type="submit" 
                                class="px-6 py-2.5 bg-gradient-to-br from-[#00bde0] to-[#0099b8] hover:shadow-md text-white rounded-xl font-semibold shadow-sm transition-all">
                                Publish Batch ({{ $approvedCount }} Students)
                            </button>
                        </div>
                    </form>
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const paymentType = document.getElementById('publish_payment_type');
                            const container = document.getElementById('publish_offline_method_container');
                            const methodSelect = document.getElementById('publish_offline_payment_method');
                            
                            if (paymentType && container) {
                                function toggleOffline() {
                                    if (paymentType.value === 'online') {
                                        container.style.display = 'none';
                                        methodSelect.required = false;
                                    } else {
                                        container.style.display = 'block';
                                        methodSelect.required = true;
                                    }
                                }
                                paymentType.addEventListener('change', toggleOffline);
                                toggleOffline();
                            }
                        });
                    </script>
                @else
                    <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-600">
                        No approved students are currently waiting to be published in a batch.
                    </div>
                @endif
            </div>
        @endif

        <!-- Filters & Sync -->
        <div class="mb-6">
            <div class="w-full">
                <x-filter-panel action="{{ route('admin.registrations.index') }}">
                    <!-- Hidden field to keep selected tab status when applying filters -->
                    <input type="hidden" name="status" value="{{ $selectedTab }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Name, email, contact, enrolment..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        </div>

                        <!-- School Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
                            <select name="school_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Schools</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->school_id }}" {{ request('school_id') == $school->school_id ? 'selected' : '' }}>
                                        {{ $school->schoolname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Batch Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Batch</label>
                            <select name="sembatches_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Batches</option>
                                @foreach($batches as $batch)
                                    @php
                                        $planNames = $batch->feePlans->pluck('fee_name')->implode(' / ');
                                        $label = !empty($planNames) ? $planNames : ($batch->display_name ?? 'Batch');
                                    @endphp
                                    <option value="{{ $batch->sembatches_id }}" {{ request('sembatches_id') == $batch->sembatches_id ? 'selected' : '' }}>
                                        {{ $label }} ({{ optional($batch->start_date)->format('d/m/Y') ?? 'N/A' }} - {{ optional($batch->end_date)->format('d/m/Y') ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Fee Plan / Subscription Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fee Plan (Subscription)</label>
                            <select name="subscription" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Fee Plans</option>
                                @foreach($subscriptions as $sub)
                                    <option value="{{ $sub }}" {{ request('subscription') === $sub ? 'selected' : '' }}>
                                        {{ $sub }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Source Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                            <select name="source" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Sources</option>
                                @foreach($sources as $key => $label)
                                    <option value="{{ $key }}" {{ request('source') === (string)$key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Payment Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                            <select name="payment_status" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Payments</option>
                                @foreach($payment_statuses as $key => $label)
                                    <option value="{{ $key }}" {{ request('payment_status') === (string)$key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Gender Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                            <select name="gender" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Genders</option>
                                <option value="M" {{ request('gender') === 'M' ? 'selected' : '' }}>Male</option>
                                <option value="F" {{ request('gender') === 'F' ? 'selected' : '' }}>Female</option>
                                <option value="O" {{ request('gender') === 'O' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <!-- Standard Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Standard / Education Level</label>
                            <select name="std_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Standards</option>
                                @foreach($standards as $std)
                                    <option value="{{ $std->std_id }}" {{ request('std_id') == $std->std_id ? 'selected' : '' }}>
                                        {{ $std->stdname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stream Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stream</label>
                            <select name="stream" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Streams</option>
                                <option value="Arts" {{ request('stream') === 'Arts' ? 'selected' : '' }}>Arts</option>
                                <option value="Commerce" {{ request('stream') === 'Commerce' ? 'selected' : '' }}>Commerce</option>
                                <option value="Science" {{ request('stream') === 'Science' ? 'selected' : '' }}>Science</option>
                            </select>
                        </div>

                        <!-- Medium Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Education Medium</label>
                            <select name="educationmedium" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Mediums</option>
                                @foreach($mediums as $med)
                                    <option value="{{ $med->medium_id }}" {{ request('educationmedium') == $med->medium_id ? 'selected' : '' }}>
                                        {{ $med->mediumname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Attending Sabha -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Attending Sabha</label>
                            <select name="attending_sabha" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All</option>
                                <option value="Yes" {{ request('attending_sabha') === 'Yes' ? 'selected' : '' }}>Yes</option>
                                <option value="No" {{ request('attending_sabha') === 'No' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>

                        <!-- Satsang Mandal -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Satsang Mandal</label>
                            <select name="satsangmandal_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Mandals</option>
                                @foreach($mandals as $mandal)
                                    <option value="{{ $mandal->satsangmandal_id }}" {{ request('satsangmandal_id') == $mandal->satsangmandal_id ? 'selected' : '' }}>
                                        {{ $mandal->mandalname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Shikhar Mandir -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shikharabaddha Mandir</label>
                            <select name="shikharm_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Mandirs</option>
                                @foreach($mandirs as $mandir)
                                    <option value="{{ $mandir->shikharm_id }}" {{ request('shikharm_id') == $mandir->shikharm_id ? 'selected' : '' }}>
                                        {{ $mandir->mname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- City Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City / Village</label>
                            <input type="text" name="city_village" value="{{ request('city_village') }}" 
                                   placeholder="City or village..." 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                        </div>

                        <!-- Educational Board Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Educational Board</label>
                            <select name="eduboard_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="">All Boards</option>
                                @foreach($educationalBoards as $board)
                                    <option value="{{ $board->eduboard_id }}" {{ request('eduboard_id') == $board->eduboard_id ? 'selected' : '' }}>
                                        {{ $board->boardname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="text" name="start_date" value="{{ request('start_date') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
                        </div>

                        <!-- End Date -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="text" name="end_date" value="{{ request('end_date') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
                        </div>

                        <!-- Sort By -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
                            <select name="sort_by" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="student_id" {{ request('sort_by') === 'student_id' ? 'selected' : '' }}>Registration Date</option>
                                <option value="fname" {{ request('sort_by') === 'fname' ? 'selected' : '' }}>First Name</option>
                                <option value="lname" {{ request('sort_by') === 'lname' ? 'selected' : '' }}>Last Name</option>
                                <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Date Created</option>
                                <option value="fees_status" {{ request('sort_by') === 'fees_status' ? 'selected' : '' }}>Payment Status</option>
                                <option value="application_status" {{ request('sort_by') === 'application_status' ? 'selected' : '' }}>Application Status</option>
                            </select>
                        </div>

                        <!-- Sort Order -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                            <select name="sort_order" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>Descending (Newest first)</option>
                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Ascending (Oldest first)</option>
                            </select>
                        </div>
                    </div>
                </x-filter-panel>
            </div>
        </div>

        <!-- Bulk Actions Bar -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-col md:flex-row justify-between items-center bg-gradient-to-r from-gray-50 via-white to-gray-50 gap-4 transition-all duration-300">
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center font-bold px-3 py-1.5 rounded-full text-xs" style="background-color: rgba(0, 189, 224, 0.1); color: #00bde0;" id="selected-badge">
                        0 Selected
                    </span>
                    <span class="text-sm text-gray-500 font-medium font-sans">Select actions to apply:</span>
                </div>
                <div class="flex items-center gap-6">
                    <label class="flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="select-visible" class="h-4.5 w-4.5 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded transition-all mr-2">
                        <span class="text-xs font-semibold text-gray-700 hover:text-gray-900 transition-colors">Select Visible (<span id="visible-count-label">0</span>)</span>
                    </label>
                    <label class="flex items-center cursor-pointer select-none">
                        <input type="checkbox" id="select-all-pages" name="select_all_pages" value="1" class="h-4.5 w-4.5 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded transition-all mr-2">
                        <span class="text-xs font-semibold text-gray-700 hover:text-gray-900 transition-colors">Select All Pages (<span id="total-count-label">0</span>)</span>
                    </label>
                </div>
            </div>
            
            <!-- Actions Group -->
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                <!-- Bulk Export Form -->
                @if(auth()->user()->canRunRegistrationOps())
                <form id="export-form" action="{{ route('admin.registrations.export') }}" method="POST" class="inline">
                    @csrf
                    @foreach(request()->query() as $key => $val)
                        @if(is_array($val))
                            @foreach($val as $item)
                                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endif
                    @endforeach
                    <input type="hidden" id="export-select-all-pages" name="select_all_pages" value="false">
                    
                    <button type="submit" id="bulk-export-btn" 
                        class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold shadow-sm transition-all flex items-center gap-1.5 text-sm">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span id="export-btn-label">Export CSV</span>
                    </button>
                </form>
                @endif

                <!-- Sync Payments Button -->
                @if(auth()->user()->canRunRegistrationOps())
                <button type="button" id="bulk-sync-payments-btn" 
                    class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold shadow-sm transition-all flex items-center gap-1.5 text-sm opacity-50 cursor-not-allowed" disabled>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Sync Payments
                </button>
                @endif

                <!-- Bulk Email Button -->
                @if(auth()->user()->isMasterAdmin())
                <button type="button" id="bulk-email-btn" 
                    class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold shadow-sm transition-all flex items-center gap-1.5 text-sm opacity-50 cursor-not-allowed" disabled>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Send Email
                </button>
                @endif

                <!-- Bulk Shift Button -->
                @if(auth()->user()->isMasterAdmin())
                <button type="button" id="bulk-shift-btn" 
                    class="px-4 py-2 bg-gradient-to-br from-[#00bde0] to-[#0099b8] hover:shadow-md text-white rounded-xl font-semibold shadow-sm transition-all flex items-center gap-1.5 text-sm opacity-50 cursor-not-allowed" disabled>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    Shift Students
                </button>
                @endif
            </div>
        </div>

        <!-- Dynamic Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-6 overflow-x-auto py-1" aria-label="Tabs">
                @foreach([
                    'all' => 'All',
                    'pending_docs' => 'Documents Pending',
                    'processing' => 'Payments in Progress',
                    'enrolled' => 'Enrolled',
                    'rejected' => 'Rejected',
                    'failed' => 'Failed Payments',
                    'unpaid' => 'Unpaid',
                    'ready_to_enroll' => 'Ready to Enroll',
                    'archived' => 'Archived'
                ] as $tabKey => $tabLabel)
                    @php
                        $isActive = $selectedTab === $tabKey;
                        $tabCount = $tabCounts[$tabKey] ?? 0;
                        
                        // Build link with all current request parameters EXCEPT page and status
                        $params = request()->except(['page', 'status']);
                        $params['status'] = $tabKey;
                        $tabUrl = route('admin.registrations.index', $params);
                    @endphp
                    <a href="{{ $tabUrl }}" 
                       class="whitespace-nowrap pb-4 px-1 border-b-2 font-semibold text-sm transition-all duration-200 flex items-center gap-2
                       {{ $isActive 
                           ? 'border-[#00bde0] text-[#00bde0]' 
                           : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $tabLabel }}
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $isActive 
                                ? 'bg-[#00bde0] text-white' 
                                : 'bg-gray-100 text-gray-600' }}">
                            {{ $tabCount }}
                        </span>
                    </a>
                @endforeach
            </nav>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 text-green-700 rounded-r-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 text-red-700 rounded-r-lg">
                {{ session('error') }}
            </div>
        @endif

        @if(session('failed_students'))
            <div class="mb-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 rounded-r-lg">
                <h4 class="font-bold mb-2">Some students could not be migrated:</h4>
                <ul class="list-disc list-inside space-y-1">
                    @foreach(session('failed_students') as $failed)
                        <li>
                            <strong class="text-gray-900">{{ $failed['name'] }}</strong> (ID: {{ $failed['id'] }}): {{ $failed['reason'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div id="ajax-grid">
            @include('admin.registrations.partials.student_grid')
        </div>
    </div>
</div>

<!-- Bulk Shift Modal -->
<div id="bulk-shift-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeBulkShiftModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="bulk-shift-form" action="{{ route('admin.registrations.bulk-shift') }}" method="POST">
                @csrf
                @foreach(request()->query() as $key => $val)
                    @if(is_array($val))
                        @foreach($val as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach
                <input type="hidden" id="modal-select-all-pages" name="select_all_pages" value="false">

                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#00bde0] bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                Bulk Shift Students
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    You have selected <span id="selected-count-badge" class="font-bold text-gray-800">0</span> student(s) to shift.
                                </p>
                            </div>
                            
                            <div class="mt-4">
                                <label for="target_sembatch_id" class="block text-sm font-semibold text-gray-700 mb-2">Target Semester Batch</label>
                                <select id="target_sembatch_id" name="target_sembatch_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select a target batch</option>
                                    @foreach($batches as $batch)
                                        @php
                                            $planNames = $batch->feePlans->pluck('fee_name')->implode(' / ');
                                            $label = !empty($planNames) ? $planNames : ($batch->display_name ?? 'Batch');
                                        @endphp
                                        <option value="{{ $batch->sembatches_id }}">
                                            {{ $label }} ({{ optional($batch->start_date)->format('d/m/Y') ?? 'N/A' }} - {{ optional($batch->end_date)->format('d/m/Y') ?? 'N/A' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-4 font-sans">
                                <label for="shift_mail_template_identifier" class="block text-sm font-semibold text-gray-700 mb-2">Notification Email Template (Optional)</label>
                                <select id="shift_mail_template_identifier" name="mail_template_identifier" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Do not send email notification</option>
                                    @foreach($mailTemplates as $template)
                                        <option value="{{ $template->identifier }}">
                                            {{ $template->title_en }} ({{ $template->identifier }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-gradient-to-br from-[#00bde0] to-[#0099b8] text-base font-semibold text-white hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00bde0] sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        Confirm Shift
                    </button>
                    <button type="button" onclick="closeBulkShiftModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Email Modal -->
<div id="bulk-email-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeBulkEmailModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="bulk-email-form" action="{{ route('admin.registrations.bulk-email') }}" method="POST">
                @csrf
                @foreach(request()->query() as $key => $val)
                    @if(is_array($val))
                        @foreach($val as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach
                <input type="hidden" id="email-modal-select-all-pages" name="select_all_pages" value="false">

                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#00bde0] bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="email-modal-title">
                                Send Bulk Email
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 font-sans">
                                    You have selected <span id="email-selected-count-badge" class="font-bold text-gray-800">0</span> student(s) to email.
                                </p>
                            </div>
                            
                            <div class="mt-4 font-sans">
                                <label for="template_identifier" class="block text-sm font-semibold text-gray-700 mb-2">Mail Template</label>
                                <select id="template_identifier" name="mail_template_identifier" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                                    <option value="">Select an email template</option>
                                    @foreach($mailTemplates as $template)
                                        <option value="{{ $template->identifier }}">
                                            {{ $template->title_en }} ({{ $template->identifier }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-gradient-to-br from-[#00bde0] to-[#0099b8] text-base font-semibold text-white hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00bde0] sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        Send Emails
                    </button>
                    <button type="button" onclick="closeBulkEmailModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Sync Payments Modal -->
<div id="bulk-sync-payments-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeBulkSyncPaymentsModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="bulk-sync-payments-form" action="{{ route('admin.registrations.bulk-sync-payments') }}" method="POST">
                @csrf
                @foreach(request()->query() as $key => $val)
                    @if(is_array($val))
                        @foreach($val as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endif
                @endforeach
                <input type="hidden" id="sync-modal-select-all-pages" name="select_all_pages" value="false">

                <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#00bde0] bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full font-sans">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="sync-modal-title">
                                Sync Payments
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 font-sans">
                                    You have selected <span id="sync-selected-count-badge" class="font-bold text-gray-800">0</span> student(s) to check and sync Razorpay payments.
                                </p>
                                <p class="text-xs text-yellow-600 mt-2 font-semibold">
                                    Note: This will check the Razorpay status for selected students and mark them as Paid if successful payments are found.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-gradient-to-br from-[#00bde0] to-[#0099b8] text-base font-semibold text-white hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#00bde0] sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        Sync Payments
                    </button>
                    <button type="button" onclick="closeBulkSyncPaymentsModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Deletion Reason Modal -->
<x-modal name="delete-student" title="Delete Student Profile" maxWidth="lg">
    <form id="delete-student-form" action="" method="POST">
        @csrf
        @method('DELETE')
        <div class="space-y-4">
            <div class="flex items-start gap-4 p-1">
                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                        </path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-gray-900 mb-2">Are you sure you want to delete this student record?</h4>
                    <p class="text-xs text-gray-500 mb-4 font-normal">This will soft-delete the student, suspending system access. Please select or write a reason for deletion:</p>
                    
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Reason for Deletion</label>
                    <select name="reason_select" id="reason_select" onchange="toggleCustomReason()" class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#00bde0] bg-white mb-4">
                        <option value="Duplicate Student Account">Duplicate Student Account</option>
                        <option value="Not Interested / Left Course">Not Interested / Left Course</option>
                        <option value="Incorrect Batch / Invalid Admission">Incorrect Batch / Invalid Admission</option>
                        <option value="Other">Other (Write Custom Reason Below)</option>
                    </select>

                    <div id="custom_reason_container" class="hidden">
                        <textarea name="reason" id="custom_reason" rows="3" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-red-500 focus:border-red-500 transition-all text-sm"
                            placeholder="Please detail the reason for deletion..."></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" x-on:click="$dispatch('close-modal', 'delete-student')"
                class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                Cancel
            </button>
            <button type="submit"
                class="px-5 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 shadow-md transition-all">
                Confirm Delete
            </button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectedIds = new Set();
        const excludedIds = new Set();
        let isSelectAllPages = false;

        // Total count across all pages
        let totalCount = {{ $students->total() }};
        const GRID_CONTAINER_ID = 'ajax-grid';

        // Helper to update labels and total count from metadata
        function syncMetadata() {
            const gridMetadata = document.getElementById('grid-metadata');
            if (gridMetadata) {
                totalCount = parseInt(gridMetadata.getAttribute('data-total-count')) || 0;
                const visibleCount = parseInt(gridMetadata.getAttribute('data-visible-count')) || 0;
                
                const visibleLabel = document.getElementById('visible-count-label');
                if (visibleLabel) visibleLabel.textContent = visibleCount;
                
                const totalLabel = document.getElementById('total-count-label');
                if (totalLabel) totalLabel.textContent = totalCount;
            }
        }

        // Initialize UI on page load
        updateUI();

        // Export form submit handler
        const exportForm = document.getElementById('export-form');
        if (exportForm) {
            exportForm.addEventListener('submit', function(e) {
                const selectAllInput = document.getElementById('export-select-all-pages');
                prepareForm(exportForm, selectAllInput);
            });
        }

        function updateUI() {
            syncMetadata();

            // Count selected based on mode
            let selectedCount = 0;
            if (isSelectAllPages) {
                selectedCount = totalCount - excludedIds.size;
            } else {
                selectedCount = selectedIds.size;
            }

            // Update selected badge
            const selectedBadge = document.getElementById('selected-badge');
            if (selectedBadge) {
                selectedBadge.textContent = `${selectedCount} Selected`;
                if (selectedCount > 0) {
                    selectedBadge.style.backgroundColor = '#00bde0';
                    selectedBadge.style.color = '#ffffff';
                } else {
                    selectedBadge.style.backgroundColor = 'rgba(0, 189, 224, 0.1)';
                    selectedBadge.style.color = '#00bde0';
                }
            }

            // Update export button label
            const exportBtnLabel = document.getElementById('export-btn-label');
            if (exportBtnLabel) {
                if (selectedCount > 0) {
                    exportBtnLabel.textContent = `Export Selected (${selectedCount})`;
                } else {
                    exportBtnLabel.textContent = 'Export CSV';
                }
            }

            // Update visible checkboxes status
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                const val = String(cb.value).trim();
                if (isSelectAllPages) {
                    cb.checked = !excludedIds.has(val);
                } else {
                    cb.checked = selectedIds.has(val);
                }
            });

            // Update master checkboxes
            const selectVisibleCheckbox = document.getElementById('select-visible');
            if (selectVisibleCheckbox) {
                if (checkboxes.length > 0) {
                    let allChecked = true;
                    checkboxes.forEach(cb => {
                        if (!cb.checked) allChecked = false;
                    });
                    selectVisibleCheckbox.checked = allChecked;
                } else {
                    selectVisibleCheckbox.checked = false;
                }
            }

            const selectAllPagesCheckbox = document.getElementById('select-all-pages');
            if (selectAllPagesCheckbox) {
                selectAllPagesCheckbox.checked = isSelectAllPages;
            }

            // Update button states
            const buttons = ['bulk-shift-btn', 'bulk-email-btn', 'bulk-sync-payments-btn'];
            buttons.forEach(id => {
                const btn = document.getElementById(id);
                if (btn) {
                    if (selectedCount > 0) {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                        if (id !== 'bulk-shift-btn') {
                            btn.classList.add('border-[#00bde0]', 'text-[#00bde0]', 'hover:bg-[#f0fdfa]');
                            btn.classList.remove('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                        }
                    } else {
                        btn.disabled = true;
                        btn.classList.add('opacity-50', 'cursor-not-allowed');
                        if (id !== 'bulk-shift-btn') {
                            btn.classList.remove('border-[#00bde0]', 'text-[#00bde0]', 'hover:bg-[#f0fdfa]');
                            btn.classList.add('border-gray-300', 'text-gray-700', 'hover:bg-gray-50');
                        }
                    }
                }
            });
        }

        // Toggle visible / all pages / individual checkboxes via delegation
        document.addEventListener('change', function(e) {
            if (e.target.id === 'select-visible') {
                const checkboxes = document.querySelectorAll('.student-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                    const val = String(cb.value).trim();
                    if (isSelectAllPages) {
                        if (cb.checked) {
                            excludedIds.delete(val);
                        } else {
                            excludedIds.add(val);
                        }
                    } else {
                        if (cb.checked) {
                            selectedIds.add(val);
                        } else {
                            selectedIds.delete(val);
                        }
                    }
                });
                updateUI();
            }

            if (e.target.id === 'select-all-pages') {
                isSelectAllPages = e.target.checked;
                selectedIds.clear();
                excludedIds.clear();
                updateUI();
            }

            if (e.target.classList.contains('student-checkbox')) {
                const val = String(e.target.value).trim();
                if (isSelectAllPages) {
                    if (e.target.checked) {
                        excludedIds.delete(val);
                    } else {
                        excludedIds.add(val);
                    }
                } else {
                    if (e.target.checked) {
                        selectedIds.add(val);
                    } else {
                        selectedIds.delete(val);
                    }
                }
                updateUI();
            }
        });

        // AJAX Pagination Logic
        async function loadGrid(url) {
            try {
                const container = document.getElementById(GRID_CONTAINER_ID);
                container.style.opacity = '0.5';
                container.style.pointerEvents = 'none';

                const response = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const html = await response.text();
                container.innerHTML = html;
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
                
                window.history.pushState({}, '', url);
                
                updateUI();
            } catch (error) {
                console.error('Failed to load grid:', error);
                window.location.href = url;
            }
        }

        // Listen for pagination clicks
        document.addEventListener('click', function(e) {
            const link = e.target.closest('.pagination-container a, .page-link');
            if (link && link.href) {
                e.preventDefault();
                loadGrid(link.href);
            }
        });

        // Helper function to prepare forms for submit
        function prepareForm(targetForm, targetSelectAllInput) {
            let selectedCount = 0;
            if (isSelectAllPages) {
                selectedCount = totalCount - excludedIds.size;
            } else {
                selectedCount = selectedIds.size;
            }

            targetSelectAllInput.value = isSelectAllPages ? 'true' : 'false';

            // Remove existing dynamic inputs in form
            targetForm.querySelectorAll('.dynamic-input').forEach(el => el.remove());

            if (isSelectAllPages) {
                excludedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'excluded_students[]';
                    input.value = id;
                    input.classList.add('dynamic-input');
                    targetForm.appendChild(input);
                });
            } else {
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selected_students[]';
                    input.value = id;
                    input.classList.add('dynamic-input');
                    targetForm.appendChild(input);
                });
            }
            return selectedCount;
        }

        // Click handlers to open modals
        document.addEventListener('click', function(e) {
            // Bulk Shift
            const shiftBtn = e.target.closest('#bulk-shift-btn');
            if (shiftBtn) {
                const form = document.getElementById('bulk-shift-form');
                const selectAllInput = document.getElementById('modal-select-all-pages');
                const selectedCount = prepareForm(form, selectAllInput);
                
                document.getElementById('selected-count-badge').textContent = selectedCount;
                document.getElementById('bulk-shift-modal').classList.remove('hidden');
            }

            // Bulk Email
            const emailBtn = e.target.closest('#bulk-email-btn');
            if (emailBtn) {
                const form = document.getElementById('bulk-email-form');
                const selectAllInput = document.getElementById('email-modal-select-all-pages');
                const selectedCount = prepareForm(form, selectAllInput);
                
                document.getElementById('email-selected-count-badge').textContent = selectedCount;
                document.getElementById('bulk-email-modal').classList.remove('hidden');
            }

            // Bulk Sync Payments
            const syncBtn = e.target.closest('#bulk-sync-payments-btn');
            if (syncBtn) {
                const form = document.getElementById('bulk-sync-payments-form');
                const selectAllInput = document.getElementById('sync-modal-select-all-pages');
                const selectedCount = prepareForm(form, selectAllInput);
                
                document.getElementById('sync-selected-count-badge').textContent = selectedCount;
                document.getElementById('bulk-sync-payments-modal').classList.remove('hidden');
            }
        });

        // Close modal helpers
        window.closeBulkShiftModal = function() {
            document.getElementById('bulk-shift-modal').classList.add('hidden');
        };
        window.closeBulkEmailModal = function() {
            document.getElementById('bulk-email-modal').classList.add('hidden');
        };
        window.closeBulkSyncPaymentsModal = function() {
            document.getElementById('bulk-sync-payments-modal').classList.add('hidden');
        };

        // Clear selection on filter form submit
        const filterForm = document.querySelector('form[action="{{ route('admin.registrations.index') }}"]');
        if (filterForm) {
            filterForm.addEventListener('submit', function() {
                selectedIds.clear();
                excludedIds.clear();
                isSelectAllPages = false;
            });
        }
    });

    function openDeleteModal(studentId, url) {
        const form = document.getElementById('delete-student-form');
        form.action = url;
        
        // Reset defaults
        document.getElementById('reason_select').value = 'Duplicate Student Account';
        document.getElementById('custom_reason').value = 'Duplicate Student Account';
        document.getElementById('custom_reason_container').classList.add('hidden');
        
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'delete-student' }));
    }

    function toggleCustomReason() {
        const select = document.getElementById('reason_select');
        const container = document.getElementById('custom_reason_container');
        const textarea = document.getElementById('custom_reason');
        
        if (select.value === 'Other') {
            container.classList.remove('hidden');
            textarea.value = '';
            textarea.required = true;
            textarea.focus();
        } else {
            container.classList.add('hidden');
            textarea.value = select.value;
            textarea.required = false;
        }
    }
    
    // Add event listener to submit form to make sure custom_reason is filled
    document.getElementById('delete-student-form').addEventListener('submit', function(e) {
        const select = document.getElementById('reason_select');
        const textarea = document.getElementById('custom_reason');
        if (select.value !== 'Other') {
            textarea.value = select.value;
            textarea.required = false;
        }
    });
</script>
@endpush
@endsection
