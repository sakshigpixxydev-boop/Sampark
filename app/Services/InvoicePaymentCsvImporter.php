<?php

namespace App\Services;

use App\Models\FeePlan;
use App\Models\Invoice;
use App\Models\PaymentMode;
use App\Models\SemesterBatch;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Imports an invoice-export CSV back into the database, so that re-running the
 * export reproduces the input file byte-for-byte.
 *
 * Phases: parse -> preload -> resolve -> plan -> [apply] -> verify.
 * Nothing is written before apply(), so a dry run exercises exactly the same
 * code path as a real run.
 *
 * The CSV is treated as the source of truth for payment data only. Student
 * names, email, mobile and the country/state/district/taluka foreign keys are
 * never written - they are diffed and reported instead.
 */
class InvoicePaymentCsvImporter
{
    /** Columns the importer writes. Everything else is report-only. */
    public const WRITABLE = InvoiceCsvExportService::WRITABLE_COLUMNS;

    /** Derived from the student's state at export time; never written here. */
    public const TAX_COLUMNS = ['Taxable Amount', 'SGST', 'CGST', 'IGST'];

    /** Blocking outcomes: the row cannot round-trip and is skipped. */
    public const BLOCKED_REASONS = [
        'MISSING_INVOICE',
        'UNRESOLVED_STUDENT',
        'AMBIGUOUS_STUDENT',
        'ORDER_ID_CONFLICT',
        'INVOICE_VOID',
        'STUDENT_IS_LEAD',
        'STUDENT_SOFT_DELETED',
        'NO_STUDENT_ON_INVOICE',
        'BAD_INVOICE_NUMBER',
    ];

    protected array $rows = [];
    protected array $plan = [];
    protected array $blocked = [];
    protected array $warnings = [];
    protected array $readonlyDiffs = [];
    protected array $extras = [];
    protected array $counts = [];

    /** @var array<int,\App\Models\Invoice> */
    protected $existingInvoices;
    protected $feePlansByName;
    protected $batchesByDisplayName;
    protected $batchesByEdmingleName;
    protected int $onlinePaymodeId = 1;

    public function __construct(
        protected InvoiceCsvExportService $exporter,
        protected InvoiceTaxService $taxService,
        protected TabularFileReader $reader
    ) {
    }

    public function getPlan(): array { return $this->plan; }
    public function getBlocked(): array { return $this->blocked; }
    public function getWarnings(): array { return $this->warnings; }
    public function getReadonlyDiffs(): array { return $this->readonlyDiffs; }
    public function getExtras(): array { return $this->extras; }
    public function getCounts(): array { return $this->counts; }
    public function getRows(): array { return $this->rows; }

    /**
     * Run the import.
     *
     * @param  string  $path       Absolute path to the CSV.
     * @param  array   $options    dry_run, create_missing, sync_fees_status, only, limit
     * @return array   Summary counts.
     *
     * @throws \RuntimeException on an unreadable or malformed file.
     */
    public function run(string $path, array $options = []): array
    {
        $options = array_merge([
            'dry_run' => true,
            'create_missing' => false,
            'sync_fees_status' => false,
            'only' => null,
            'limit' => null,
        ], $options);

        $this->parse($path, $options);
        $this->preload();
        $this->buildPlan($options);
        $this->collectExtras();

        if (!$options['dry_run']) {
            $this->apply($options);
        }

        $this->counts = [
            'csv_rows' => count($this->rows),
            'planned_changes' => count($this->plan),
            'invoices_touched' => count(array_unique(array_column($this->plan, 'invoice_number'))),
            'blocked' => count($this->blocked),
            'warnings' => count($this->warnings),
            'readonly_diffs' => count($this->readonlyDiffs),
            'extras' => count($this->extras),
            'dry_run' => (bool) $options['dry_run'],
        ];

        return $this->counts;
    }

    /**
     * Columns whose value must be canonicalised before comparison, because a
     * spreadsheet hands them back typed (2500.0, a DateTime, a phone number as
     * a float) while the export renders them as formatted strings.
     */
    protected const MONEY_COLUMNS = ['Taxable Amount', 'SGST', 'CGST', 'IGST', 'Total Amount'];
    protected const DATE_COLUMNS = ['Payment Date', 'Registration Date'];

    /**
     * Does this header look like one of our invoice exports?
     */
    public static function matchesHeader(array $header): bool
    {
        $header = array_map(fn ($h) => trim((string) $h), $header);
        while (!empty($header) && end($header) === '') {
            array_pop($header);
        }

        return $header === InvoiceCsvExportService::COLUMNS;
    }

