@extends('layouts.app')

@section('title', 'Initiator Dashboard')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Initiator Dashboard</h2>
            <p class="mt-2 text-sm text-gray-600">Overview of your assigned students and schools</p>
        </div>

        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-{{ Auth::user()->financial_type === 'paid' ? '4' : '2' }} gap-6 mb-8">
            <!-- Total Students Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Assigned Students</p>
                        <p class="text-4xl font-bold text-gray-900 mt-3">{{ number_format($totalStudents) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-[#00bde0] to-[#0099b8] p-4 rounded-xl shadow-md">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Schools Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Assigned Schools</p>
                        <p class="text-4xl font-bold text-gray-900 mt-3">{{ number_format($totalSchools) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-[#00bde0] to-[#0099b8] p-4 rounded-xl shadow-md">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                </div>
            </div>

            @if(Auth::user()->isPaidInitiator())
                <!-- Earnings Card (Paid Only) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">My Earnings</p>
                            <p class="text-3xl font-bold text-green-600 mt-3">₹{{ number_format($earnings['total'], 2) }}</p>
                            <a href="{{ route('initiator.earnings') }}" class="mt-2 inline-block text-xs text-[#00bde0] hover:underline font-medium">
                                View Calculation Details
                            </a>
                        </div>
                        <div class="bg-gradient-to-br from-green-400 to-green-600 p-4 rounded-xl shadow-md">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Incentive Card (Paid Only) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-lg transition-shadow duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Financial Model</p>
                            <p class="text-2xl font-bold text-[#00bde0] mt-3">
                                {{ (Auth::user()->financialConfig?->student_calculation_type ?? 0) == 1 ? 'Slab Based' : 'Per Student' }}
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-400 to-purple-600 p-4 rounded-xl shadow-md">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if(Auth::user()->isPaidInitiator())
        <!-- Pending Payments Section (Paid Only) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Pending Payments</h3>
                        <p class="mt-1 text-sm text-gray-500">Recent invoices awaiting payment</p>
                    </div>
                    <form method="GET" action="{{ route('initiator.dashboard') }}" class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600 mr-2">Filter by School:</label>
                        <select name="school_filter" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent bg-white">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->school_id }}" {{ request('school_filter') == $school->school_id ? 'selected' : '' }}>
                                    {{ $school->schoolname }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Invoice ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">School</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Amount Due</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pendingPayments as $invoice)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $invoice->invoice_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $invoice->student->fname ?? 'N/A' }} {{ $invoice->student->lname ?? '' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $invoice->school->schoolname ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    ₹{{ number_format($invoice->total_due, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                        {{ $invoice->status === 'ISSUED' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $invoice->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-500">No pending payments found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- New Inquiries Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">New Inquiries</h3>
                        <p class="mt-1 text-sm text-gray-500">Recent student registrations</p>
                    </div>
                    <form method="GET" action="{{ route('initiator.dashboard') }}" class="flex items-center space-x-2">
                        <label class="text-sm text-gray-600 mr-2">Payment Status:</label>
                        <select name="payment_status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-[#00bde0] focus:border-transparent bg-white">
                            <option value="">All Status</option>
                            <option value="PENDING" {{ request('payment_status') == 'PENDING' ? 'selected' : '' }}>Pending</option>
                            <option value="AUTHORIZED" {{ request('payment_status') == 'AUTHORIZED' ? 'selected' : '' }}>Authorized</option>
                            <option value="FAILED" {{ request('payment_status') == 'FAILED' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Student ID</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">School</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Payment Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($newInquiries as $student)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $student->student_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $student->fname }} {{ $student->mname }} {{ $student->lname }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $student->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ $student->school->schoolname ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $latestPayment = $student->invoices->flatMap->payments->sortByDesc('payment_id')->first();
                                        $status = $latestPayment ? $latestPayment->status : 'No Payment';
                                    @endphp
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full 
                                        {{ $status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($status === 'AUTHORIZED' ? 'bg-green-100 text-green-800' : 
                                           ($status === 'FAILED' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                        {{ $status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-500">No inquiries found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Earnings Details Modal -->
        <div id="earningsDetailsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-black/30 backdrop-blur-[2px] transition-opacity" aria-hidden="true" onclick="closeEarningsDetails()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100">
                    <div class="bg-gradient-to-r from-[#00bde0] to-[#01a7c7] px-6 py-4 flex items-center justify-between">
                        <h3 class="text-xl font-extrabold text-white" id="modal-title">Earnings Breakdown</h3>
                        <button onclick="closeEarningsDetails()" class="text-white hover:bg-white/20 p-1 rounded-lg transition-colors focus:outline-none">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="bg-white px-8 py-8">
                        <div class="space-y-5">
                            <!-- Schools Section -->
                            @if(($earnings['breakdown']['schools']['earnings'] ?? 0) > 0)
                            <div class="flex items-center justify-between p-4 bg-blue-50/50 rounded-2xl border border-blue-100 shadow-sm">
                                <div class="flex items-center">
                                    <div class="p-3 bg-blue-500 rounded-xl mr-4 shadow-sm">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">School Registration</h4>
                                        <p class="text-xs text-gray-500">{{ $earnings['breakdown']['schools']['count'] ?? 0 }} Schools assigned</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xl font-bold text-gray-900">₹{{ number_format($earnings['breakdown']['schools']['earnings'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                            @endif

                            <!-- Students Section -->
                            @if(($earnings['breakdown']['students']['earnings'] ?? 0) > 0)
                            <div class="flex items-center justify-between p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100 shadow-sm">
                                <div class="flex items-center">
                                    <div class="p-3 bg-indigo-500 rounded-xl mr-4 shadow-sm">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">Student Enrollment</h4>
                                        <p class="text-xs text-gray-500">{{ $earnings['breakdown']['students']['paid_count'] ?? 0 }} Paid / {{ $earnings['breakdown']['students']['count'] ?? 0 }} Registered</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xl font-bold text-gray-900">₹{{ number_format($earnings['breakdown']['students']['earnings'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                            @endif

                            <!-- Incentive Section -->
                            @if(isset($earnings['breakdown']['incentive']['earnings']) && $earnings['breakdown']['incentive']['earnings'] > 0)
                            <div class="flex items-center justify-between p-4 bg-green-50/50 rounded-2xl border border-green-100 shadow-sm">
                                <div class="flex items-center">
                                    <div class="p-3 bg-green-500 rounded-xl mr-4 shadow-sm">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">Revenue Incentive</h4>
                                        <p class="text-xs text-gray-500">{{ $earnings['breakdown']['incentive']['percentage'] ?? 0 }}% of ₹{{ number_format($earnings['total_revenue'] ?? 0, 2) }} revenue</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xl font-bold text-gray-900">₹{{ number_format($earnings['breakdown']['incentive']['earnings'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                            @endif

                            <!-- Bonus Slabs Section -->
                            @if(($earnings['breakdown']['slabs']['earnings'] ?? 0) > 0)
                            <div class="flex items-center justify-between p-4 bg-purple-50/50 rounded-2xl border border-purple-100 shadow-sm">
                                <div class="flex items-center">
                                    <div class="p-3 bg-purple-500 rounded-xl mr-4 shadow-sm">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">Performance Bonuses</h4>
                                        <p class="text-xs text-gray-500">
                                            @if(isset($earnings['breakdown']['slabs']['slab_details']['min_students']))
                                                @php $sd = $earnings['breakdown']['slabs']['slab_details']; @endphp
                                                Slab: {{ $earnings['breakdown']['slabs']['paid_count'] ?? 0 }} Paid Students (Min {{ $sd['min_students'] ?? 0 }})
                                                <br>({{ $sd['percentage'] ?? 0 }}% 
                                                @if(($sd['fixed_amount'] ?? 0) > 0)
                                                    + ₹{{ number_format($sd['fixed_amount'], 2) }} fixed/paid stud
                                                @endif)
                                            @else
                                                Based on paid student count slabs
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xl font-bold text-gray-900">₹{{ number_format($earnings['breakdown']['slabs']['earnings'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                            @endif

                            <!-- Total Row -->
                            <div class="mt-8 p-6 bg-[#00bde0] rounded-2xl shadow-lg shadow-[#00bde0]/20 flex items-center justify-between transform hover:scale-[1.02] transition-transform duration-200">
                                <div>
                                    <p class="text-white/80 text-xs font-bold uppercase tracking-wider">Estimated Total Earnings</p>
                                    <h5 class="text-2xl font-black text-white">Total Payout</h5>
                                </div>
                                <div class="text-right">
                                    <span class="text-4xl font-black text-white">₹{{ number_format($earnings['total'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gray-50 px-8 py-5 flex justify-end">
                        <button type="button" onclick="closeEarningsDetails()" class="px-8 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition-all duration-200 shadow-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Terms & Conditions Section -->
        @if(Auth::user()->tc_content)
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-3 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Your Initiator Agreement
                </h3>
                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Snapshotted on {{ Auth::user()->created_at->format('M d, Y') }}
                </span>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto bg-gray-50/50">
                <div class="prose prose-sm max-w-none text-gray-600">
                    {!! Auth::user()->tc_content !!}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showEarningsDetails() {
        const modal = document.getElementById('earningsDetailsModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeEarningsDetails() {
        const modal = document.getElementById('earningsDetailsModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }
</script>
@endpush
