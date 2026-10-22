@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Students</h1>
            <p class="text-sm text-gray-600 mt-1">Manage student information</p>
        </div>
        <div class="flex space-x-3">
            <form action="{{ route('students.export') }}" method="GET" class="inline-flex">
                @if(isset($filters['search']))
                    <input type="hidden" name="search" value="{{ $filters['search'] }}">
                @endif
                @if(isset($filters['school_id']))
                    <input type="hidden" name="school_id" value="{{ $filters['school_id'] }}">
                @endif
                @if(isset($filters['user_id']))
                    <input type="hidden" name="user_id" value="{{ $filters['user_id'] }}">
                @endif
                @if(isset($filters['payment_status']))
                    <input type="hidden" name="payment_status" value="{{ $filters['payment_status'] }}">
                @endif
                @if(isset($filters['sembatches_id']))
                    <input type="hidden" name="sembatches_id" value="{{ $filters['sembatches_id'] }}">
                @endif
                @if(isset($filters['source']))
                    <input type="hidden" name="source" value="{{ $filters['source'] }}">
                @endif
                @if(isset($filters['city_village']))
                    <input type="hidden" name="city_village" value="{{ $filters['city_village'] }}">
                @endif

                @if(isset($filters['eduboard_id']))
                    @if(is_array($filters['eduboard_id']))
                        @foreach($filters['eduboard_id'] as $boardId)
                            <input type="hidden" name="eduboard_id[]" value="{{ $boardId }}">
                        @endforeach
                    @else
                        <input type="hidden" name="eduboard_id" value="{{ $filters['eduboard_id'] }}">
                    @endif
                @endif
                @if(isset($filters['studenttype_id']))
                    @if(is_array($filters['studenttype_id']))
                        @foreach($filters['studenttype_id'] as $typeId)
                            <input type="hidden" name="studenttype_id[]" value="{{ $typeId }}">
                        @endforeach
                    @else
                        <input type="hidden" name="studenttype_id" value="{{ $filters['studenttype_id'] }}">
                    @endif
                @endif
                @if(request('start_date'))
                    <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                @endif
                @if(request('end_date'))
                    <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                @endif
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#00bde0] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export CSV
                </button>
            </form>
            <a href="{{ route('students.bulk-upload.physical-form') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#00bde0] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download Form
            </a>
            <a href="{{ route('students.bulk-upload.sample') }}"
                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#00bde0] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Sample
            </a>
            <a href="{{ route('students.bulk-upload.form') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition text-sm">
                Bulk Upload
            </a>
            <a href="{{ route('students.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition text-sm">
                Add New Student
            </a>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Filters -->
    <x-filter-panel action="{{ route('students.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Name or email..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
            </div>

            <!-- School Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
                <select name="school_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->school_id }}" {{ ($filters['school_id'] ?? '') == $school->school_id ? 'selected' : '' }}>
                            {{ $school->schoolname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- User Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Assigned User</label>
                <select name="user_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->sysuser_id }}" {{ ($filters['user_id'] ?? '') == $user->sysuser_id ? 'selected' : '' }}>
                            {{ $user->fname }} {{ $user->lname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
                <select name="payment_status" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Status</option>
                    <option value="DRAFT" {{ ($filters['payment_status'] ?? '') === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="ISSUED" {{ ($filters['payment_status'] ?? '') === 'ISSUED' ? 'selected' : '' }}>Issued</option>
                    <option value="PARTIAL" {{ ($filters['payment_status'] ?? '') === 'PARTIAL' ? 'selected' : '' }}>Partial</option>
                    <option value="PAID" {{ ($filters['payment_status'] ?? '') === 'PAID' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>

            <!-- Batch Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Batch</label>
                <select name="sembatches_id" class="searchable-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Batches</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->sembatches_id }}" {{ ($filters['sembatches_id'] ?? '') == $batch->sembatches_id ? 'selected' : '' }}>
                            {{ $batch->display_name ?? 'Batch' }} ({{ $batch->start_date->format('d/m/Y') }} - {{ $batch->end_date->format('d/m/Y') }})
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
                        <option value="{{ $key }}" {{ ($filters['source'] ?? '') === (string)$key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- City Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" name="city_village" value="{{ $filters['city_village'] ?? '' }}" 
                       placeholder="City or village..." 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
            </div>



            <!-- Educational Board Filter (Multi-select) -->
            <x-multi-select 
                name="eduboard_id"
                label="Educational Board"
                :options="$educationalBoards"
                :selected="isset($filters['eduboard_id']) ? (is_array($filters['eduboard_id']) ? $filters['eduboard_id'] : [$filters['eduboard_id']]) : []"
                valueKey="eduboard_id"
                labelKey="boardname"
                placeholder="Select boards..."
            />

            <!-- Student Type Filter (Multi-select) -->
            <x-multi-select 
                name="studenttype_id"
                label="Student Type"
                :options="$studentTypes"
                :selected="isset($filters['studenttype_id']) ? (is_array($filters['studenttype_id']) ? $filters['studenttype_id'] : [$filters['studenttype_id']]) : []"
                valueKey="studenttype_id"
                labelKey="studenttypename"
                placeholder="Select types..."
            />

            <!-- Date Range Filters -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="text" name="start_date" value="{{ request('start_date') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                       placeholder="DD/MM/YYYY">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                <input type="text" name="end_date" value="{{ request('end_date') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent"
                       placeholder="DD/MM/YYYY">
            </div>
        </div>
    </x-filter-panel>

    <!-- Students Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Student Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type / Board
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            School
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Semester
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($students as $student)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-[#00bde0] bg-opacity-10 flex items-center justify-center mr-3">
                                        <span class="text-[#00bde0] text-sm font-medium">
                                            {{ substr($student->fname, 0, 1) }}{{ substr($student->lname, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $student->fname }} {{ $student->mname }} {{ $student->lname }}
                                        </div>
                                        <div class="flex flex-col">
                                            <x-masked-email :email="$student->email" />
                                            @if($student->enrolment_no)
                                                <span class="text-[10px] font-bold text-[#00bde0] uppercase mt-0.5 tracking-wider">{{ $student->enrolment_no }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $student->studentType->studenttypename ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $student->educationalBoard->boardname ?? 'N/A' }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $student->school->schoolname ?? 'Not Assigned' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $student->city_village ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ $student->taluka->tname ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 flex items-center gap-2 group">
                                    <x-masked-phone :number="$student->contact_no" />
                                </div>
                                @if($student->whatsapp_no)
                                    <div class="text-sm text-gray-500 flex items-center gap-2 group mt-1">
                                        WA: <x-masked-phone :number="$student->whatsapp_no" />
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $activeSemester = $student->semesterBatches()
                                        ->wherePivot('status', 0) // 0 = Active
                                        ->orderBy('start_date')
                                        ->first();
                                    
                                    $upcomingSemesters = $student->semesterBatches()
                                        ->wherePivot('status', 0) // 0 = Active
                                        ->where('start_date', '>', now())
                                        ->orderBy('start_date')
                                        ->get();
                                @endphp
                                @if($activeSemester)
                                    <div class="text-sm font-medium text-gray-900">
                                        Current: {{ $activeSemester->display_name ?? 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $activeSemester->start_date ? $activeSemester->start_date->format('d/m/Y') : '' }} - 
                                        {{ $activeSemester->end_date ? $activeSemester->end_date->format('d/m/Y') : '' }}
                                    </div>
                                    @if($upcomingSemesters->count() > 1)
                                        <div class="text-xs text-[#00bde0] mt-1">
                                            +{{ $upcomingSemesters->count() - 1 }} upcoming
                                        </div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-500">Not Enrolled</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2 text-xs">
                                    @canAccess(10501)
                                    <a href="{{ route('student-growth.student', $student->student_id) }}" class="inline-flex items-center px-2 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50 text-[#00bde0] transition-colors" title="Growth Tracker">
                                        Growth
                                    </a>
                                    @endcanAccess
                                    <a href="{{ route('students.show', $student->student_id) }}" class="inline-flex items-center px-2.5 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50 text-gray-700 hover:text-[#00bde0] transition-colors" title="View Profile">
                                        View
                                    </a>
                                    <a href="{{ route('students.edit', $student->student_id) }}" class="inline-flex items-center px-2.5 py-1 bg-white border border-gray-300 rounded hover:bg-gray-50 text-gray-700 hover:text-gray-900 transition-colors" title="Edit Profile">
                                        Edit
                                    </a>
                                    @if(auth()->user()->hasPermission(10042) || auth()->user()->hasPermission(11201) || auth()->user()->hasPermission(10001) || auth()->user()->hasPermission(11602))
                                        <button type="button" onclick="openDeleteModal('{{ $student->student_id }}', '{{ route('students.destroy', $student->student_id) }}')" class="inline-flex items-center px-2.5 py-1 bg-white border border-red-200 rounded hover:bg-red-50 text-red-600 transition-colors" title="Delete Student">
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No students found. <a href="{{ route('students.create') }}" class="text-[#00bde0] hover:text-[#0099b8]">Create one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $students->links() }}
            </div>
        @endif
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

<script>
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
@endsection