    /**
     * Read the file and validate its shape. A header that does not match the
     * export's own column list exactly is a hard abort - importing a file with
     * shifted columns would silently corrupt data.
     */
    protected function parse(string $path, array $options): void
    {
        $records = $this->reader->read($path);
        $header = $this->reader->trimTrailingBlanks(array_shift($records));

        $expected = InvoiceCsvExportService::COLUMNS;
        if ($header !== $expected) {
            throw new \RuntimeException($this->describeHeaderMismatch($header, $expected));
        }

        $only = $options['only']
            ? array_map('trim', explode(',', $options['only']))
            : null;

        $lineNo = 1;
        $seen = [];
        foreach ($records as $data) {
            $lineNo++;

            if ($this->reader->isBlankRecord($data)) {
                continue;
            }

            // Tolerate trailing padding columns.
            if (count($data) > count($expected) && $this->reader->isBlankRecord(array_slice($data, count($expected)))) {
                $data = array_slice($data, 0, count($expected));
            }

            if (count($data) !== count($expected)) {
                throw new \RuntimeException(sprintf(
                    'Row %d has %d fields, expected %d.', $lineNo, count($data), count($expected)
                ));
            }

            $row = $this->normaliseRow(array_combine($expected, $data));
            $invoiceNumber = $row['Invoice Number'];

            if (isset($seen[$invoiceNumber])) {
                throw new \RuntimeException(sprintf(
                    'Duplicate Invoice Number %s on rows %d and %d.',
                    $invoiceNumber, $seen[$invoiceNumber], $lineNo
                ));
            }
            $seen[$invoiceNumber] = $lineNo;

            if ($only !== null && !in_array($invoiceNumber, $only, true)) {
                continue;
            }

            $row['__line'] = $lineNo;
            $this->rows[] = $row;

            if ($options['limit'] && count($this->rows) >= (int) $options['limit']) {
                break;
            }
        }
    }

    /**
     * Explain a header mismatch in terms a human can act on: which columns are
     * unexpected, which are missing, and what the file actually starts with.
     * A bare column count is not enough to tell a Razorpay export apart from a
     * stale invoice export.
     */
    protected function describeHeaderMismatch(array $header, array $expected): string
    {
        $unexpected = array_values(array_diff($header, $expected));
        $missing = array_values(array_diff($expected, $header));

        $message = sprintf(
            "This file does not look like an invoice export. It has %d column(s); the invoice export has %d.",
            count($header),
            count($expected)
        );

        if (RazorpayPaymentCsvImporter::matchesHeader($header)) {
            $message .= "\n\nIt looks like a Razorpay payments export - use the Razorpay import mode for it.";
        }

        if ($missing) {
            $message .= "\n\nMissing expected column(s): " . implode(', ', $missing);
        }
        if ($unexpected) {
            $shown = array_slice($unexpected, 0, 12);
            $message .= "\n\nUnexpected column(s): " . implode(', ', $shown)
                . (count($unexpected) > count($shown) ? sprintf(' ... and %d more', count($unexpected) - count($shown)) : '');
        }

        $message .= "\n\nFile header: " . implode(' | ', array_slice($header, 0, 40));

        return $message;
    }

    /**
     * Canonicalise a parsed row to the exact strings the export would render.
     *
     * Without this, an .xlsx round-trip fails verification for cosmetic
     * reasons: Excel returns 2500 where the export writes "2500.00", a
     * DateTime or serial number where it writes "2026-08-22", and a float
     * where a mobile number should be digits.
     */
    protected function normaliseRow(array $row): array
    {
        foreach ($row as $column => $value) {
            if ($value === null) {
                $row[$column] = '';
                continue;
            }
            if ($value instanceof \DateTimeInterface) {
                $row[$column] = $value->format('Y-m-d');
                continue;
            }
            $row[$column] = trim((string) $value);
        }

        foreach (self::MONEY_COLUMNS as $column) {
            if ($row[$column] !== '' && is_numeric(str_replace(',', '', $row[$column]))) {
                $row[$column] = number_format((float) str_replace(',', '', $row[$column]), 2, '.', '');
            }
        }

        foreach (self::DATE_COLUMNS as $column) {
            $row[$column] = $this->reader->normaliseDate($row[$column]);
        }

        // A mobile number read from Excel arrives as a float (9426549856.0).
        if ($row['Mobile'] !== '' && is_numeric($row['Mobile'])) {
            $row['Mobile'] = number_format((float) $row['Mobile'], 0, '.', '');
        }

        return $row;
    }

