@extends('layouts.app')

@section('title', 'School Registrations')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">School Registrations</h2>
                <p class="mt-2 text-sm text-gray-600">Students from bulk imports and the school registration link</p>
            </div>
            <a href="{{ route('admin.dashboard') }}"
                class="flex-shrink-0 px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold shadow-sm transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Dashboard
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="{{ route('school.registrations.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="{{ $schools->isNotEmpty() ? '' : 'lg:col-span-2' }}">
                        <label for="q" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Search</label>
                        <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="Name, email, phone, enrolment no."
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>

                    <div>
                        <label for="source" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Source</label>
                        <select name="source" id="source" class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent bg-white">
                            <option value="">All sources</option>
                            <option value="link" {{ request('source') === 'link' ? 'selected' : '' }}>Registration Link</option>
                            <option value="bulk" {{ request('source') === 'bulk' ? 'selected' : '' }}>Bulk Import</option>
                        </select>
                    </div>

                    @if($schools->isNotEmpty())
                        <div>
                            <label for="school_id" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">School</label>
                            <select name="school_id" id="school_id" class="searchable-select block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent bg-white">
                                <option value="">All schools</option>
                                @foreach($schools as $schoolOption)
                                    <option value="{{ $schoolOption->school_id }}" {{ (string) request('school_id') === (string) $schoolOption->school_id ? 'selected' : '' }}>
                                        {{ $schoolOption->schoolname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label for="payment_status" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Payment Status</label>
                        <select name="payment_status" id="payment_status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent bg-white">
                            <option value="">All payments</option>
                            <option value="Pending" {{ request('payment_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Approved" {{ request('payment_status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Rejected" {{ request('payment_status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        <p class="mt-1 text-[11px] text-gray-400">Applies to link registrations</p>
                    </div>

                    <div>
                        <label for="approval_status" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Approval Status</label>
                        <select name="approval_status" id="approval_status" class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent bg-white">
                            <option value="">All approvals</option>
                            <option value="Pending" {{ request('approval_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Approved" {{ request('approval_status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                            <option value="Rejected" {{ request('approval_status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label for="date_from" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Registered From</label>
                        <input type="text" name="date_from" id="date_from" value="{{ request('date_from') }}" placeholder="dd/mm/yyyy"
                            class="date-picker block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>

                    <div>
                        <label for="date_to" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1.5">Registered To</label>
                        <input type="text" name="date_to" id="date_to" value="{{ request('date_to') }}" placeholder="dd/mm/yyyy"
                            class="date-picker block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-gradient-to-br from-[#00bde0] to-[#0099b8] hover:shadow-md text-white rounded-xl font-semibold text-sm shadow-sm transition-all">
                        Apply Filters
                    </button>
                    <a href="{{ route('school.registrations.index') }}" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-semibold text-sm shadow-sm transition-all">
                        Reset
                    </a>
                    <span class="ml-auto text-sm text-gray-500">{{ number_format($registrations->total()) }} registration{{ $registrations->total() === 1 ? '' : 's' }}</span>
                </div>
            </form>
        </div>

        <!-- Listing -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Contact</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Source</th>
                            @if($schools->isNotEmpty())
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">School</th>
                            @endif
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fee Plan</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Approval</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Registered</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($registrations as $student)
                            @php $viaLink = (bool) $student->registered_via_school_link; @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ trim($student->fname . ' ' . $student->mname . ' ' . $student->lname) }}</p>
                                    <p class="text-xs text-gray-500">
                                        #{{ $student->student_id }}
                                        @if($student->enrolment_no)
                                            &middot; {{ $student->enrolment_no }}
                                        @endif
                                        @if($student->standard)
                                            &middot; {{ $student->standard->stdname }}
                                        @endif
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-700">{{ $student->email ?: '—' }}</p>
                                    <p class="text-xs text-gray-500">{{ $student->contact_no ?: '—' }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($viaLink)
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Registration Link</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Bulk Import</span>
                                    @endif
                                    @if($student->uploadBatch)
                                        <p class="mt-1 text-[11px] text-gray-500">{{ $student->uploadBatch->batch_code }}</p>
                                    @endif
                                </td>
                                @if($schools->isNotEmpty())
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $student->school->schoolname ?? '—' }}</td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @if($viaLink)
                                        {{ $student->school_payment_plan_name ?? '—' }}
                                        @if($student->school_payment_amount !== null)
                                            <span class="block text-xs font-semibold text-[#0099b8]">₹{{ number_format((float) $student->school_payment_amount, 2) }}</span>
                                        @endif
                                    @else
                                        {{ $student->subscription ?? '—' }}
                                        @if($student->uploadBatch && $student->uploadBatch->fee_per_student !== null)
                                            <span class="block text-xs font-semibold text-[#0099b8]">₹{{ number_format((float) $student->uploadBatch->fee_per_student, 2) }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($viaLink)
                                        @include('school.registrations.partials.status-badge', ['status' => $student->school_payment_status])
                                    @else
                                        @include('school.registrations.partials.status-badge', ['status' => $student->fees_status])
                                        @if($student->uploadBatch)
                                            <p class="mt-1 text-[11px] text-gray-500">Batch {{ $student->uploadBatch->status }}</p>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @include('school.registrations.partials.status-badge', ['status' => $student->school_approval_status])
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $student->created_at ? $student->created_at->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <a href="{{ route('admin.registrations.show', $student->student_id) }}"
                                        class="text-sm font-semibold text-[#0099b8] hover:text-[#007b94]">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $schools->isNotEmpty() ? 9 : 8 }}" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-500">No registrations match these filters</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($registrations->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $registrations->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
