@extends('layouts.app')

@section('title', (auth()->user()->role ? auth()->user()->role->rolename : 'Admin') . ' Dashboard')

@push('styles')
<style>
    /* Custom styling for standardizing the UI/UX */
    .dashboard-card {
        background: #ffffff !important;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 4px 12px rgba(0, 0, 0, 0.03);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dashboard-card:hover {
        transform: translateY(-4px);
        border-color: #00bde0;
        box-shadow: 0 12px 20px -8px rgba(0, 189, 224, 0.12), 0 4px 20px 0 rgba(0, 0, 0, 0.04);
    }
    .chart-container-sm {
        position: relative;
        height: 60px;
        width: 120px;
    }
    .chart-container-md {
        position: relative;
        height: 300px;
        width: 100%;
        background: #ffffff;
    }
    .glass-icon {
        background: rgba(0, 189, 224, 0.1);
        color: #00bde0;
    }
</style>
@endpush

@section('content')
<div class="py-6 bg-[#f8fafc] min-h-screen">
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-end gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 tracking-tight">{{ auth()->user()->role ? auth()->user()->role->rolename : 'Admin' }} Dashboard</h2>
                <p class="mt-1 text-sm text-gray-500">
                    @if(auth()->user()->hasRole('School') || auth()->user()->hasRole('Initiator'))
                        Overview of assigned metrics and activities
                    @else
                        Overview of system-wide metrics and activities
                    @endif
                </p>
            </div>
            
            <!-- Date Filter Form -->
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="relative">
                    <select name="days" onchange="this.form.submit()" class="appearance-none bg-white border border-gray-200 text-gray-600 pl-10 pr-10 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 shadow-sm cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#00bde0]">
                        <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30" {{ (request('days') == 30 || !request('days')) ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90" {{ request('days') == 90 ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="365" {{ request('days') == 365 ? 'selected' : '' }}>Last Year</option>
                    </select>
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                <!-- Preserve existing filters -->
                @if(request('sem_standard'))<input type="hidden" name="sem_standard" value="{{ request('sem_standard') }}">@endif
                @if(request('sem_school'))<input type="hidden" name="sem_school" value="{{ request('sem_school') }}">@endif
                @if(request('sem_district'))<input type="hidden" name="sem_district" value="{{ request('sem_district') }}">@endif
            </form>
        </div>

        @if($cohort)
            @php
                $cohortHints = [
                    'school'  => 'School Users — students who came in via a school link or bulk import',
                    'regular' => 'Regular Users — students who registered themselves on the public form',
                ];
            @endphp
            <div class="mb-8 flex items-start gap-3 p-4 rounded-xl border border-[#00bde0]/30 bg-[#00bde0]/5">
                <svg class="w-5 h-5 text-[#0099b8] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                <p class="text-sm font-semibold text-gray-700">
                    {{ $cohortHints[$cohort] }}.
                    <span class="font-normal text-gray-500">Change this from the selector in the header to see everything.</span>
                </p>
            </div>
        @endif

        <!-- Weekly Performance (Sparklines) -->
        <div class="mb-8">
            <h3 class="text-sm font-semibold text-gray-500 mb-4 uppercase tracking-wider">Weekly Performance (Last 7 Days)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                @if(auth()->user()->canAccessModule(10041))
                <!-- Sparkline 1: Total Registrations -->
                <div class="dashboard-card p-4 lg:p-5 flex items-center justify-between overflow-hidden">
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="flex items-center gap-1.5 mb-1">
                            <p class="text-xs lg:text-sm font-medium text-gray-500 truncate">Total Registrations</p>
                            <svg class="w-3 h-3 lg:w-4 lg:h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="flex items-baseline gap-2 flex-wrap">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900">{{ number_format($totalStudents) }}</h4>
                            @if($regTrend)
                                @if($regTrend['type'] === 'increase')
                                    <span class="text-[10px] lg:text-xs font-semibold text-green-500 flex items-center whitespace-nowrap"><svg class="w-2.5 h-2.5 lg:w-3 lg:h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> {{ $regTrend['value'] }}%</span>
                                @else
                                    <span class="text-[10px] lg:text-xs font-semibold text-red-500 flex items-center whitespace-nowrap"><svg class="w-2.5 h-2.5 lg:w-3 lg:h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg> {{ $regTrend['value'] }}%</span>
                                @endif
                            @endif
                        </div>
                        <p class="text-[10px] lg:text-[11px] text-gray-400 mt-0.5 truncate">Total vs Prev 7 Days</p>
                    </div>
                    <div class="chart-container-sm flex-shrink-0" style="width: 80px;">
                        <canvas id="sparkline1"></canvas>
                    </div>
                </div>
                @endif

                @if(auth()->user()->canAccessModule(10041))
                <!-- Sparkline 2: Active Students -->
                <div class="dashboard-card p-4 lg:p-5 flex items-center justify-between overflow-hidden">
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="flex items-center gap-1.5 mb-1">
                            <p class="text-xs lg:text-sm font-medium text-gray-500 truncate">Active Students</p>
                            <svg class="w-3 h-3 lg:w-4 lg:h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="flex items-baseline gap-2 flex-wrap">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900">{{ number_format($activeStudents) }}</h4>
                            @if($activeTrend)
                                @if($activeTrend['type'] === 'increase')
                                    <span class="text-[10px] lg:text-xs font-semibold text-green-500 flex items-center whitespace-nowrap"><svg class="w-2.5 h-2.5 lg:w-3 lg:h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> {{ $activeTrend['value'] }}%</span>
                                @else
                                    <span class="text-[10px] lg:text-xs font-semibold text-red-500 flex items-center whitespace-nowrap"><svg class="w-2.5 h-2.5 lg:w-3 lg:h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg> {{ $activeTrend['value'] }}%</span>
                                @endif
                            @endif
                        </div>
                        <p class="text-[10px] lg:text-[11px] text-gray-400 mt-0.5 truncate">Total vs Prev 7 Days</p>
                    </div>
                    <div class="chart-container-sm flex-shrink-0" style="width: 80px;">
                        <canvas id="sparkline2"></canvas>
                    </div>
                </div>
                @endif

                @if(auth()->user()->canAccessModule(11201))
                <!-- Sparkline 3: Pending Reviews -->
                <div class="dashboard-card p-4 lg:p-5 flex items-center justify-between overflow-hidden">
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="flex items-center gap-1.5 mb-1">
                            <p class="text-xs lg:text-sm font-medium text-gray-500 truncate">Pending Reviews</p>
                            <svg class="w-3 h-3 lg:w-4 lg:h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="flex items-baseline gap-2 flex-wrap">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900">{{ number_format($pendingReviews) }}</h4>
                            @if($pendingRevTrend)
                                @if($pendingRevTrend['type'] === 'increase')
                                    <span class="text-[10px] lg:text-xs font-semibold text-green-500 flex items-center whitespace-nowrap"><svg class="w-2.5 h-2.5 lg:w-3 lg:h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> {{ $pendingRevTrend['value'] }}%</span>
                                @else
                                    <span class="text-[10px] lg:text-xs font-semibold text-red-500 flex items-center whitespace-nowrap"><svg class="w-2.5 h-2.5 lg:w-3 lg:h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg> {{ $pendingRevTrend['value'] }}%</span>
                                @endif
                            @endif
                        </div>
                        <p class="text-[10px] lg:text-[11px] text-gray-400 mt-0.5 truncate">Total vs Prev 7 Days</p>
                    </div>
                    <div class="chart-container-sm flex-shrink-0" style="width: 80px;">
                        <canvas id="sparkline3"></canvas>
                    </div>
                </div>
                @endif

                @if(auth()->user()->canAccessModule(10071))
                <!-- Sparkline 4: Today's Revenue -->
                <div class="dashboard-card p-4 lg:p-5 flex items-center justify-between overflow-hidden">
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="flex items-center gap-1.5 mb-1">
                            <p class="text-xs lg:text-sm font-medium text-gray-500 truncate">Today's Revenue</p>
                            <svg class="w-3 h-3 lg:w-4 lg:h-4 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="flex items-baseline gap-2 flex-wrap">
                            <h4 class="text-xl lg:text-2xl font-bold text-gray-900 truncate" title="₹{{ number_format($todaysPayment) }}">₹{{ number_format($todaysPayment) }}</h4>
                            @if($revTrend)
                                @if($revTrend['type'] === 'increase')
                                    <span class="text-[10px] lg:text-xs font-semibold text-green-500 flex items-center whitespace-nowrap"><svg class="w-2.5 h-2.5 lg:w-3 lg:h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg> {{ $revTrend['value'] }}%</span>
                                @else
                                    <span class="text-[10px] lg:text-xs font-semibold text-red-500 flex items-center whitespace-nowrap"><svg class="w-2.5 h-2.5 lg:w-3 lg:h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg> {{ $revTrend['value'] }}%</span>
                                @endif
                            @endif
                        </div>
                        <p class="text-[10px] lg:text-[11px] text-gray-400 mt-0.5 truncate">Today vs Yesterday</p>
                    </div>
                    <div class="chart-container-sm flex-shrink-0" style="width: 80px;">
                        <canvas id="sparkline4"></canvas>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Main Charts Area -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Left Chart: Daily Active Users -->
            <div class="dashboard-card p-6 flex flex-col">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Daily Active Users</h3>
                        <div class="flex items-end gap-3">
                            <span class="text-4xl font-bold text-gray-900">{{ number_format($dailyActiveUsersAvg) }}</span>
                            <span class="text-sm text-gray-400 mb-1">Avg of last {{ $days }} days</span>
                        </div>
                    </div>
                </div>
                <div class="chart-container-md flex-1 mt-auto">
                    <canvas id="dailyActiveChart"></canvas>
                </div>
            </div>

            <!-- Right Chart: Daily Registrations -->
            <div class="dashboard-card p-6 flex flex-col">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Daily Registrations</h3>
                        <div class="flex items-end gap-3">
                            <span class="text-4xl font-bold text-gray-900">{{ number_format($dailyRegistrationsAvg) }}</span>
                            <span class="text-sm text-gray-400 mb-1">Avg of last {{ $days }} days</span>
                        </div>
                    </div>
                </div>
                <div class="chart-container-md flex-1 mt-auto">
                    <canvas id="dailyRegistrationsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Enrollment Pipeline Chart (Full Width) -->
        <div class="dashboard-card p-6 mb-8 flex flex-col">
            <div class="flex flex-col sm:flex-row justify-between sm:items-start mb-6 gap-4">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Enrollments Pipeline</h3>
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-bold text-gray-900">{{ number_format($enrollmentsTotalXDays) }}</span>
                        <span class="text-sm text-gray-400 mb-1">Total Registrations (Last {{ $days }} days)</span>
                    </div>
                </div>
            </div>
            <div class="chart-container-md flex-1">
                <canvas id="enrollmentsChart"></canvas>
            </div>
        </div>

        <!-- System Overview Cards (Moved and Improved) -->
        <div class="dashboard-card p-6 mb-8">
            <div class="mb-6 pb-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900">System Overview</h3>
                <p class="mt-1 text-sm text-gray-500">Summary of platform usage and metrics</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                @if(auth()->user()->canAccessModule(10041))
                <!-- Total Students -->
                <a href="{{ route('students.index') }}" class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#00bde0] flex items-center justify-center flex-shrink-0 group-hover:bg-[#00bde0] group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Total Students</p>
                        <h4 class="text-xl font-bold text-gray-900 mt-0.5 truncate">{{ number_format($totalStudents) }}</h4>
                    </div>
                </a>
                @endif

                @if(auth()->user()->canAccessModule(10031) && $cohort !== 'regular')
                <!-- Total Schools -->
                <a href="{{ route('schools.index') }}" class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-500 flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Total Schools</p>
                        <h4 class="text-xl font-bold text-gray-900 mt-0.5 truncate">{{ number_format($totalSchools) }}</h4>
                    </div>
                </a>
                @endif

                @php
                    $canAccessRegistrations = auth()->user()->canAccessModule(11201);
                @endphp

                @if(auth()->user()->canAccessModule(10041))
                <!-- Pending Students -->
                @if($canAccessRegistrations)
                <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                @else
                <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                @endif
                    <div class="w-12 h-12 rounded-xl bg-yellow-50 text-yellow-500 flex items-center justify-center flex-shrink-0 transition-colors group-hover:bg-yellow-500 group-hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Pending Students</p>
                        <h4 class="text-xl font-bold text-gray-900 mt-0.5 truncate">{{ number_format($pendingStudents) }}</h4>
                    </div>
                @if($canAccessRegistrations)
                </a>
                @else
                </div>
                @endif
                @endif

                @if(auth()->user()->canAccessModule(10061))
                <!-- Pending Fees -->
                @if($canAccessRegistrations)
                <a href="{{ route('admin.registrations.index', ['payment_status' => 'unpaid']) }}" class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                @else
                <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                @endif
                    <div class="w-12 h-12 rounded-xl bg-orange-50 text-orange-500 flex items-center justify-center flex-shrink-0 transition-colors group-hover:bg-orange-500 group-hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Pending Fees</p>
                        <h4 class="text-xl font-bold text-gray-900 mt-0.5 truncate" title="₹{{ number_format($pendingFees) }}">₹{{ number_format($pendingFees) }}</h4>
                    </div>
                @if($canAccessRegistrations)
                </a>
                @else
                </div>
                @endif
                @endif

                @if(auth()->user()->canAccessModule(10041) || auth()->user()->canAccessModule(11201))
                <!-- Approved Today Students -->
                @if($canAccessRegistrations)
                <a href="{{ route('admin.registrations.index', ['approved_today' => 1]) }}" class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                @else
                <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                @endif
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Approved Today</p>
                        <h4 class="text-xl font-bold text-gray-900 mt-0.5 truncate">{{ number_format($approvedToday) }}</h4>
                    </div>
                @if($canAccessRegistrations)
                </a>
                @else
                </div>
                @endif
                @endif

                @if(auth()->user()->canAccessModule(10081) && $cohort !== 'regular')
                <!-- Pending Batches -->
                <a href="{{ route('accountant.batches.index', ['status' => 'pending']) }}" class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Pending Batches</p>
                        <h4 class="text-xl font-bold text-gray-900 mt-0.5 truncate">{{ number_format($pendingBatches) }}</h4>
                    </div>
                </a>
                
                <!-- Total Approved Batches -->
                <a href="{{ route('accountant.batches.index', ['status' => 'approved']) }}" class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex items-center gap-4 hover:shadow-md hover:bg-white transition-all group h-full">
                    <div class="w-12 h-12 rounded-xl bg-teal-50 text-teal-500 flex items-center justify-center flex-shrink-0 group-hover:bg-teal-500 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">Total Approved Batches</p>
                        <h4 class="text-xl font-bold text-gray-900 mt-0.5 truncate">{{ number_format($totalApproved) }}</h4>
                    </div>
                </a>
                @endif
            </div>
        </div>

        <!-- Registration Overview: school vs regular, same component either way -->
        @if($cohort)
            {{-- Page tabs already pick the cohort, so show just that panel. --}}
            <div class="dashboard-card p-6 mb-8">
                <div class="mb-6 pb-4 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Registration Detail</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ $registrationChannels[$cohort]['description'] }}</p>
                </div>
                @include('partials.registration-channel', ['stats' => $registrationChannels[$cohort]])
            </div>
        @else
            {{-- Viewers without the cohort tabs still get both channels here. --}}
            <div class="dashboard-card p-6 mb-8" x-data="{ channel: 'school' }">
                <div class="mb-6 pb-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Registration Overview</h3>
                        <p class="mt-1 text-sm text-gray-500"
                            x-text="channel === 'school'
                                ? '{{ $registrationChannels['school']['description'] }}'
                                : '{{ $registrationChannels['regular']['description'] }}'"></p>
                    </div>
                    <div class="inline-flex p-1 bg-gray-100 rounded-xl self-start">
                        @foreach($registrationChannels as $key => $channelStats)
                            <button type="button" @click="channel = '{{ $key }}'"
                                class="px-4 py-2 text-sm font-semibold rounded-lg transition-all"
                                :class="channel === '{{ $key }}'
                                    ? 'bg-white text-[#0099b8] shadow-sm'
                                    : 'text-gray-600 hover:text-gray-900'">
                                {{ $channelStats['label'] }}
                                <span class="ml-1 text-xs font-bold opacity-70">{{ number_format($channelStats['total']) }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @foreach($registrationChannels as $key => $channelStats)
                    <div x-show="channel === '{{ $key }}'" x-cloak>
                        @include('partials.registration-channel', ['stats' => $channelStats])
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Remaining Original Analytics Sections (Filters, Sem Wise, Pies, Tables) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            @if(auth()->user()->canAccessModule(10041))
            <!-- Sem wise students count -->
            <div class="dashboard-card overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Standard Wise Students</h3>
                </div>
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-100">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-3">
                        <select name="sem_standard" onchange="this.form.submit()" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm bg-white focus:ring-[#00bde0] focus:border-[#00bde0]">
                            <option value="">All Standards</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->std_id }}" {{ request('sem_standard') == $standard->std_id ? 'selected' : '' }}>
                                    {{ $standard->stdname }}
                                </option>
                            @endforeach
                        </select>
                        <select name="sem_school" onchange="this.form.submit()" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm bg-white max-w-[150px] truncate focus:ring-[#00bde0] focus:border-[#00bde0]">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->school_id }}" {{ request('sem_school') == $school->school_id ? 'selected' : '' }}>
                                    {{ $school->schoolname }}
                                </option>
                            @endforeach
                        </select>
                        <select name="sem_district" onchange="this.form.submit()" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm bg-white focus:ring-[#00bde0] focus:border-[#00bde0]">
                            <option value="">All Districts</option>
                            @foreach($districts as $district)
                                <option value="{{ $district->dist_id }}" {{ request('sem_district') == $district->dist_id ? 'selected' : '' }}>
                                    {{ $district->dname }}
                                </option>
                            @endforeach
                        </select>
                        @if(request('days'))<input type="hidden" name="days" value="{{ request('days') }}">@endif
                    </form>
                </div>
                <div class="p-6 chart-container-md">
                    <canvas id="semWiseChart"></canvas>
                </div>
            </div>
            @endif

            @if(auth()->user()->canAccessModule(10061) || auth()->user()->canAccessModule(10071))
            <!-- Fees payment pie chart -->
            <div class="dashboard-card overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-white">
                    <h3 class="text-lg font-semibold text-gray-900">Fees Payment Status</h3>
                    <p class="mt-1 text-sm text-gray-500">Overview of pending vs completed fees</p>
                </div>
                <div class="p-6 flex items-center justify-center chart-container-md bg-white">
                    <div style="height: 100%; width: 100%; max-width: 300px;">
                        <canvas id="feesPieChart"></canvas>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            @if(auth()->user()->canAccessModule(10071))
            <!-- Recent Payments Section -->
            <div class="lg:col-span-2">
                <div class="dashboard-card h-full flex flex-col overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Recent Payments</h3>
                                <p class="mt-1 text-sm text-gray-500">Latest successful transactions</p>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto flex-1">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead class="bg-gray-50/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-50">
                                @forelse($recentPayments as $payment)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $payment->processdatetime ? $payment->processdatetime->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                            {{ $payment->invoice->student->fname ?? 'N/A' }} {{ $payment->invoice->student->lname ?? '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            ₹{{ number_format($payment->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusMap = [
                                                    '0' => ['label' => 'Pending', 'class' => 'bg-yellow-50 text-yellow-700 border border-yellow-200'],
                                                    '1' => ['label' => 'Authorized', 'class' => 'bg-green-50 text-green-700 border border-green-200'],
                                                    '2' => ['label' => 'Refunded', 'class' => 'bg-blue-50 text-blue-700 border border-blue-200'],
                                                    '3' => ['label' => 'Failed', 'class' => 'bg-red-50 text-red-700 border border-red-200'],
                                                    '4' => ['label' => 'Cancelled', 'class' => 'bg-gray-50 text-gray-700 border border-gray-200'],
                                                ];
                                                $statusInfo = $statusMap[$payment->status] ?? ['label' => 'Unknown', 'class' => 'bg-gray-50 text-gray-700 border border-gray-200'];
                                            @endphp
                                            <span class="px-2.5 py-1 text-xs font-semibold rounded-md {{ $statusInfo['class'] }}">
                                                {{ $statusInfo['label'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            No recent payments found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div></div>
            @endif

            @if(auth()->user()->canAccessModule(10801))
            <!-- Sidebar Recent Tickets -->
            <div class="dashboard-card flex flex-col overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Tickets</h3>
                    <p class="mt-1 text-sm text-gray-500">Latest support requests</p>
                </div>
                <div class="p-0 flex-1">
                    @forelse($recentTickets as $ticket)
                        <div class="px-6 py-4 border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                            <div class="flex justify-between items-start mb-1.5 gap-2">
                                <h4 class="text-sm font-semibold text-gray-900 truncate flex-1">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="hover:text-[#00bde0] transition-colors">{{ $ticket->title }}</a>
                                </h4>
                                @php
                                    $ticketStatusColor = match($ticket->status) {
                                        'Open', 'Reopen' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                                        'In Progress' => 'bg-blue-50 text-blue-700 border border-blue-200',
                                        'Resolved', 'Closed' => 'bg-green-50 text-green-700 border border-green-200',
                                        default => 'bg-gray-50 text-gray-700 border border-gray-200'
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-md {{ $ticketStatusColor }} whitespace-nowrap flex-shrink-0">
                                    {{ $ticket->status }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mb-2 truncate">{{ $ticket->school->schoolname ?? 'General' }}</p>
                            <div class="flex items-center text-xs text-gray-400 font-medium">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $ticket->created_at ? $ticket->created_at->diffForHumans() : 'N/A' }}
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500">
                            No recent tickets found
                        </div>
                    @endforelse
                </div>
                <div class="px-6 py-4 border-t border-gray-100 text-center mt-auto bg-gray-50/30">
                    <a href="{{ route('tickets.index') }}" class="text-sm font-semibold text-[#00bde0] hover:text-[#0099b8] transition-colors">View All Tickets &rarr;</a>
                </div>
            </div>
            @endif
        </div>

        @if(auth()->user()->canAccessModule(10041) || auth()->user()->canAccessModule(10001))
        <!-- Recent Activity Logs Section -->
        <div class="dashboard-card mb-8 overflow-hidden bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity Logs</h3>
                        <p class="mt-1 text-sm text-gray-500">Real-time system actions and audit logs</p>
                    </div>
                    @canAccess(10001)
                    <a href="{{ route('audit-logs.index') }}" class="text-sm font-semibold text-[#00bde0] hover:text-[#0099b8] transition-colors">
                        View All Logs &rarr;
                    </a>
                    @endcanAccess
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Event Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @forelse($recentLogs as $log)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 rounded-full bg-[#00bde0]/10 flex items-center justify-center text-[#00bde0] font-bold text-xs uppercase mr-3">
                                            {{ strtoupper(substr($log->user->fname ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-950">
                                                {{ $log->user ? $log->user->fname . ' ' . $log->user->lname : 'System' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $log->user->role->rolename ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $badgeClass = match($log->event_type) {
                                            'CREATE' => 'bg-green-50 text-green-700 border-green-200',
                                            'UPDATE' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'DELETE' => 'bg-red-50 text-red-700 border-red-200',
                                            'LOGIN_SUCCESS' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                            'LOGIN_FAILED' => 'bg-pink-50 text-pink-700 border-pink-200',
                                            default => 'bg-amber-50 text-amber-700 border-amber-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-md border {{ $badgeClass }}">
                                        {{ $log->event_type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title="{{ $log->description }}">
                                    {{ $log->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="font-medium text-gray-700 font-semibold">
                                        {{ $log->created_at ? $log->created_at->format('M d, Y H:i') : 'N/A' }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-semibold">
                                    @canAccess(10001)
                                    <a href="{{ route('audit-logs.show', $log->id) }}" class="text-[#00bde0] hover:text-[#0099b8] bg-[#00bde0]/10 hover:bg-[#00bde0]/20 px-3 py-1.5 rounded-md transition-colors text-xs font-bold">
                                        View Details
                                    </a>
                                    @else
                                    <span class="text-gray-450 text-xs font-normal">-</span>
                                    @endcanAccess
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No recent activity logs found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(auth()->user()->canAccessModule(10081) && $cohort !== 'regular')
        <!-- Recent Batches Section -->
        <div class="dashboard-card mb-8 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Batches</h3>
                        <p class="mt-1 text-sm text-gray-500">Latest upload batches for verification</p>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('accountant.batches.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                            View All
                        </a>
                        <a href="{{ route('accountant.batches.index', ['status' => 'pending']) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-semibold text-white bg-yellow-500 hover:bg-yellow-600 transition-colors shadow-sm shadow-yellow-500/20">
                            Pending Batches
                        </a>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch Code</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Semester</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Students</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Fees</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @forelse($recentBatches as $batch)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    {{ $batch->batch_code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                                    {{ $batch->semesterBatch->display_name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $batch->total_students }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    ₹{{ number_format($batch->total_fees, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($batch->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md border border-yellow-200 text-xs font-semibold bg-yellow-50 text-yellow-700">
                                            Pending
                                        </span>
                                    @elseif($batch->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md border border-green-200 text-xs font-semibold bg-green-50 text-green-700">
                                            Approved
                                        </span>
                                    @elseif($batch->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md border border-red-200 text-xs font-semibold bg-red-50 text-red-700">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <a href="{{ route('accountant.batches.show', $batch->batch_id) }}" 
                                       class="text-[#00bde0] hover:text-[#0099b8] font-semibold transition-colors">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No recent batches found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Shared Chart.js Defaults for Clean UI
    Chart.defaults.font.family = "'Inter', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif";
    Chart.defaults.color = '#9ca3af';
    Chart.defaults.scale.grid.color = '#f3f4f6';

    // Helper for sparklines
    const createSparkline = (id, color, data) => {
        const ctx = document.getElementById(id);
        if(!ctx) return;
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7'],
                datasets: [{
                    data: data,
                    borderColor: color,
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 0,
                    fill: {
                        target: 'origin',
                        above: color + '20' // add alpha for gradient effect
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false, min: Math.min(...data) * 0.9 } },
                layout: { padding: 0 }
            }
        });
    };

    // Initialize Sparklines
    createSparkline('sparkline1', '#10b981', @json($sparkline1Data)); // Green
    createSparkline('sparkline2', '#00bde0', @json($sparkline2Data)); // Blue
    createSparkline('sparkline3', '#ef4444', @json($sparkline3Data)); // Red
    createSparkline('sparkline4', '#10b981', @json($sparkline4Data)); // Green

    // Daily Active Users Chart (Dynamic Data)
    const dailyActiveCtx = document.getElementById('dailyActiveChart');
    if (dailyActiveCtx) {
        const labels = @json($dailyActiveUsersLabels);
        const data = @json($dailyActiveUsers);

        new Chart(dailyActiveCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Active Users',
                    data: data,
                    borderColor: '#4338ca', // Indigo
                    backgroundColor: 'rgba(67, 56, 202, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#4338ca',
                    pointBorderWidth: 2,
                    pointRadius: labels.length > 15 ? 0 : 3,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 10,
                        titleFont: { size: 13 },
                        bodyFont: { size: 14, weight: 'bold' },
                        displayColors: false
                    }
                },
                scales: {
                    x: { 
                        grid: { display: false }, 
                        ticks: { 
                            autoSkip: true, 
                            maxTicksLimit: 10,
                            maxRotation: 45,
                            minRotation: 0,
                            font: { size: 11 } 
                        } 
                    },
                    y: { border: { dash: [4, 4], display: false }, beginAtZero: true, ticks: { maxTicksLimit: 6, font: { size: 11 } } }
                }
            }
        });
    }

    // Daily Registrations Chart (Dynamic Data)
    const dailyRegCtx = document.getElementById('dailyRegistrationsChart');
    if (dailyRegCtx) {
        const labels = @json($dailyRegistrationsLabels);
        const data = @json($dailyRegistrations);

        new Chart(dailyRegCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Registrations',
                    data: data,
                    borderColor: '#10b981', // Emerald
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2,
                    pointRadius: labels.length > 15 ? 0 : 3,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 10,
                        titleFont: { size: 13 },
                        bodyFont: { size: 14, weight: 'bold' },
                        displayColors: false
                    }
                },
                scales: {
                    x: { 
                        grid: { display: false }, 
                        ticks: { 
                            autoSkip: true, 
                            maxTicksLimit: 10,
                            maxRotation: 45,
                            minRotation: 0,
                            font: { size: 11 } 
                        } 
                    },
                    y: { border: { dash: [4, 4], display: false }, beginAtZero: true, ticks: { maxTicksLimit: 6, font: { size: 11 } } }
                }
            }
        });
    }

    // Enrollments Chart (Dynamic Data)
    const enrollmentsCtx = document.getElementById('enrollmentsChart');
    if (enrollmentsCtx) {
        const labels = @json($enrollmentsLabels);
        const registrations = @json($enrollmentsXDays);
        const pending = @json($pendingXDays);
        const enrolled = @json($approvalsXDays);

        let enrollmentsChart = new Chart(enrollmentsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Registered',
                        data: registrations,
                        borderColor: '#3b82f6', // Blue
                        backgroundColor: 'rgba(59, 130, 246, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: labels.length > 15 ? 0 : 3,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Pending',
                        data: pending,
                        borderColor: '#f59e0b', // Amber
                        backgroundColor: 'rgba(245, 158, 11, 0.05)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: labels.length > 15 ? 0 : 3,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Enrolled',
                        data: enrolled,
                        borderColor: '#10b981', // Green
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: labels.length > 15 ? 0 : 3,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { 
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            font: { size: 12 }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 10,
                        titleFont: { size: 13 },
                        bodyFont: { size: 13, weight: 'bold' },
                        usePointStyle: true,
                        boxPadding: 4
                    }
                },
                scales: {
                    x: { 
                        grid: { display: false }, 
                        ticks: { 
                            autoSkip: true, 
                            maxTicksLimit: 12,
                            maxRotation: 45,
                            minRotation: 0,
                            font: { size: 11 } 
                        } 
                    },
                    y: { border: { dash: [4, 4], display: false }, beginAtZero: true, ticks: { maxTicksLimit: 8, font: { size: 11 } } }
                }
            }
        });
    }

    // Original Charts Restyled
    // Semester Wise Chart
    const semData = @json($semWiseData);
    const semLabels = semData.map(item => item.name || 'Unknown');
    const semValues = semData.map(item => item.value);

    const semCanvas = document.getElementById('semWiseChart');
    if (semCanvas) {
        const semCtx = semCanvas.getContext('2d');
        new Chart(semCtx, {
            type: 'bar',
            data: {
                labels: semLabels,
                datasets: [{
                    label: 'Students Count',
                    data: semValues,
                    backgroundColor: '#00bde0',
                    borderRadius: 6,
                    barThickness: 'flex',
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false } },
                    y: { border: { dash: [4, 4], display: false }, beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#1f2937', padding: 10, titleFont: { size: 13 }, bodyFont: { size: 14, weight: 'bold' }, displayColors: false }
                }
            }
        });
    }

    // Fees Pie Chart
    const feesData = @json($feesPieData);
    const feesCanvas = document.getElementById('feesPieChart');
    if (feesCanvas) {
        const feesCtx = feesCanvas.getContext('2d');
        new Chart(feesCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending Fees', 'Completed Fees'],
                datasets: [{
                    data: [feesData.pending, feesData.completed],
                    backgroundColor: [
                        '#f59e0b', // Amber
                        '#10b981'  // Emerald
                    ],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, usePointStyle: true, pointStyle: 'circle' }
                    },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.raw !== null) {
                                    label += '₹' + new Intl.NumberFormat('en-IN').format(context.raw);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