    /**
     * One query per lookup, rather than per row.
     */
    protected function preload(): void
    {
        $this->feePlansByName = FeePlan::all()->keyBy('fee_name');

        // Mirror the export's own precedence: display_name first, and
        // edmingle_batch_name only where display_name is empty. Lowest id wins
        // a duplicate name so the choice is deterministic.
        $batches = SemesterBatch::orderBy('sembatches_id')->get();
        $this->batchesByDisplayName = [];
        $this->batchesByEdmingleName = [];
        foreach ($batches as $batch) {
            if (!empty($batch->display_name)) {
                if (!isset($this->batchesByDisplayName[$batch->display_name])) {
                    $this->batchesByDisplayName[$batch->display_name] = $batch;
                }
            } elseif (!empty($batch->edmingle_batch_name)) {
                if (!isset($this->batchesByEdmingleName[$batch->edmingle_batch_name])) {
                    $this->batchesByEdmingleName[$batch->edmingle_batch_name] = $batch;
                }
            }
        }

        $invoiceIds = [];
        foreach ($this->rows as $row) {
            $id = InvoiceCsvExportService::invoiceIdFrom((string) $row['Invoice Number']);
            if ($id !== null) {
                $invoiceIds[] = $id;
            }
        }

        $this->existingInvoices = Invoice::with(InvoiceCsvExportService::eagerLoads())
            ->whereIn('invoice_id', $invoiceIds)
            ->get()
            ->keyBy('invoice_id');

        // Same fallback as InvoiceService::generateRegistrationInvoiceAndPayment().
        $this->onlinePaymodeId = PaymentMode::where('paymodename', 'Online')->value('paymode_id') ?: 1;
    }

    protected function block(array $row, string $reason, string $detail = ''): void
    {
        $this->blocked[] = [
            'invoice_number' => trim((string) $row['Invoice Number']),
            'line' => $row['__line'] ?? '',
            'email' => trim((string) $row['Email']),
            'reason' => $reason,
            'detail' => $detail,
        ];
    }

    protected function warn(array $row, string $code, string $detail = ''): void
    {
        $this->warnings[] = [
            'invoice_number' => trim((string) $row['Invoice Number']),
            'line' => $row['__line'] ?? '',
            'code' => $code,
            'detail' => $detail,
        ];
    }

    protected function change(string $invoiceNumber, string $action, string $table, $key, string $column, $from, $to): void
    {
        // Normalise for comparison so "2500.00" vs 2500.0 is not a change.
        if ($this->sameValue($from, $to)) {
            return;
        }

        $this->plan[] = [
            'invoice_number' => $invoiceNumber,
            'action' => $action,
            'table' => $table,
            'key' => (string) $key,
            'column' => $column,
            'from' => $this->scalarise($from),
            'to' => $this->scalarise($to),
        ];
    }

    protected function sameValue($a, $b): bool
    {
        if ($a === null && ($b === null || $b === '')) return true;
        if ($b === null && ($a === null || $a === '')) return true;
        if (is_numeric($a) && is_numeric($b)) {
            return abs((float) $a - (float) $b) < 0.005;
        }
        return (string) $this->scalarise($a) === (string) $this->scalarise($b);
    }

    protected function scalarise($v): string
    {
        if ($v === null) return '';
        if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d H:i:s');
        if (is_bool($v)) return $v ? '1' : '0';
        return (string) $v;
    }

