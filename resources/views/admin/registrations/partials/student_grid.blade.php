<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div id="grid-metadata" data-visible-count="{{ $students->count() }}" data-total-count="{{ $students->total() }}" class="hidden"></div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left w-10"></th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Dates</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Source</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($students as $student)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <input type="checkbox" name="selected_students[]" value="{{ $student->student_id }}" 
                                class="student-checkbox h-4 w-4 text-[#00bde0] focus:ring-[#00bde0] border-gray-300 rounded">
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-[#00bde0] flex items-center justify-center text-white font-bold">
                                        {{ substr($student->fname, 0, 1) }}{{ substr($student->lname, 0, 1) }}
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $student->fname }} {{ $student->lname }}</div>
                                    <div class="text-xs text-gray-500"><x-masked-email :email="$student->email" /></div>
                                    <div class="text-xs text-gray-600 font-medium">
                                        {{ $student->standard->stdname ?? 'N/A' }} 
                                        @if($student->stream)
                                            - {{ $student->stream }}
                                        @endif
                                    </div>
                                    @if($student->enrolment_no)
                                    <div class="text-[10px] font-bold text-[#00bde0] uppercase mt-0.5 tracking-wider">{{ $student->enrolment_no }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 flex items-center gap-2 group">
                                <x-masked-phone :number="$student->contact_no" />
                            </div>
                            <div class="text-xs text-gray-500 flex items-center gap-2 group mt-1">
                                WhatsApp: <x-masked-phone :number="$student->whatsapp_no" />
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $student->district->dname ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $student->state->sname ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-600 mt-1 font-medium truncate max-w-[180px]" title="{{ $student->school->schoolname ?? $student->school_name ?? 'N/A' }}">
                                Sch: {{ $student->school->schoolname ?? $student->school_name ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs font-medium text-gray-900">
                                Reg: <span class="font-normal text-gray-600">{{ $student->created_at ? $student->created_at->format('d/m/Y') : 'N/A' }}</span>
                            </div>
                            @php
                                $statusDateLabel = 'Enrolled';
                                $statusDate = null;
                                if ($student->application_status === 'Enrolled') {
                                    $latestBatch = $student->semesterBatches->sortByDesc('pivot.assigned_at')->first();
                                    if ($latestBatch && $latestBatch->pivot->assigned_at) {
                                        $statusDate = \Carbon\Carbon::parse($latestBatch->pivot->assigned_at)->format('d/m/Y');
                                    } else {
                                        $statusDate = $student->updated_at ? $student->updated_at->format('d/m/Y') : 'N/A';
                                    }
                                } elseif ($student->application_status === 'Approved') {
                                    $statusDateLabel = 'Approved';
                                    $statusDate = $student->updated_at ? $student->updated_at->format('d/m/Y') : 'N/A';
                                }
                            @endphp
                            @if($statusDate)
                                <div class="text-xs font-medium text-gray-900 mt-1.5">
                                    {{ $statusDateLabel }}: <span class="font-normal text-gray-600">{{ $statusDate }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $sourceLabel = 'Public Form';
                                $sourceColor = 'bg-gray-100 text-gray-800';
                                if ($student->createdby === 'Migration_Engine') {
                                    $sourceLabel = 'Migrated';
                                    $sourceColor = 'bg-yellow-100 text-yellow-800';
                                } elseif ($student->creator) {
                                    $role = $student->creator->role->rolename ?? '';
                                    if ($role === 'School') {
                                        $sourceLabel = 'School Admin';
                                        $sourceColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($role === 'Initiator') {
                                        $sourceLabel = 'Initiator';
                                        $sourceColor = 'bg-purple-100 text-purple-800';
                                    } elseif ($role === 'Admin') {
                                        $sourceLabel = 'Admin (Manual)';
                                        $sourceColor = 'bg-indigo-100 text-indigo-800';
                                    } else {
                                        $sourceLabel = $role;
                                    }
                                }
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $sourceColor }}">
                                {{ $sourceLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $student->fees_status === 'Paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $student->fees_status ?: 'Unpaid' }}
                            </span>
                            @php
                                $now = now();
                                $allStudentBatches = $student->semesterBatches;
                                $activeBatches = $allStudentBatches->where('pivot.status', 0);

                                // Currently running active batches (start_date <= now)
                                $runningActiveBatches = $activeBatches->filter(function($sem) use ($now) {
                                    return !$sem->start_date || $sem->start_date->lessThanOrEqualTo($now);
                                })->sortByDesc(function($sem) {
                                    return [
                                        $sem->start_date ? $sem->start_date->timestamp : 0,
                                        $sem->pivot->assigned_at ? \Carbon\Carbon::parse($sem->pivot->assigned_at)->timestamp : 0,
                                        $sem->sembatches_id
                                    ];
                                });

                                if ($runningActiveBatches->isNotEmpty()) {
                                    $activeSemester = $runningActiveBatches->first();
                                } elseif ($activeBatches->isNotEmpty()) {
                                    // Earliest starting active batch if none started yet
                                    $activeSemester = $activeBatches->sortBy(function($sem) {
                                        return [
                                            $sem->start_date ? $sem->start_date->timestamp : PHP_INT_MAX,
                                            $sem->pivot->assigned_at ? \Carbon\Carbon::parse($sem->pivot->assigned_at)->timestamp : PHP_INT_MAX,
                                            $sem->sembatches_id
                                        ];
                                    })->first();
                                } else {
                                    // Fallback: latest batch among all batches
                                    $activeSemester = $allStudentBatches->sortByDesc(function($sem) {
                                        return [
                                            $sem->start_date ? $sem->start_date->timestamp : 0,
                                            $sem->pivot->assigned_at ? \Carbon\Carbon::parse($sem->pivot->assigned_at)->timestamp : 0,
                                            $sem->sembatches_id
                                        ];
                                    })->first();
                                }

                                $activeFeePlanName = null;
                                if ($activeSemester) {
                                    $feePlan = $activeSemester->feePlans
                                        ->where('fee_name', $student->subscription)
                                        ->first();
                                    
                                    if (!$feePlan && $student->school_id) {
                                        $feePlan = $activeSemester->feePlans
                                            ->where('school_id', $student->school_id)
                                            ->first();
                                    }

                                    if (!$feePlan) {
                                        $feePlan = $activeSemester->feePlans
                                            ->whereNull('school_id')
                                            ->first();
                                    }

                                    if ($feePlan) {
                                        $activeFeePlanName = $feePlan->fee_name;
                                    }
                                }

                                if (!$activeFeePlanName) {
                                    $activeFeePlanName = $student->subscription;
                                }

                                $upcomingSemesters = $activeBatches->filter(function($sem) use ($now, $activeSemester) {
                                    if ($activeSemester && $sem->sembatches_id == $activeSemester->sembatches_id) {
                                        return false;
                                    }
                                    return $sem->start_date && $sem->start_date->isAfter($now);
                                })->sortBy('start_date');

                                $upcomingCount = $upcomingSemesters->count();

                                // Payload for Batch History Modal
                                $historyTimeline = [];
                                foreach ($allStudentBatches->sortBy('start_date') as $sem) {
                                    $isCurrent = ($activeSemester && $sem->sembatches_id == $activeSemester->sembatches_id);
                                    $isUpcoming = ($sem->pivot->status == 0 && $sem->start_date && $sem->start_date->isAfter($now) && !$isCurrent);
                                    $isCompleted = ($sem->pivot->status == 1 || ($sem->end_date && $sem->end_date->isBefore($now) && !$isCurrent));

                                    $statusLabel = 'Assigned';
                                    $statusBadgeClass = 'bg-blue-50 text-blue-700 border-blue-200';

                                    if ($isCurrent) {
                                        $statusLabel = 'Current Active';
                                        $statusBadgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-300 font-bold';
                                    } elseif ($isUpcoming) {
                                        $statusLabel = 'Upcoming Batch';
                                        $statusBadgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200 font-bold';
                                    } elseif ($isCompleted) {
                                        $statusBadgeClass = 'bg-gray-100 text-gray-600 border-gray-200';
                                        $statusLabel = ($sem->pivot->status == 1) ? 'Shifted / Completed' : 'Completed';
                                    }

                                    $semFeePlan = $sem->feePlans->where('fee_name', $student->subscription)->first()
                                        ?? $sem->feePlans->first();

                                    $historyTimeline[] = [
                                        'batch_id' => $sem->sembatches_id,
                                        'display_name' => $sem->display_name ?: ($sem->edmingle_batch_name ?: 'Batch #'.$sem->sembatches_id),
                                        'fee_plan' => $semFeePlan ? $semFeePlan->fee_name : ($student->subscription ?: 'N/A'),
                                        'start_date' => $sem->start_date ? $sem->start_date->format('d M, Y') : 'N/A',
                                        'end_date' => $sem->end_date ? $sem->end_date->format('d M, Y') : 'N/A',
                                        'assigned_at' => $sem->pivot->assigned_at ? \Carbon\Carbon::parse($sem->pivot->assigned_at)->format('d M, Y H:i') : 'N/A',
                                        'status_label' => $statusLabel,
                                        'status_badge_class' => $statusBadgeClass,
                                        'is_current' => $isCurrent,
                                        'is_upcoming' => $isUpcoming,
                                    ];
                                }

                                $migrationRecords = [];
                                if ($student->histories && $student->histories->count() > 0) {
                                    foreach ($student->histories->sortByDesc('created_at') as $h) {
                                        $migrationRecords[] = [
                                            'from_batch' => $h->fromBatch->display_name ?? 'Initial Batch',
                                            'to_batch' => $h->toBatch->display_name ?? 'N/A',
                                            'remarks' => $h->remarks ?: 'Batch Shift',
                                            'date' => $h->created_at ? $h->created_at->format('d M, Y H:i') : 'N/A',
                                            'executor' => trim(($h->executor->fname ?? 'System') . ' ' . ($h->executor->lname ?? ''))
                                        ];
                                    }
                                }

                                $batchModalPayload = [
                                    'student_id' => $student->student_id,
                                    'student_name' => trim($student->fname . ' ' . $student->mname . ' ' . $student->lname),
                                    'enrolment_no' => $student->enrolment_no ?: ('ID: ' . $student->student_id),
                                    'school_name' => $student->school->schoolname ?? $student->school_name ?? 'N/A',
                                    'subscription' => $student->subscription ?: 'N/A',
                                    'current_batch_name' => $activeSemester ? ($activeSemester->display_name ?? 'N/A') : 'N/A',
                                    'current_fee_plan' => $activeFeePlanName ?: ($student->subscription ?: 'N/A'),
                                    'timeline' => $historyTimeline,
                                    'shifts' => $migrationRecords,
                                ];
                            @endphp

                            <div onclick='openStudentBatchModal(@json($batchModalPayload))' 
                                 class="group cursor-pointer p-2 rounded-xl transition-all hover:bg-cyan-50/80 border border-transparent hover:border-[#00bde0]/40 relative"
                                 title="Click to view full batch assignment & shift history">
                                @if($activeSemester)
                                    <div class="text-xs font-bold text-gray-900 flex items-center justify-between gap-1">
                                        <span>Current: {{ $activeSemester->display_name ?? 'N/A' }}</span>
                                        <svg class="w-3.5 h-3.5 text-[#00bde0] opacity-70 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-xs text-gray-600 font-medium mt-0.5 group-hover:text-[#00bde0] transition-colors">
                                        {{ $activeFeePlanName }}
                                    </div>
                                    @if($upcomingCount > 0)
                                        <div class="text-[10px] text-[#00bde0] font-extrabold mt-1 inline-flex items-center gap-1 bg-[#00bde0]/10 px-1.5 py-0.5 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#00bde0] animate-pulse"></span>
                                            +{{ $upcomingCount }} upcoming batch
                                        </div>
                                    @endif
                                @else
                                    <div class="text-xs text-gray-500 font-semibold flex items-center justify-between">
                                        <span>{{ $activeFeePlanName ?: 'Not Selected' }}</span>
                                        <svg class="w-3.5 h-3.5 text-[#00bde0] opacity-70 group-hover:opacity-100 transition-opacity flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($student->application_status === 'Documents Submitted')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending Review
                                </span>
                            @elseif($student->application_status === 'Enrollment in Progress')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Payments in Progress
                                </span>
                            @elseif($student->application_status === 'Approved')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    Approved
                                </span>
                            @elseif($student->application_status === 'Enrolled')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                    Enrolled
                                </span>
                            @elseif($student->application_status === 'Done')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    Done
                                </span>
                            @elseif($student->application_status === 'Rejected')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            @elseif($student->invoices()->whereHas('payments', function($q) { $q->where('status', '3'); })->exists() && $student->fees_status !== 'Paid')
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Failed Payment
                                </span>
                            @elseif(!$student->document || !$student->document->photo)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Document Pending
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $student->application_status ?: 'Initiated' }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                @if($student->trashed())
                                    @if(auth()->user()->hasPermission(10042) || auth()->user()->hasPermission(11201) || auth()->user()->hasPermission(10001) || auth()->user()->hasPermission(11602))
                                        <form action="{{ route('admin.registrations.restore', $student->student_id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-emerald-300 rounded-lg shadow-sm text-sm font-medium text-emerald-600 bg-white hover:bg-emerald-50 hover:text-emerald-700 transition-all duration-200" title="Restore Student">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.registrations.show', $student->student_id) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-br from-[#00bde0] to-[#0099b8] hover:shadow-md transition-all duration-200">
                                        Review
                                    </a>
                                @else
                                    <a href="{{ route('students.edit', $student->student_id) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 hover:text-[#00bde0] transition-all duration-200" title="Edit Student">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <a href="{{ route('admin.registrations.show', $student->student_id) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-br from-[#00bde0] to-[#0099b8] hover:shadow-md transition-all duration-200">
                                        Review
                                    </a>
                                    @if(auth()->user()->hasPermission(10042) || auth()->user()->hasPermission(11201) || auth()->user()->hasPermission(10001) || auth()->user()->hasPermission(11602))
                                        <button type="button" onclick="openDeleteModal('{{ $student->student_id }}', '{{ route('admin.registrations.destroy', $student->student_id) }}')" class="inline-flex items-center px-3 py-2 border border-red-300 rounded-lg shadow-sm text-sm font-medium text-red-600 bg-white hover:bg-red-50 hover:text-red-700 transition-all duration-200" title="Delete Student">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-gray-500 font-medium">No registrations found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 pagination-container">
            {{ $students->appends(request()->except('page'))->links() }}
        </div>
    @endif
</div>

<!-- Student Batch Details & History Modal -->
<div id="student-batch-details-modal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeStudentBatchModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#00bde0] bg-opacity-10 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="sbm-student-name">
                                Student Batch Details
                            </h3>
                            <button type="button" onclick="closeStudentBatchModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-500 font-sans mt-0.5" id="sbm-student-sub">
                            Enrolment & Fee Plan Details
                        </p>

                        <!-- Content Sections -->
                        <div class="mt-5 space-y-5 max-h-[60vh] overflow-y-auto pr-1">
                            <!-- Top Card: Current Active Batch & Plan -->
                            <div class="bg-gradient-to-r from-cyan-50/70 via-white to-cyan-50/40 rounded-xl p-4 border border-[#00bde0]/30 shadow-sm relative">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[10px] font-bold text-[#00bde0] uppercase tracking-wider">Current Active Semester</span>
                                    <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-[#00bde0] text-white">
                                        Active Semester
                                    </span>
                                </div>
                                <h4 class="text-base font-bold text-gray-900" id="sbm-current-batch">Semester Batch Name</h4>
                                <p class="text-xs text-gray-600 font-semibold mt-0.5" id="sbm-current-plan">Fee Plan Name</p>
                                <div class="mt-3 pt-3 border-t border-cyan-100/80 grid grid-cols-2 gap-4 text-xs">
                                    <div>
                                        <span class="text-gray-400 font-medium uppercase text-[10px] block">School / Institution</span>
                                        <span class="text-gray-800 font-bold" id="sbm-school-name">N/A</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 font-medium uppercase text-[10px] block">Subscribed Plan</span>
                                        <span class="text-gray-800 font-bold" id="sbm-subscription">N/A</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline: Batch Assignment & Shift History -->
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Batch Assignment Timeline & History
                                </h4>
                                <div id="sbm-timeline-container" class="space-y-3">
                                    <!-- Populated dynamically via JS -->
                                </div>
                            </div>

                            <!-- Shift Logs (If available) -->
                            <div id="sbm-shift-logs-section" class="hidden">
                                <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    Batch Shift Audit Trail
                                </h4>
                                <div id="sbm-shifts-container" class="space-y-2">
                                    <!-- Populated dynamically via JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                <button type="button" onclick="closeStudentBatchModal()" class="w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm transition-all">
                    Close Details
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof window.openStudentBatchModal === 'undefined') {
        window.openStudentBatchModal = function(data) {
            document.getElementById('sbm-student-name').textContent = data.student_name || 'Student Details';
            document.getElementById('sbm-student-sub').textContent = (data.enrolment_no ? data.enrolment_no + ' • ' : '') + (data.subscription || 'N/A');
            document.getElementById('sbm-current-batch').textContent = data.current_batch_name || 'N/A';
            document.getElementById('sbm-current-plan').textContent = data.current_fee_plan || 'N/A';
            document.getElementById('sbm-school-name').textContent = data.school_name || 'N/A';
            document.getElementById('sbm-subscription').textContent = data.subscription || 'N/A';

            // Render Timeline
            const timelineContainer = document.getElementById('sbm-timeline-container');
            if (timelineContainer) {
                timelineContainer.innerHTML = '';
                if (data.timeline && data.timeline.length > 0) {
                    data.timeline.forEach(function(item) {
                        const card = document.createElement('div');
                        card.className = 'bg-white p-4 rounded-xl border ' + (item.is_current ? 'border-[#00bde0] ring-1 ring-[#00bde0]/30' : 'border-gray-200') + ' shadow-sm space-y-2';

                        card.innerHTML = `
                            <div class="flex justify-between items-start gap-2">
                                <div>
                                    <span class="inline-block px-2.5 py-0.5 text-[10px] font-bold rounded-full border ${item.status_badge_class}">
                                        ${item.status_label}
                                    </span>
                                    <h5 class="text-sm font-bold text-gray-900 mt-1">${item.display_name}</h5>
                                    <p class="text-xs text-gray-600 font-medium">Plan: ${item.fee_plan}</p>
                                </div>
                                <div class="text-right text-[11px] text-gray-500">
                                    <span class="font-semibold text-gray-700">Duration:</span>
                                    <div class="font-mono text-gray-600">${item.start_date} - ${item.end_date}</div>
                                </div>
                            </div>
                            <div class="text-[11px] text-gray-400 pt-2 border-t border-gray-100 flex justify-between items-center">
                                <span>Assigned On: <strong class="text-gray-600">${item.assigned_at}</strong></span>
                                ${item.is_current ? '<span class="text-[#00bde0] font-bold">● Active Semester</span>' : ''}
                                ${item.is_upcoming ? '<span class="text-indigo-600 font-bold">● Scheduled Upcoming</span>' : ''}
                            </div>
                        `;
                        timelineContainer.appendChild(card);
                    });
                } else {
                    timelineContainer.innerHTML = '<p class="text-xs text-gray-500 italic p-3 bg-white rounded-xl border border-gray-200">No semester batch records found for this student.</p>';
                }
            }

            // Render Shift Logs
            const shiftLogsSection = document.getElementById('sbm-shift-logs-section');
            const shiftsContainer = document.getElementById('sbm-shifts-container');
            if (shiftLogsSection && shiftsContainer) {
                shiftsContainer.innerHTML = '';
                if (data.shifts && data.shifts.length > 0) {
                    shiftLogsSection.classList.remove('hidden');
                    data.shifts.forEach(function(shift) {
                        const logItem = document.createElement('div');
                        logItem.className = 'bg-amber-50/60 p-3 rounded-lg border border-amber-200/80 text-xs flex justify-between items-start gap-2';
                        logItem.innerHTML = `
                            <div>
                                <p class="font-bold text-amber-900">Shifted from ${shift.from_batch} → ${shift.to_batch}</p>
                                <p class="text-amber-700 text-[11px] mt-0.5">Remarks: ${shift.remarks}</p>
                            </div>
                            <div class="text-right text-[10px] text-amber-600">
                                <div>${shift.date}</div>
                                <div>By: ${shift.executor}</div>
                            </div>
                        `;
                        shiftsContainer.appendChild(logItem);
                    });
                } else {
                    shiftLogsSection.classList.add('hidden');
                }
            }

            const modal = document.getElementById('student-batch-details-modal');
            if (modal) modal.classList.remove('hidden');
        };

        window.closeStudentBatchModal = function() {
            const modal = document.getElementById('student-batch-details-modal');
            if (modal) modal.classList.add('hidden');
        };
    }
</script>
