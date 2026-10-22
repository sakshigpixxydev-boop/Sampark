@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Import Invoice Payment CSV</h1>
        <a href="{{ route('invoices.index') }}" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-4 py-2 rounded-lg transition">
            Back to Invoices
        </a>
    </div>

    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-800">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    @if ($applied)
        <div class="mb-6 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-900">
            <strong>Import applied.</strong> Re-run the preview with the same file: it should now report
            <em>0 planned changes</em>. That is the idempotency check.
        </div>
    @endif

    {{-- Upload form --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
        <form action="{{ route('invoices.import-payments.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <label class="block text-sm font-medium text-gray-700 mb-2">Invoice export file (.csv, .xlsx or .xls)</label>
            <input type="file" name="csv_file" accept=".csv,.xlsx,.xls,text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required
                   class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer mb-4 p-2">

            <div class="flex flex-col gap-2 mb-4">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="create_missing" value="1"
                           @checked(($options['create_missing'] ?? false)) class="rounded border-gray-300">
                    Create invoices that do not exist, reusing the CSV's INV number
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="sync_fees_status" value="1"
                           @checked(($options['sync_fees_status'] ?? false)) class="rounded border-gray-300">
                    Also set student fees status to Paid
                </label>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Razorpay only &mdash; payment statuses to act on</label>
                    <select name="statuses" class="border border-gray-300 rounded-lg px-3 py-2 w-full text-sm">
                        <option value="captured_authorized">captured + authorized</option>
                        <option value="captured">captured only</option>
                        <option value="all">all statuses (records refunded/failed too)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Razorpay only &mdash; amount unit</label>
                    <select name="amount_unit" class="border border-gray-300 rounded-lg px-3 py-2 w-full text-sm">
                        <option value="auto">Detect automatically</option>
                        <option value="rupees">Rupees (use as-is)</option>
                        <option value="paise">Paise (divide by 100)</option>
                    </select>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-4">
                Accepts either an <strong>invoice export</strong> (23 columns) or a
                <strong>Razorpay payments export</strong> &mdash; the format is detected from the header.
                Uploading only analyses it &mdash; nothing is written until you confirm on the next screen.
            </p>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition">
                Analyse (dry run)
            </button>
        </form>
    </div>

    @if ($result)
        @php
            $c = $result['counts'];
            $reportFiles = ['plan.csv', 'blocked.csv', 'warnings.csv', 'readonly-diffs.csv', 'extras.csv', 'summary.json'];
        @endphp

        {{-- Summary --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-2">
                {{ $c['dry_run'] ? 'Dry run result' : 'Applied' }} &mdash; run {{ $runId }}
            </h2>

            <p class="text-sm text-gray-600 mb-4">
                Detected format:
                <strong>{{ $result['format'] === 'razorpay' ? 'Razorpay payments export' : 'Invoice export' }}</strong>
            </p>

            @if ($result['format'] === 'razorpay')
                <div class="mb-4 rounded-lg border {{ ($c['amount_unit'] ?? '') === 'paise' ? 'border-yellow-300 bg-yellow-50' : 'border-blue-200 bg-blue-50' }} px-4 py-3 text-sm">
                    <strong>Amount unit detected: {{ $c['amount_unit'] ?? 'rupees' }}.</strong>
                    @if (($c['amount_unit'] ?? '') === 'paise')
                        Every amount is being divided by 100 (Razorpay's API reports paise).
                    @else
                        Amounts are being used as-is.
                    @endif
                    Check a row in the change plan below before applying &mdash; if this is wrong, every
                    corrected amount will be wrong by a factor of 100.
                </div>
            @endif
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                @php
                    $metrics = $result['format'] === 'razorpay'
                        ? [
                            'Rows in file' => $c['file_rows'],
                            'Actionable (by status)' => $c['actionable_rows'],
                            'Planned changes' => $c['planned_changes'],
                            'Payments matched' => $c['payments_matched'],
                            'Blocked rows' => $c['blocked'],
                            'Warnings' => $c['warnings'],
                        ]
                        : [
                            'CSV rows' => $c['csv_rows'],
                            'Planned changes' => $c['planned_changes'],
                            'Invoices touched' => $c['invoices_touched'],
                            'Blocked rows' => $c['blocked'],
                            'Warnings' => $c['warnings'],
                            'Read-only diffs' => $c['readonly_diffs'],
                            'Extra invoices in DB' => $c['extras'],
                        ];
                @endphp
                @foreach ($metrics as $label => $value)
                    <div class="border border-gray-200 rounded-lg p-3">
                        <div class="text-xs uppercase text-gray-500">{{ $label }}</div>
                        <div class="text-xl font-semibold {{ $value > 0 && in_array($label, ['Blocked rows']) ? 'text-red-600' : 'text-gray-900' }}">{{ $value }}</div>
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ($reportFiles as $file)
                    <a href="{{ route('invoices.import-payments.report', [$runId, $file]) }}"
                       class="text-sm bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-3 py-1.5 rounded-lg transition">
                        {{ $file }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Blocked --}}
        @if (count($result['blocked']))
            <div class="bg-white border border-red-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-red-800 mb-1">Blocked rows ({{ count($result['blocked']) }})</h2>
                <p class="text-sm text-gray-600 mb-4">These rows are skipped entirely. Resolve them before relying on a byte-for-byte match.</p>
                @if ($result['format'] === 'razorpay')
                    @include('invoices._import_table', [
                        'headers' => ['Payment', 'Order', 'Line', 'Email', 'Amount', 'Status', 'Reason', 'Detail'],
                        'keys' => ['payment_id', 'order_id', 'line', 'email', 'amount', 'status', 'reason', 'detail'],
                        'rows' => $result['blocked'],
                    ])
                @else
                    @include('invoices._import_table', [
                        'headers' => ['Invoice', 'Line', 'Email', 'Reason', 'Detail'],
                        'keys' => ['invoice_number', 'line', 'email', 'reason', 'detail'],
                        'rows' => $result['blocked'],
                    ])
                @endif
            </div>
        @endif

        {{-- Plan --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-1">Change plan ({{ $result['planTotal'] }})</h2>
            <p class="text-sm text-gray-600 mb-4">
                Every write this import will make.
                @if ($result['planTotal'] > count($result['plan']))
                    Showing the first {{ count($result['plan']) }} &mdash; download plan.csv for the rest.
                @endif
            </p>
            @include('invoices._import_table', [
                'headers' => [$result['format'] === 'razorpay' ? 'Payment' : 'Invoice', 'Action', 'Table', 'Key', 'Column', 'From', 'To'],
                'keys' => [$result['format'] === 'razorpay' ? 'payment_id' : 'invoice_number', 'action', 'table', 'key', 'column', 'from', 'to'],
                'rows' => $result['plan'],
            ])
        </div>

        {{-- Warnings --}}
        @if ($result['warningsTotal'])
            <div class="bg-white border border-yellow-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-yellow-800 mb-1">Warnings ({{ $result['warningsTotal'] }})</h2>
                <p class="text-sm text-gray-600 mb-4">Written anyway, but worth a look &mdash; unresolved plan or batch names, multiple items or payments on one invoice.</p>
                @include('invoices._import_table', [
                    'headers' => [$result['format'] === 'razorpay' ? 'Payment' : 'Invoice', 'Line', 'Code', 'Detail'],
                    'keys' => [$result['format'] === 'razorpay' ? 'payment_id' : 'invoice_number', 'line', 'code', 'detail'],
                    'rows' => $result['warnings'],
                ])
            </div>
        @endif

        {{-- Read-only diffs --}}
        @if ($result['readonlyTotal'])
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Read-only differences ({{ $result['readonlyTotal'] }})</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Columns that describe the student record rather than the payment. The importer deliberately
                    does <strong>not</strong> write these &mdash; they are listed so a human can decide.
                </p>
                @include('invoices._import_table', [
                    'headers' => ['Invoice', 'Column', 'CSV value', 'Current DB value'],
                    'keys' => ['invoice_number', 'column', 'csv_value', 'db_value'],
                    'rows' => $result['readonlyDiffs'],
                ])
            </div>
        @endif

        {{-- Extras --}}
        @if (count($result['extras']))
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Invoices in the DB but not in the CSV ({{ count($result['extras']) }})</h2>
                <p class="text-sm text-gray-600 mb-4">Each of these adds an extra row to a re-export. Nothing is deleted.</p>
                @include('invoices._import_table', [
                    'headers' => ['Invoice', 'Student', 'School', 'Status', 'Date', 'Total'],
                    'keys' => ['invoice_number', 'student_id', 'school_id', 'status', 'invoice_date', 'total_due'],
                    'rows' => array_slice($result['extras'], 0, 200),
                ])
            </div>
        @endif

        {{-- Apply --}}
        @if ($c['dry_run'])
            <div class="bg-white border-2 border-blue-300 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-2">Apply this import</h2>
                <p class="text-sm text-gray-600 mb-4">
                    Runs inside a single transaction: if anything fails, or if the post-write check finds a
                    cell that still disagrees with the CSV, nothing is written at all.
                </p>
                <form action="{{ route('invoices.import-payments.apply') }}" method="POST" class="flex flex-col gap-3">
                    @csrf
                    <input type="hidden" name="run_id" value="{{ $runId }}">
                    <input type="hidden" name="create_missing" value="{{ (($options['create_missing'] ?? $options['create_missing_invoices'] ?? false)) ? 1 : 0 }}">
                    <input type="hidden" name="sync_fees_status" value="{{ (($options['sync_fees_status'] ?? $options['mark_paid'] ?? false)) ? 1 : 0 }}">
                    <input type="hidden" name="correct_amounts" value="{{ ($options['correct_amounts'] ?? true) ? 1 : 0 }}">
                    <input type="hidden" name="statuses" value="{{ $options['statuses'] ?? 'captured_authorized' }}">
                    <input type="hidden" name="amount_unit" value="{{ $result['counts']['amount_unit'] ?? ($options['amount_unit'] ?? 'auto') }}">

                    <label class="text-sm text-gray-700">
                        Type <code class="bg-gray-100 px-1.5 py-0.5 rounded font-mono">{{ $confirmPhrase }}</code> to confirm:
                    </label>
                    <input type="text" name="confirm_phrase" required autocomplete="off"
                           class="border border-gray-300 rounded-lg px-3 py-2 max-w-xs font-mono">

                    <div>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition">
                            Apply {{ $result['planTotal'] }} change(s)
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif
</div>
@endsection
