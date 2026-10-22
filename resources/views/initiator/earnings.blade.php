@extends('layouts.app')

@section('title', 'My Earnings')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">My Earnings</h2>
                <p class="mt-2 text-sm text-gray-600">Detailed breakdown of your commissions and performance bonuses</p>
            </div>
            <div class="flex gap-8 text-right">
                <div class="bg-white px-4 py-2 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Earnings</p>
                    <p class="text-xl font-black text-gray-900">₹{{ number_format($earnings['total'], 2) }}</p>
                </div>
                <div class="bg-white px-4 py-2 rounded-lg border border-gray-100 shadow-sm">
                    <p class="text-[10px] font-bold text-green-500 uppercase tracking-widest">Amount Received</p>
                    <p class="text-xl font-black text-green-600">₹{{ number_format($earnings['total_paid'], 2) }}</p>
                </div>
                <div class="bg-primary-50 px-4 py-2 rounded-lg border border-primary-100 shadow-sm">
                    <p class="text-[10px] font-bold text-primary-400 uppercase tracking-widest">Current Balance</p>
                    <p class="text-xl font-black text-primary-500">₹{{ number_format($earnings['balance'], 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">School Commission</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">₹{{ number_format($earnings['breakdown']['schools']['earnings'] ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $earnings['breakdown']['schools']['count'] ?? 0 }} Schools assigned</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Student commission</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">₹{{ number_format($earnings['breakdown']['students']['earnings'] ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $earnings['breakdown']['students']['paid_count'] ?? 0 }} Paid Students</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Slab Bonuses</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">₹{{ number_format($earnings['breakdown']['slabs']['earnings'] ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $earnings['breakdown']['slabs']['details'] ?? 'N/A' }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide">Total Revenue</p>
                <p class="text-2xl font-bold text-primary-500 mt-2">₹{{ number_format($earnings['total_revenue'] ?? 0, 2) }}</p>
                <p class="text-xs text-gray-500 mt-1">From fully paid invoices</p>
            </div>
        </div>

        <!-- Schools Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50">
                <h3 class="text-xl font-bold text-gray-900">Assigned Schools</h3>
                <p class="text-sm text-gray-500 mt-1">Detailed list of schools contributing to registration commission</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">School ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">School Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Registration Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($earnings['details']['schools'] as $school)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">#{{ $school->school_id }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $school->schoolname }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $school->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $school->status == 1 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $school->status == 1 ? 'Approved' : 'Pending' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">No schools assigned yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Details & Slab Contribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Student & slab Details</h3>
                    <p class="text-sm text-gray-500 mt-1">Detailed log of students and their contribution to your earnings</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-primary-500/10 text-primary-500 border border-primary-500/20">
                        {{ $earnings['breakdown']['students']['paid_count'] ?? 0 }} Paid Students
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Student Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Enrollment Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Comm. %</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Earned</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @forelse($earnings['details']['invoices'] as $invoice)
                        @php
                            $semCount = $invoice->student ? $invoice->student->semesterBatches->count() : 0;
                            $isRepeat = $semCount > 1;
                            $configs = Auth::user()->financialConfigs;
                            $percArray = [];
                            $earned = 0;
                            foreach ($configs as $config) {
                                if ($invoice->status == 'PAID' && $config->student_calculation_type == \App\Models\InitiatorFinancialConfig::TYPE_PER_STUDENT) {
                                    $p = $isRepeat ? $config->per_student_commission_repeat_semester : $config->per_student_commission_first_semester;
                                    if ($p > 0) {
                                        $percArray[] = $p . '%';
                                        $earned += ($invoice->total_due * $p / 100);
                                    }
                                }
                            }
                            $percDisplay = !empty($percArray) ? implode(' + ', $percArray) : '-';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">#{{ $invoice->invoice_id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $invoice->student->fname ?? 'N/A' }} {{ $invoice->student->lname ?? '' }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $invoice->student_id }}</div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900">₹{{ number_format($invoice->total_due, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $invoice->status == 'PAID' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($isRepeat)
                                <span class="text-purple-600 font-medium">Repeat Semester</span>
                                @else
                                <span class="text-blue-600 font-medium">First Semester</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700">
                                {{ $percDisplay }}
                            </td>
                            <td class="px-6 py-4 font-bold text-green-600">
                                {{ $earned > 0 ? '₹' . number_format($earned, 2) : '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 italic">No student invoices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(isset($earnings['breakdown']['slabs']['slab_details']) && $earnings['breakdown']['slabs']['slab_details'])
        <!-- Slab Information Alert -->
        <div class="bg-blue-50 border-l-4 border-primary-500 p-4 rounded-lg mb-8">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-bold text-blue-800">Slab Achievement Information</p>
                    <p class="text-sm text-blue-700 mt-1">
                        You have achieved the slab for <strong>{{ $earnings['breakdown']['slabs']['paid_count'] }} paid students</strong>.
                        Applicable rates: <strong>{{ $earnings['breakdown']['slabs']['slab_details']['percentage'] }}% Commission</strong> 
                        @if(($earnings['breakdown']['slabs']['slab_details']['fixed_amount'] ?? 0) > 0)
                            + <strong>₹{{ number_format($earnings['breakdown']['slabs']['slab_details']['fixed_amount'], 2) }} fixed per student</strong>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif
        @if(isset($earnings['details']['payments']) && count($earnings['details']['payments']) > 0)
        <!-- Payment History -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50">
                <h3 class="text-xl font-bold text-gray-900">Payment History</h3>
                <p class="text-sm text-gray-500 mt-1">History of physical payments received from Administration</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reference</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Notes</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 text-sm">
                        @foreach($earnings['details']['payments'] as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-900">{{ $payment->payment_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $payment->payment_method }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $payment->reference_number ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-500 italic text-xs">{{ $payment->notes ?? '-' }}</td>
                            <td class="px-6 py-4 text-right font-bold text-green-600">₹{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