    /**
     * Work out every write the CSV implies, without performing any of them.
     */
    protected function buildPlan(array $options): void
    {
        // Order-id is stored per STUDENT but appears on every invoice row, so
        // two rows for one student must agree. Detect that up front.
        $orderIdByStudent = [];

        foreach ($this->rows as $row) {
            $invoiceNumber = trim((string) $row['Invoice Number']);
            $invoiceId = InvoiceCsvExportService::invoiceIdFrom($invoiceNumber);

            if ($invoiceId === null) {
                $this->block($row, 'BAD_INVOICE_NUMBER', 'Expected the form INV-000123');
                continue;
            }

            $invoice = $this->existingInvoices->get($invoiceId);
            $isNew = ($invoice === null);

            if ($isNew && !$options['create_missing']) {
                $this->block($row, 'MISSING_INVOICE', "invoice_id {$invoiceId} does not exist; re-run with create-missing to insert it");
                continue;
            }

            if (!$isNew && $invoice->status === 'VOID') {
                $this->block($row, 'INVOICE_VOID', 'Invoice is VOID; voiding was a deliberate act, leaving it alone');
                continue;
            }

            // Locate the student.
            if (!$isNew) {
                $student = $invoice->student;
                if (!$student) {
                    // Either the FK is null, or the global excludeLeads scope /
                    // soft delete is hiding the row. Distinguish the two, because
                    // a hidden student means the export can never reproduce the
                    // name and address columns for this invoice.
                    $raw = $invoice->student_id
                        ? Student::withLeads()->withTrashed()->find($invoice->student_id)
                        : null;

                    if (!$raw) {
                        $this->block($row, 'NO_STUDENT_ON_INVOICE', "invoice_id {$invoiceId} has student_id " . ($invoice->student_id ?? 'NULL'));
                        continue;
                    }
                    if ($raw->trashed()) {
                        $this->block($row, 'STUDENT_SOFT_DELETED', "student_id {$raw->student_id} is soft-deleted");
                        continue;
                    }
                    $this->block($row, 'STUDENT_IS_LEAD', "student_id {$raw->student_id} is flagged is_lead");
                    continue;
                }
            } else {
                $student = $this->resolveStudentForNewInvoice($row);
                if ($student === null) {
                    continue; // already blocked with a reason
                }
            }

            $studentId = $student->student_id;
            $csvOrderId = trim((string) $row['Razorpay Order ID']);
            $csvOrderId = $csvOrderId === '' ? null : $csvOrderId;

            if (array_key_exists($studentId, $orderIdByStudent)) {
                if ($orderIdByStudent[$studentId]['value'] !== $csvOrderId) {
                    $this->block($row, 'ORDER_ID_CONFLICT', sprintf(
                        'student_id %d already needs order id "%s" from %s, but this row wants "%s"',
                        $studentId,
                        $orderIdByStudent[$studentId]['value'] ?? '',
                        $orderIdByStudent[$studentId]['invoice_number'],
                        $csvOrderId ?? ''
                    ));
                    continue;
                }
            } else {
                $orderIdByStudent[$studentId] = ['value' => $csvOrderId, 'invoice_number' => $invoiceNumber];
            }

            $this->planRow($row, $invoiceNumber, $invoiceId, $invoice, $student, $isNew, $options);
        }
    }

    /**
     * Deterministic student lookup for the create-missing path, narrowing one
     * step at a time and refusing to guess when still ambiguous.
     */
    protected function resolveStudentForNewInvoice(array $row): ?Student
    {
        $email = strtolower(trim((string) $row['Email']));

        if ($email === '') {
            $this->block($row, 'UNRESOLVED_STUDENT', 'CSV row has no email to match on');
            return null;
        }

        $candidates = Student::withLeads()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->get();

        if ($candidates->isEmpty()) {
            $this->block($row, 'UNRESOLVED_STUDENT', "No student with email {$email}");
            return null;
        }

        if ($candidates->count() === 1) {
            return $candidates->first();
        }

        // 2. Narrow by first + last name.
        $fname = strtoupper(trim((string) $row['First Name']));
        $lname = strtoupper(trim((string) $row['Last Name']));
        $byName = $candidates->filter(fn ($s) =>
            strtoupper(trim((string) $s->fname)) === $fname &&
            strtoupper(trim((string) $s->lname)) === $lname
        );
        if ($byName->count() === 1) {
            return $byName->first();
        }
        if ($byName->count() > 1) {
            $candidates = $byName;
        }

        // 3. Narrow by the razorpay ids the CSV carries.
        $orderId = trim((string) $row['Razorpay Order ID']);
        $paymentId = trim((string) $row['Razorpay Payment ID']);
        if ($orderId !== '' || ($paymentId !== '' && $paymentId !== 'N/A')) {
            $byRzp = $candidates->filter(fn ($s) =>
                ($orderId !== '' && $s->razorpay_order_id === $orderId) ||
                ($paymentId !== '' && $paymentId !== 'N/A' && $s->razorpay_payment_id === $paymentId)
            );
            if ($byRzp->count() === 1) {
                return $byRzp->first();
            }
            if ($byRzp->count() > 1) {
                $candidates = $byRzp;
            }
        }

        // 4. Narrow to students not already holding an invoice for this plan.
        $plan = trim((string) $row['Subscription Plan']);
        $withoutPlan = $candidates->filter(function ($s) use ($plan) {
            return !Invoice::where('student_id', $s->student_id)
                ->whereHas('items', fn ($q) => $q->where('description', $plan))
                ->exists();
        });
        if ($withoutPlan->count() === 1) {
            return $withoutPlan->first();
        }

        $this->block($row, 'AMBIGUOUS_STUDENT', sprintf(
            '%d students share email %s and could not be narrowed (ids: %s)',
            $candidates->count(), $email, $candidates->pluck('student_id')->implode(', ')
        ));

        return null;
    }

