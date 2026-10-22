@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Payment Details</h1>
            <p class="text-sm text-gray-600 mt-1">Payment ID: #{{ $payment->payment_id }}</p>
        </div>
        @if(auth()->user()->hasPermission(10072))
        <a href="{{ route('payments.edit', $payment->payment_id) }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
            Edit Payment
        </a>
        @endif
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Payment Information -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment Information</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Payment Mode</p>
                        <p class="text-base font-medium text-gray-900">{{ $payment->paymentMode->paymodename ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Transaction ID</p>
                        <p class="text-base font-medium text-gray-900">{{ $payment->txnid }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Payment Date & Time</p>
                        <p class="text-base font-medium text-gray-900">{{ optional($payment->processdatetime)->format('d M Y H:i') ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        @php
                            $statusMap = [
                                '0' => ['label' => 'Pending', 'class' => 'bg-yellow-100 text-yellow-800'],
                                '1' => ['label' => 'Authorized', 'class' => 'bg-green-100 text-green-800'],
                                '2' => ['label' => 'Refunded', 'class' => 'bg-blue-100 text-blue-800'],
                                '3' => ['label' => 'Failed', 'class' => 'bg-red-100 text-red-800'],
                                '4' => ['label' => 'Cancelled', 'class' => 'bg-gray-100 text-gray-800'],
                            ];
                            $status = $statusMap[$payment->status] ?? ['label' => 'Unknown', 'class' => 'bg-gray-100 text-gray-800'];
                        @endphp
                        <span class="inline-block px-3 py-1 text-sm font-medium rounded-full {{ $status['class'] }}">
                            {{ $status['label'] }}
                        </span>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Amount</p>
                        <p class="text-xl font-bold text-gray-900">₹{{ number_format($payment->amount, 2) }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Discount</p>
                        <p class="text-base font-medium text-gray-900">₹{{ number_format($payment->discount ?? 0, 2) }}</p>
                    </div>
                </div>
                
                @if($payment->orgresponse)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600 mb-2">Payment Gateway Response</p>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ $payment->orgresponse }}</pre>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Invoice Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Details</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Invoice ID</span>
                        <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-sm font-medium text-[#00bde0] hover:text-[#0099b8]">
                            #{{ $payment->invoice_id }}
                        </a>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Invoice Date</span>
                        <span class="text-sm font-medium text-gray-900">{{ optional(optional($payment->invoice)->invoice_date)->format('d M Y') ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Due Date</span>
                        <span class="text-sm font-medium text-gray-900">{{ optional(optional($payment->invoice)->due_date)->format('d M Y') ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Invoice Status</span>
                        <span class="text-sm font-medium text-gray-900">{{ $payment->invoice->status }}</span>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Total Before Discount</span>
                            <span class="text-sm font-medium text-gray-900">₹{{ number_format($payment->invoice->total_before_discount, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between mt-2">
                            <span class="text-sm text-gray-600">Discount Total</span>
                            <span class="text-sm font-medium text-gray-900">₹{{ number_format($payment->invoice->discount_total, 2) }}</span>
                        </div>
                        
                        <div class="flex justify-between mt-2 pt-2 border-t border-gray-200">
                            <span class="text-base font-semibold text-gray-900">Total Due</span>
                            <span class="text-base font-bold text-gray-900">₹{{ number_format($payment->invoice->total_due, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Semester Information -->
            @php
                $feePlan = $payment->invoice->items->first()->feePlan ?? null;
                $semesterBatches = $feePlan ? $feePlan->semesterBatches()->orderBy('start_date')->get() : collect();
                $currentSemester = null;
                $upcomingSemester = null;
                
                if ($semesterBatches->isNotEmpty()) {
                    // Determine current and upcoming semesters based on payment amount
                    $totalFees = $semesterBatches->sum('fees');
                    $paymentAmount = $payment->amount;
                    
                    if ($paymentAmount >= $totalFees * 0.8) {
                        // Full payment or near full payment - show current and upcoming
                        $currentSemester = $semesterBatches->first();
                        $upcomingSemester = $semesterBatches->skip(1)->first();
                    } else {
                        // Partial payment - show only current
                        $currentSemester = $semesterBatches->first();
                    }
                }
            @endphp
            
            @if($currentSemester)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Semester Information</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">Current Semester</p>
                            <p class="text-base font-medium text-gray-900">{{ $currentSemester->display_name ?? 'N/A' }}</p>
                            @if($currentSemester->start_date)
                                <p class="text-xs text-gray-500">{{ $currentSemester->start_date->format('M Y') }} - {{ $currentSemester->end_date->format('M Y') }}</p>
                            @endif
                        </div>
                        
                        @if($upcomingSemester)
                            <div class="pt-3 border-t border-gray-200">
                                <p class="text-sm text-gray-600">Upcoming Semester</p>
                                <p class="text-base font-medium text-gray-900">{{ $upcomingSemester->display_name ?? 'N/A' }}</p>
                                @if($upcomingSemester->start_date)
                                    <p class="text-xs text-gray-500">{{ $upcomingSemester->start_date->format('M Y') }} - {{ $upcomingSemester->end_date->format('M Y') }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Student & School Information -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Student Information</h2>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Name</p>
                        <p class="text-base font-medium text-gray-900">
                            {{ $payment->invoice->student->firstname }} 
                            {{ $payment->invoice->student->middlename }} 
                            {{ $payment->invoice->student->lastname }}
                        </p>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-600">Email</p>
                        <p class="text-base font-medium text-gray-900">{{ $payment->invoice->student->email }}</p>
                    </div>
                    
                    @if($payment->invoice->student->contactno)
                        <div>
                            <p class="text-sm text-gray-600">Contact</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->invoice->student->contactno }}</p>
                        </div>
                    @endif
                </div>
            </div>
            
            @if($payment->invoice->school)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">School Information</h2>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-600">School Name</p>
                            <p class="text-base font-medium text-gray-900">{{ $payment->invoice->school->schoolname }}</p>
                        </div>
                        
                        @if($payment->invoice->school->adminname)
                            <div>
                                <p class="text-sm text-gray-600">Admin Name</p>
                                <p class="text-base font-medium text-gray-900">{{ $payment->invoice->school->adminname }}</p>
                            </div>
                        @endif
                        
                        @if($payment->invoice->school->admincontact)
                            <div>
                                <p class="text-sm text-gray-600">Admin Contact</p>
                                <p class="text-base font-medium text-gray-900">{{ $payment->invoice->school->admincontact }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
    
    <div class="mt-6">
        <a href="{{ route('payments.index') }}" class="text-[#00bde0] hover:text-[#0099b8] font-medium">
            ← Back to Payment Logs
        </a>
    </div>
</div>
@endsection
