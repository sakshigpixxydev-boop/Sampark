@extends('layouts.app')

@section('title', 'Student Migration History')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Student Migration History</h2>
                <p class="mt-2 text-sm text-gray-600">Track and review all historical student semester batch shifts and assignments</p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ route('admin.registrations.index') }}" class="px-4 py-2 border border-gray-300 rounded-xl font-semibold shadow-sm text-gray-700 bg-white hover:bg-gray-50 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Back to Registrations
                </a>
            </div>
        </div>

        <!-- Filters Panel -->
        <div class="mb-6">
            <x-filter-panel action="{{ route('admin.registrations.history') }}">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search Student</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Name, email, enrolment no..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    </div>

                    <!-- From Batch Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From Batch</label>
                        <select name="from_batch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            <option value="">All Batches</option>
                            @foreach($batches as $batch)
                                @php
                                    $planNames = $batch->feePlans->pluck('fee_name')->implode(' / ');
                                    $label = !empty($planNames) ? $planNames : ($batch->display_name ?? 'Batch');
                                @endphp
                                <option value="{{ $batch->sembatches_id }}" {{ request('from_batch_id') == $batch->sembatches_id ? 'selected' : '' }}>
                                    {{ $label }} ({{ optional($batch->start_date)->format('d/m/Y') ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- To Batch Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To Batch</label>
                        <select name="to_batch_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                            <option value="">All Batches</option>
                            @foreach($batches as $batch)
                                @php
                                    $planNames = $batch->feePlans->pluck('fee_name')->implode(' / ');
                                    $label = !empty($planNames) ? $planNames : ($batch->display_name ?? 'Batch');
                                @endphp
                                <option value="{{ $batch->sembatches_id }}" {{ request('to_batch_id') == $batch->sembatches_id ? 'selected' : '' }}>
                                    {{ $label }} ({{ optional($batch->start_date)->format('d/m/Y') ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-filter-panel>
        </div>

        <!-- History Records Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">From Semester Batch</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">To Semester Batch</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Executor</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Migration Date</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($histories as $log)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    @if($log->student)
                                        <div class="flex items-center">
                                            <div class="h-9 w-9 rounded-full bg-[#00bde0] flex items-center justify-center text-white font-bold text-sm">
                                                {{ substr($log->student->fname, 0, 1) }}{{ substr($log->student->lname, 0, 1) }}
                                            </div>
                                            <div class="ml-3">
                                                <a href="{{ route('admin.registrations.show', $log->student->student_id) }}" class="text-sm font-semibold text-gray-900 hover:text-[#00bde0]">
                                                    {{ $log->student->fname }} {{ $log->student->lname }}
                                                </a>
                                                <div class="text-xs text-gray-500">{{ $log->student->email }}</div>
                                                <div class="text-[10px] text-gray-400 font-medium">Standard: {{ $log->student->standard->stdname ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Student Not Found (Deleted)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->fromBatch)
                                        <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-gray-100 text-gray-700 border border-gray-200">
                                            {{ $log->fromBatch->display_name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">None (First Enrollment)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->toBatch)
                                        <span class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-100">
                                            {{ $log->toBatch->display_name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-red-500 italic">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 font-medium">
                                        {{ $log->executor->fname ?? 'System' }} {{ $log->executor->lname ?? '' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $log->executor->role->rolename ?? 'System Engine' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $log->created_at->format('d/m/Y H:i A') }}
                                    <span class="block text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="max-w-xs truncate" title="{{ $log->remarks }}">
                                        {{ $log->remarks ?: 'No remarks' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="text-gray-500 font-medium">No migration logs found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($histories->hasPages())
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $histories->appends(request()->except('page'))->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
