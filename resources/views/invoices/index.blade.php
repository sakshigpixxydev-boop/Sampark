@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Invoices</h1>
        <div class="flex gap-2">
            <form id="export-form" action="{{ route('invoices.export') }}" method="POST" class="inline flex gap-2">
                @csrf
                <input type="hidden" name="selected_invoices" id="selected_invoices_input">
                <!-- Preserve filters for export -->
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                <input type="hidden" name="school_id" value="{{ request('school_id') }}">
                <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                
                <button type="submit" name="export_type" value="all" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 rounded-lg transition">
                    Export CSV (All)
                </button>
                <button type="submit" name="export_type" value="all" formaction="{{ route('invoices.export-pdf') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 rounded-lg transition">
                    Export PDF (All)
                </button>
                
                <button type="submit" name="export_type" value="selected" id="export-selected-btn" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 rounded-lg transition hidden">
                    Export CSV Selected
                </button>
                <button type="submit" name="export_type" value="selected" id="export-selected-pdf-btn" formaction="{{ route('invoices.export-pdf') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 rounded-lg transition hidden">
                    Export PDF Selected
                </button>
            </form>
            @if (auth()->user()->role && auth()->user()->role->permissions()->where('permkey', 10001)->exists())
                <a href="{{ route('invoices.import-payments.form') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 rounded-lg transition">
                    Import Payment CSV
                </a>
            @endif
            <a href="{{ route('invoices.create') }}" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition">
                Generate Invoice
            </a>
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

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('invoices.index') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent">
                    <option value="">All Statuses</option>
                    <option value="DRAFT" {{ request('status') == 'DRAFT' ? 'selected' : '' }}>Draft</option>
                    <option value="ISSUED" {{ request('status') == 'ISSUED' ? 'selected' : '' }}>Issued</option>
                    <option value="PARTIAL" {{ request('status') == 'PARTIAL' ? 'selected' : '' }}>Partial</option>
                    <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Paid</option>
                    <option value="VOID" {{ request('status') == 'VOID' ? 'selected' : '' }}>Void</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                <input type="text" name="date_from" value="{{ request('date_from') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                <input type="text" name="date_to" value="{{ request('date_to') }}" class="date-picker w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#00bde0] focus:border-transparent" placeholder="DD/MM/YYYY">
            </div>

            <div class="flex items-end">
                <button type="submit" class="bg-[#00bde0] hover:bg-[#0099b8] text-white font-medium px-4 py-2 rounded-lg transition w-full">
                    Apply Filters
                </button>
            </div>

            <div class="flex items-end">
                <a href="{{ route('invoices.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-lg transition w-full text-center">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Due</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" name="invoice_ids[]" value="{{ $invoice->invoice_id }}" class="invoice-checkbox rounded border-gray-300 text-[#00bde0] focus:ring-[#00bde0]">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #{{ $invoice->invoice_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $invoice->invoice_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $invoice->student ? $invoice->student->fname . ' ' . $invoice->student->lname : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $invoice->school ? $invoice->school->schoolname : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹{{ number_format($invoice->total_before_discount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ₹{{ number_format($invoice->discount_total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                ₹{{ number_format($invoice->total_due, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'DRAFT' => 'bg-gray-100 text-gray-800',
                                        'ISSUED' => 'bg-blue-100 text-blue-800',
                                        'PARTIAL' => 'bg-yellow-100 text-yellow-800',
                                        'PAID' => 'bg-green-100 text-green-800',
                                        'VOID' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $invoice->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm flex items-center gap-2">
                                <a href="{{ route('invoices.show', $invoice->invoice_id) }}" class="text-[#00bde0] hover:text-[#0099b8] font-medium">
                                    View
                                </a>
                                <span class="text-gray-300">|</span>
                                <a href="{{ route('invoices.pdf', $invoice->invoice_id) }}" class="text-gray-600 hover:text-gray-900 font-medium">
                                    PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $invoices->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.invoice-checkbox');
        const exportSelectedBtn = document.getElementById('export-selected-btn');
        const exportSelectedPdfBtn = document.getElementById('export-selected-pdf-btn');
        const selectedInput = document.getElementById('selected_invoices_input');
        const exportForm = document.getElementById('export-form');

        function updateExportButton() {
            const selectedCount = document.querySelectorAll('.invoice-checkbox:checked').length;
            if (selectedCount > 0) {
                exportSelectedBtn.classList.remove('hidden');
                exportSelectedBtn.textContent = `Export CSV Selected (${selectedCount})`;
                exportSelectedPdfBtn.classList.remove('hidden');
                exportSelectedPdfBtn.textContent = `Export PDF Selected (${selectedCount})`;
            } else {
                exportSelectedBtn.classList.add('hidden');
                exportSelectedPdfBtn.classList.add('hidden');
            }
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateExportButton();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateExportButton();
                // Update select all checkbox state
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                selectAll.checked = allChecked;
            });
        });

        exportForm.addEventListener('submit', function(e) {
            const submitter = e.submitter;
            if (submitter && submitter.value === 'selected') {
                const selectedIds = Array.from(document.querySelectorAll('.invoice-checkbox:checked'))
                    .map(cb => cb.value);
                
                if (selectedIds.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one invoice to export.');
                    return;
                }
                
                selectedInput.value = selectedIds.join(',');
            } else {
                selectedInput.value = ''; // Clear for "Export All"
            }
        });
    });
</script>
@endpush