    /**
     * Plan the writes for a single CSV row.
     */
    protected function planRow(array $row, string $invoiceNumber, int $invoiceId, ?Invoice $invoice, Student $student, bool $isNew, array $options): void
    {
        $total = $this->money($row['Total Amount']);
        $planName = trim((string) $row['Subscription Plan']);
        $batchName = trim((string) $row['Batch Name']);
        $paymentDate = $this->date($row['Payment Date']);
        $orderId = trim((string) $row['Razorpay Order ID']) ?: null;
        $paymentTxn = trim((string) $row['Razorpay Payment ID']);

        if ($paymentDate === null) {
            $this->block($row, 'BAD_INVOICE_NUMBER', "Unparseable Payment Date '{$row['Payment Date']}'");
            return;
        }

        // ---- invoice header ----
        $action = $isNew ? 'create' : 'update';

        $this->change($invoiceNumber, $action, 'invoice', $invoiceId, 'invoice_date',
            $invoice?->invoice_date?->format('Y-m-d'), $paymentDate);
        $this->change($invoiceNumber, $action, 'invoice', $invoiceId, 'total_before_discount',
            $invoice?->total_before_discount, $total);
        $this->change($invoiceNumber, $action, 'invoice', $invoiceId, 'discount_total',
            $invoice?->discount_total, 0);
        $this->change($invoiceNumber, $action, 'invoice', $invoiceId, 'total_due',
            $invoice?->total_due, $total);
        $this->change($invoiceNumber, $action, 'invoice', $invoiceId, 'status',
            $invoice?->status, 'PAID');

        if ($isNew) {
            $this->change($invoiceNumber, 'create', 'invoice', $invoiceId, 'student_id', null, $student->student_id);
            $this->change($invoiceNumber, 'create', 'invoice', $invoiceId, 'school_id', null, $student->school_id);
        }

        // ---- invoice item (canonical row = lowest id) ----
        $item = $isNew ? null : $invoice->items->sortBy('invoice_item_id')->first();
        if (!$isNew && $invoice->items->count() > 1) {
            $this->warn($row, 'MULTIPLE_ITEMS_ON_INVOICE', $invoice->items->count() . ' items; only the lowest id is updated');
        }

        $feePlan = $this->feePlansByName[$planName] ?? null;
        if (!$feePlan) {
            $this->warn($row, 'PLAN_UNRESOLVED', "No fee_plan named '{$planName}'; description written, fee_plan_id left as-is");
        }

        $itemKey = $item ? $item->invoice_item_id : 'new';
        $this->change($invoiceNumber, $item ? 'update' : 'create', 'invoice_item', $itemKey, 'description',
            $item?->description, $planName);
        $this->change($invoiceNumber, $item ? 'update' : 'create', 'invoice_item', $itemKey, 'unit_amount',
            $item?->unit_amount, $total);
        $this->change($invoiceNumber, $item ? 'update' : 'create', 'invoice_item', $itemKey, 'line_amount',
            $item?->line_amount, $total);
        if ($feePlan) {
            $this->change($invoiceNumber, $item ? 'update' : 'create', 'invoice_item', $itemKey, 'fee_plan_id',
                $item?->fee_plan_id, $feePlan->fee_plan_id);
        }

        // ---- payment log (canonical row = lowest id, keyed by invoice, never by txnid) ----
        $payment = $isNew ? null : $invoice->payments->sortBy('payment_id')->first();
        if (!$isNew && $invoice->payments->count() > 1) {
            $this->warn($row, 'MULTIPLE_PAYMENTS_ON_INVOICE', $invoice->payments->count() . ' payment rows; only the lowest id is updated');
        }

        $payKey = $payment ? $payment->payment_id : 'new';
        $this->change($invoiceNumber, $payment ? 'update' : 'create', 'paymentlog', $payKey, 'txnid',
            $payment?->txnid, $paymentTxn === '' ? 'N/A' : $paymentTxn);
        $this->change($invoiceNumber, $payment ? 'update' : 'create', 'paymentlog', $payKey, 'amount',
            $payment?->amount, $total);
        $this->change($invoiceNumber, $payment ? 'update' : 'create', 'paymentlog', $payKey, 'processdatetime',
            $payment?->processdatetime?->format('Y-m-d H:i:s'), $paymentDate . ' 00:00:00');
        $this->change($invoiceNumber, $payment ? 'update' : 'create', 'paymentlog', $payKey, 'status',
            $payment?->status, '1');
        if (!$payment) {
            $this->change($invoiceNumber, 'create', 'paymentlog', $payKey, 'paymode_id', null, $this->onlinePaymodeId);
        }

        // ---- student ----
        $this->change($invoiceNumber, 'update', 'student', $student->student_id, 'razorpay_order_id',
            $student->razorpay_order_id, $orderId);
        $this->change($invoiceNumber, 'update', 'student', $student->student_id, 'subscription',
            $student->subscription, $planName);

        if ($options['sync_fees_status']) {
            $this->change($invoiceNumber, 'update', 'student', $student->student_id, 'fees_status',
                $student->fees_status, 'Paid');
        }

        // ---- batch link ----
        $batch = $this->resolveBatch($batchName);
        if ($batchName !== '' && $batchName !== 'N/A' && !$batch) {
            $this->warn($row, 'BATCH_UNRESOLVED', "No sembatches row named '{$batchName}'");
        } elseif ($batch) {
            $linked = $student->semesterBatches
                ->contains(fn ($b) => $b->sembatches_id == $batch->sembatches_id);
            if (!$linked) {
                $this->change($invoiceNumber, 'create', 'student_sembatches', $student->student_id . ':' . $batch->sembatches_id,
                    'sembatches_id', null, $batch->sembatches_id);
            } else {
                // The export reads the FIRST linked batch. If the CSV's batch is
                // not first, the row will not round-trip.
                $first = $student->semesterBatches->first();
                if ($first && $first->sembatches_id != $batch->sembatches_id) {
                    $this->warn($row, 'BATCH_NOT_FIRST', sprintf(
                        "Student's first batch is '%s' but the CSV expects '%s'",
                        $first->display_name ?: $first->edmingle_batch_name, $batchName
                    ));
                }
            }
        }

        // ---- read-only columns: diff and report, never write ----
        if (!$isNew) {
            $this->diffReadonly($row, $invoice, $invoiceNumber);
        }
    }

