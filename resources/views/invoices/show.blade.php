@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Invoice #{{ $invoice->invoice_id }}</h1>
            <p class="text-gray-600 mt-1">{{ optional($invoice->invoice_date)->format('d/m/Y') ?? 'N/A' }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('invoices.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition">
                Back to List
            </a>
            @if($invoice->status !== 'VOID' && $invoice->status !== 'PAID')
                <button onclick="document.getElementById('voidForm').submit()" class="bg-red-500 hover:bg-red-600 text-white font-medium px-4 py-2 rounded-lg transition">
                    Void Invoice
                </button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Invoice Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Student & School Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Information</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Student</p>
                        <p class="text-gray-900 mt-1">
                            {{ $invoice->student->fname }} {{ $invoice->student->mname }} {{ $invoice->student->lname }}
                        </p>
                        <p class="text-sm text-gray-600"><x-masked-email :email="$invoice->student->email" /></p>
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-500">School</p>
                        <p class="text-gray-900 mt-1">{{ $invoice->school->schoolname ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-500">Standard</p>
                        <p class="text-gray-900 mt-1">{{ $invoice->student->standard->stdname ?? 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <p class="text-sm font-medium text-gray-500">Due Date</p>
                        <p class="text-gray-900 mt-1">{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                </div>

                @if($invoice->notes)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm font-medium text-gray-500">Notes</p>
                        <p class="text-gray-900 mt-1">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Invoice Items -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Invoice Items</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($invoice->items as $item)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $item->description }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ $item->qty }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">₹{{ number_format($item->unit_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900 text-right">₹{{ number_format($item->line_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="text-gray-900 font-medium">₹{{ number_format($invoice->total_before_discount, 2) }}</span>
                        </div>
                        
                        @if($invoice->discount_total > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount</span>
                                <span class="text-green-600 font-medium">-₹{{ number_format($invoice->discount_total, 2) }}</span>
                            </div>
                        @endif
                        
                        <div class="flex justify-between text-lg font-semibold pt-2 border-t border-gray-200">
                            <span class="text-gray-900">Total Due</span>
                            <span class="text-[#00bde0]">₹{{ number_format($invoice->total_due, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            @if($invoice->payments->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Payment History</h2>
                    
                    <div class="space-y-3">
                        @foreach($invoice->payments as $payment)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $payment->paymentmode->modename ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-600">{{ optional($payment->payment_datetime)->format('d/m/Y') ?? 'N/A' }}</p>
                                    @if($payment->transaction_id)
                                        <p class="text-xs text-gray-600">Transaction: {{ $payment->transaction_id }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">₹{{ number_format($payment->amount, 2) }}</p>
                                    <span class="text-xs px-2 py-1 rounded-full {{ $payment->status === 'PAID' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $payment->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Invoice Status</h3>
                
                @php
                    $statusColors = [
                        'DRAFT' => 'bg-gray-100 text-gray-800',
                        'ISSUED' => 'bg-blue-100 text-blue-800',
                        'PARTIAL' => 'bg-yellow-100 text-yellow-800',
                        'PAID' => 'bg-green-100 text-green-800',
                        'VOID' => 'bg-red-100 text-red-800',
                    ];
                @endphp
                
                <span class="inline-block px-3 py-1 text-sm font-medium rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $invoice->status }}
                </span>

                @if($invoice->status !== 'VOID' && $invoice->status !== 'PAID')
                    <form method="POST" action="{{ route('invoices.update', $invoice->invoice_id) }}" class="mt-4">
                        @csrf
                        @method('PUT')
                        
                        <label class="block text-sm font-medium text-gray-700 mb-2">Update Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent mb-3">
                            <option value="DRAFT" {{ $invoice->status === 'DRAFT' ? 'selected' : '' }}>Draft</option>
                            <option value="ISSUED" {{ $invoice->status === 'ISSUED' ? 'selected' : '' }}>Issued</option>
                            <option value="PARTIAL" {{ $invoice->status === 'PARTIAL' ? 'selected' : '' }}>Partial</option>
                            <option value="PAID" {{ $invoice->status === 'PAID' ? 'selected' : '' }}>Paid</option>
                        </select>
                        
                        <button type="submit" class="w-full bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                            Update Status
                        </button>
                    </form>
                @endif
            </div>

            <!-- Actions Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Actions</h3>
                
                <div class="space-y-2">
                    <a href="{{ route('invoices.pdf', $invoice->invoice_id) }}" class="w-full inline-block text-center bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition text-sm mb-2">
                        Download PDF
                    </a>
                    
                    <form method="POST" action="{{ route('invoices.recalculate', $invoice->invoice_id) }}">
                        @csrf
                        <button type="submit" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium px-4 py-2 rounded-lg transition text-sm">
                            Recalculate Totals
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Void Form -->
    <form id="voidForm" method="POST" action="{{ route('invoices.void', $invoice->invoice_id) }}" class="hidden">
        @csrf
    </form>
</div>
@endsection