    protected function resolveBatch(string $batchName): ?SemesterBatch
    {
        if ($batchName === '' || $batchName === 'N/A') {
            return null;
        }
        return $this->batchesByDisplayName[$batchName]
            ?? $this->batchesByEdmingleName[$batchName]
            ?? null;
    }

    /**
     * Compare the columns the importer refuses to write against what the export
     * would currently produce. These describe the student record, not the
     * payment, so they are surfaced for a human rather than overwritten.
     */
    protected function diffReadonly(array $row, Invoice $invoice, string $invoiceNumber): void
    {
        $current = $this->exporter->rowFor($invoice, 0);
        $columns = InvoiceCsvExportService::COLUMNS;

        foreach ($columns as $i => $column) {
            if ($column === 'No.' || $column === 'Invoice Number') {
                continue;
            }
            if (in_array($column, self::WRITABLE, true)) {
                continue;
            }
            $csvValue = trim((string) $row[$column]);
            $dbValue = trim((string) $current[$i]);
            if ($csvValue !== $dbValue) {
                $this->readonlyDiffs[] = [
                    'invoice_number' => $invoiceNumber,
                    'column' => $column,
                    'csv_value' => $csvValue,
                    'db_value' => $dbValue,
                    // Tax cells are derived from the student's state, so a
                    // difference here points at the student record, not the
                    // invoice.
                    'note' => in_array($column, self::TAX_COLUMNS, true)
                        ? 'Derived from the student state - fix state_id/country_id to change it'
                        : '',
                ];
            }
        }
    }

    /**
     * Invoices present in the DB but absent from the CSV. Each of these adds an
     * extra row to a re-export, so they must be explained before claiming a
     * byte-for-byte match. Reported only - never deleted.
     */
    protected function collectExtras(): void
    {
        $csvIds = [];
        foreach ($this->rows as $row) {
            $id = InvoiceCsvExportService::invoiceIdFrom((string) $row['Invoice Number']);
            if ($id !== null) {
                $csvIds[$id] = true;
            }
        }

        Invoice::select('invoice_id', 'student_id', 'school_id', 'status', 'invoice_date', 'total_due')
            ->orderBy('invoice_id')
            ->chunk(500, function ($chunk) use ($csvIds) {
                foreach ($chunk as $invoice) {
                    if (!isset($csvIds[$invoice->invoice_id])) {
                        $this->extras[] = [
                            'invoice_number' => InvoiceCsvExportService::invoiceNumberFor($invoice->invoice_id),
                            'student_id' => $invoice->student_id ?? '',
                            'school_id' => $invoice->school_id ?? '',
                            'status' => $invoice->status,
                            'invoice_date' => $invoice->invoice_date?->format('Y-m-d') ?? '',
                            'total_due' => $invoice->total_due,
                        ];
                    }
                }
            });
    }

    protected function money($value): float
    {
        return round((float) str_replace(',', '', trim((string) $value)), 2);
    }

    protected function date($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        try {
            return Carbon::createFromFormat('Y-m-d', $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Execute the planned writes.
     *
     * Everything runs in one transaction, and the post-write verification runs
     * inside it too: if any writable column still disagrees with the CSV after
     * writing, that is an importer bug, and rolling back is the right response.
     */
    protected function apply(array $options): void
    {
        $plannedInvoiceNumbers = array_flip(array_column($this->plan, 'invoice_number'));

        DB::transaction(function () use ($options, $plannedInvoiceNumbers) {
            $touchedInvoiceIds = [];
            $syncStudentIds = [];

            foreach ($this->rows as $row) {
                $invoiceNumber = trim((string) $row['Invoice Number']);

                if (!isset($plannedInvoiceNumbers[$invoiceNumber])) {
                    continue; // nothing to do, or blocked
                }

                $invoiceId = InvoiceCsvExportService::invoiceIdFrom($invoiceNumber);
                $invoice = $this->existingInvoices->get($invoiceId);
                $isNew = ($invoice === null);

                $student = $isNew
                    ? $this->resolveStudentForNewInvoice($row)
                    : $invoice->student;

                if (!$student) {
                    continue;
                }

                $total = $this->money($row['Total Amount']);
                $paymentDate = $this->date($row['Payment Date']);
                $planName = trim((string) $row['Subscription Plan']);
                $paymentTxn = trim((string) $row['Razorpay Payment ID']);
                $paymentTxn = $paymentTxn === '' ? 'N/A' : $paymentTxn;
                $orderId = trim((string) $row['Razorpay Order ID']) ?: null;

                $invoiceAttrs = [
                    'invoice_date' => $paymentDate,
                    'total_before_discount' => $total,
                    'discount_total' => 0,
                    'total_due' => $total,
                    'status' => 'PAID',
                ];

                if ($isNew) {
                    // Explicit-PK insert must go through the query builder:
                    // Eloquent routes inserts via insertGetId(), and MySQL does
                    // not set LAST_INSERT_ID() for an explicit auto-increment
                    // value, so the model would come back with the wrong key.
                    // Note invoice.created_at/updated_at are DATE columns.
                    DB::table('invoice')->insert($invoiceAttrs + [
                        'invoice_id' => $invoiceId,
                        'due_date' => $paymentDate,
                        'notes' => 'Imported from invoice payment CSV',
                        'created_at' => $paymentDate,
                        'updated_at' => Carbon::now()->toDateString(),
                        'school_id' => $student->school_id,
                        'student_id' => $student->student_id,
                    ]);
                } else {
                    DB::table('invoice')->where('invoice_id', $invoiceId)->update($invoiceAttrs + [
                        'updated_at' => Carbon::now()->toDateString(),
                    ]);
                }

                $touchedInvoiceIds[] = $invoiceId;

                // --- invoice item ---
                $feePlan = $this->feePlansByName[$planName] ?? null;
                $itemAttrs = [
                    'description' => $planName,
                    'qty' => 1,
                    'unit_amount' => $total,
                    'line_amount' => $total,
                ];
                if ($feePlan) {
                    $itemAttrs['fee_plan_id'] = $feePlan->fee_plan_id;
                }

                $existingItemId = DB::table('invoice_item')
                    ->where('invoice_id', $invoiceId)
                    ->min('invoice_item_id');

                if ($existingItemId) {
                    DB::table('invoice_item')->where('invoice_item_id', $existingItemId)->update($itemAttrs);
                } else {
                    DB::table('invoice_item')->insert($itemAttrs + ['invoice_id' => $invoiceId]);
                }

                // --- payment log: keyed by invoice_id, NEVER by txnid, so two
                // invoices sharing one razorpay payment id each keep their row ---
                $paymentAttrs = [
                    'txnid' => $paymentTxn,
                    'amount' => $total,
                    'processdatetime' => $paymentDate . ' 00:00:00',
                    'status' => '1',
                ];

                $existingPaymentId = DB::table('paymentlog')
                    ->where('invoice_id', $invoiceId)
                    ->min('payment_id');

                if ($existingPaymentId) {
                    DB::table('paymentlog')->where('payment_id', $existingPaymentId)->update($paymentAttrs);
                } else {
                    DB::table('paymentlog')->insert($paymentAttrs + [
                        'invoice_id' => $invoiceId,
                        'paymode_id' => $this->onlinePaymodeId,
                    ]);
                }

                // --- student ---
                DB::table('student')->where('student_id', $student->student_id)->update([
                    'razorpay_order_id' => $orderId,
                    'subscription' => $planName,
                ]);

                if ($options['sync_fees_status']) {
                    DB::table('student')->where('student_id', $student->student_id)->update(['fees_status' => 'Paid']);
                    $syncStudentIds[] = $student->student_id;
                }

                // --- batch link ---
                $batch = $this->resolveBatch(trim((string) $row['Batch Name']));
                if ($batch) {
                    $exists = DB::table('student_sembatches')
                        ->where('student_id', $student->student_id)
                        ->where('sembatches_id', $batch->sembatches_id)
                        ->exists();

                    if (!$exists) {
                        DB::table('student_sembatches')->insert([
                            'student_id' => $student->student_id,
                            'sembatches_id' => $batch->sembatches_id,
                            'status' => 0,
                            'assigned_at' => Carbon::now(),
                        ]);
                    }
                }
            }

            $this->verifyOrFail($touchedInvoiceIds);

            Log::info('InvoicePaymentCsvImporter applied', [
                'invoices' => count($touchedInvoiceIds),
                'changes' => count($this->plan),
            ]);
        });
    }

    /**
     * Re-render every touched invoice through the export and compare the
     * writable cells to the CSV. Runs inside the transaction, so a mismatch
     * rolls the whole import back rather than leaving half-written data.
     *
     * @throws \RuntimeException
     */
    protected function verifyOrFail(array $touchedInvoiceIds): void
    {
        if (empty($touchedInvoiceIds)) {
            return;
        }

        $rowsByNumber = [];
        foreach ($this->rows as $row) {
            $rowsByNumber[trim((string) $row['Invoice Number'])] = $row;
        }

        $columns = InvoiceCsvExportService::COLUMNS;
        $writableIndexes = [];
        foreach ($columns as $i => $column) {
            if (in_array($column, self::WRITABLE, true)) {
                $writableIndexes[$i] = $column;
            }
        }

        $failures = [];

        Invoice::with(InvoiceCsvExportService::eagerLoads())
            ->whereIn('invoice_id', $touchedInvoiceIds)
            ->get()
            ->each(function ($invoice) use ($rowsByNumber, $writableIndexes, &$failures) {
                $invoiceNumber = InvoiceCsvExportService::invoiceNumberFor($invoice->invoice_id);
                $csvRow = $rowsByNumber[$invoiceNumber] ?? null;
                if (!$csvRow) {
                    return;
                }

                $rendered = $this->exporter->rowFor($invoice, 0);

                foreach ($writableIndexes as $i => $column) {
                    $expected = trim((string) $csvRow[$column]);
                    $actual = trim((string) $rendered[$i]);
                    if ($expected !== $actual) {
                        $failures[] = "{$invoiceNumber} [{$column}] expected '{$expected}', got '{$actual}'";
                    }
                }
            });

        if (!empty($failures)) {
            throw new \RuntimeException(
                "Post-write verification failed for " . count($failures) . " cell(s); rolling back.\n  - "
                . implode("\n  - ", array_slice($failures, 0, 25))
                . (count($failures) > 25 ? "\n  ... and " . (count($failures) - 25) . " more" : '')
            );
        }
    }

    /**
     * Write the report files and return the directory they landed in.
     */
    public function writeReports(string $runId): string
    {
        $dir = storage_path('app/invoice-csv-import/' . $runId);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $this->writeCsv("{$dir}/plan.csv",
            ['invoice_number', 'action', 'table', 'key', 'column', 'from', 'to'], $this->plan);
        $this->writeCsv("{$dir}/blocked.csv",
            ['invoice_number', 'line', 'email', 'reason', 'detail'], $this->blocked);
        $this->writeCsv("{$dir}/warnings.csv",
            ['invoice_number', 'line', 'code', 'detail'], $this->warnings);
        $this->writeCsv("{$dir}/readonly-diffs.csv",
            ['invoice_number', 'column', 'csv_value', 'db_value', 'note'], $this->readonlyDiffs);
        $this->writeCsv("{$dir}/extras.csv",
            ['invoice_number', 'student_id', 'school_id', 'status', 'invoice_date', 'total_due'], $this->extras);

        file_put_contents("{$dir}/summary.json", json_encode([
            'run_id' => $runId,
            'counts' => $this->counts,
            'php_version' => PHP_VERSION,
            'database' => config('database.connections.' . config('database.default') . '.database'),
            'app_env' => config('app.env'),
        ], JSON_PRETTY_PRINT));

        return $dir;
    }

    protected function writeCsv(string $path, array $header, array $rows): void
    {
        $handle = fopen($path, 'w');
        fputcsv($handle, $header);
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($k) => $row[$k] ?? '', $header));
        }
        fclose($handle);
    }
}
