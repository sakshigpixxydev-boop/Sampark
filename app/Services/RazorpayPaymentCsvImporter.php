<?php

namespace App\Services;

use App\Models\FeePlan;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentLog;
use App\Models\PaymentMode;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Imports a Razorpay *Payments* dashboard export and reconciles it against our
 * invoices, payment logs and students.
 *
 * Three things about this format are easy to get wrong, and are handled
 * explicitly below:
 *
 *  - `invoice_id` is Razorpay's own invoice reference (inv_...), NOT our
 *    invoice.invoice_id. It is never used for matching.
 *  - `fee` and `tax` are Razorpay's charges for processing the payment, NOT
 *    our GST. They are never written to invoice.sgst/cgst/igst.
 *  - `amount` is in paise in Razorpay's API but often in rupees in a dashboard
 *    export. The unit is detected, reported, and overridable.
 */
class RazorpayPaymentCsvImporter
{
    /** Columns that must be present for a file to be a Razorpay payments export. */
    public const REQUIRED_COLUMNS = ['id', 'amount', 'status', 'order_id'];

    /** Statuses that represent money we can act on, by mode. */
    public const STATUS_SETS = [
        'captured' => ['captured'],
        'captured_authorized' => ['captured', 'authorized'],
        'all' => ['captured', 'authorized', 'refunded', 'failed', 'created'],
    ];

    /** Razorpay status -> paymentlog.status (0 PENDING, 1 AUTHORIZED, 2 REFUNDED, 3 FAILED, 4 CANCELLED). */
    public const STATUS_MAP = [
        'captured' => '1',
        'authorized' => '1',
        'refunded' => '2',
        'failed' => '3',
        'created' => '0',
    ];

    protected array $rows = [];
    protected array $plan = [];
    protected array $blocked = [];
    protected array $warnings = [];
    protected array $counts = [];
    protected array $header = [];
    protected string $amountUnit = 'rupees';
    protected int $onlinePaymodeId = 1;
    protected $feePlansById;
    protected $feePlansByName;

    public function __construct(
        protected TabularFileReader $reader,
        protected InvoiceCsvExportService $exporter
    ) {
    }

    public function getPlan(): array { return $this->plan; }
    public function getBlocked(): array { return $this->blocked; }
    public function getWarnings(): array { return $this->warnings; }
    public function getCounts(): array { return $this->counts; }
    public function getAmountUnit(): string { return $this->amountUnit; }

    /**
     * Does this header look like a Razorpay payments export?
     */
    public static function matchesHeader(array $header): bool
    {
        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

        foreach (self::REQUIRED_COLUMNS as $required) {
            if (!in_array($required, $header, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array  $options  dry_run, statuses, amount_unit, create_missing_invoices,
     *                          correct_amounts, mark_paid, limit
     */
    public function run(string $path, array $options = []): array
    {
        $options = array_merge([
            'dry_run' => true,
            'statuses' => 'captured_authorized',
            'amount_unit' => 'auto',
            'create_missing_invoices' => true,
            'correct_amounts' => true,
            'mark_paid' => true,
            'limit' => null,
        ], $options);

        $this->parse($path, $options);
        $this->detectAmountUnit($options);
        $this->preload();
        $this->buildPlan($options);

        if (!$options['dry_run']) {
            $this->apply($options);
        }

        $this->counts = [
            'file_rows' => count($this->rows),
            'actionable_rows' => count(array_filter($this->rows, fn ($r) => $this->isActionable($r, $options))),
            'planned_changes' => count($this->plan),
            'payments_matched' => count(array_unique(array_column($this->plan, 'payment_id'))),
            'blocked' => count($this->blocked),
            'warnings' => count($this->warnings),
            'amount_unit' => $this->amountUnit,
            'dry_run' => (bool) $options['dry_run'],
        ];

        return $this->counts;
    }

    protected function parse(string $path, array $options): void
    {
        $records = $this->reader->read($path);
        $header = $this->reader->trimTrailingBlanks(array_shift($records));
        $this->header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);

        $missing = array_diff(self::REQUIRED_COLUMNS, $this->header);
        if ($missing) {
            throw new \RuntimeException(
                "This does not look like a Razorpay payments export. Missing column(s): "
                . implode(', ', $missing)
                . "\n\nFile header: " . implode(' | ', $this->header)
            );
        }

        $width = count($this->header);
        $lineNo = 1;

        foreach ($records as $data) {
            $lineNo++;

            if ($this->reader->isBlankRecord($data)) {
                continue;
            }

            $data = array_slice(array_pad($data, $width, ''), 0, $width);
            $row = array_combine($this->header, array_map(fn ($v) => trim((string) $v), $data));
            $row['__line'] = $lineNo;

            $this->rows[] = $row;

            if ($options['limit'] && count($this->rows) >= (int) $options['limit']) {
                break;
            }
        }
    }

    /**
     * Razorpay's API reports amounts in paise; dashboard exports often convert
     * to rupees. Guessing wrong would write 250000 for a 2500 payment, so the
     * decision is made from the data and always surfaced to the operator.
     */
    protected function detectAmountUnit(array $options): void
    {
        if (in_array($options['amount_unit'], ['paise', 'rupees'], true)) {
            $this->amountUnit = $options['amount_unit'];
            return;
        }

        $amounts = [];
        foreach ($this->rows as $row) {
            $raw = str_replace(',', '', (string) $row['amount']);
            if (is_numeric($raw) && (float) $raw > 0) {
                $amounts[] = (float) $raw;
            }
        }

        if (empty($amounts)) {
            $this->amountUnit = 'rupees';
            return;
        }

        // Paise values are whole numbers and, for real fees, large. If every
        // amount is an integer multiple of 100 and the median is at least
        // 10,000, treat the column as paise.
        sort($amounts);
        $median = $amounts[intdiv(count($amounts), 2)];
        $allWholeHundreds = true;
        foreach ($amounts as $amount) {
            if (fmod($amount, 100.0) !== 0.0) {
                $allWholeHundreds = false;
                break;
            }
        }

        $this->amountUnit = ($allWholeHundreds && $median >= 10000) ? 'paise' : 'rupees';
    }

    protected function toRupees(string $raw): float
    {
        $value = (float) str_replace(',', '', $raw);

        return $this->amountUnit === 'paise' ? round($value / 100, 2) : round($value, 2);
    }

    protected function preload(): void
    {
        $this->feePlansById = FeePlan::all()->keyBy('fee_plan_id');
        $this->feePlansByName = FeePlan::all()->keyBy('fee_name');
        $this->onlinePaymodeId = PaymentMode::where('paymodename', 'Online')->value('paymode_id') ?: 1;
    }

    protected function isActionable(array $row, array $options): bool
    {
        $allowed = self::STATUS_SETS[$options['statuses']] ?? self::STATUS_SETS['captured_authorized'];

        return in_array(strtolower($row['status'] ?? ''), $allowed, true);
    }

    /**
     * Razorpay's `notes` column arrives either as JSON or as a loose
     * "key: value" blob depending on how the export was produced.
     */
    protected function parseNotes(string $notes): array
    {
        $notes = trim($notes);
        if ($notes === '') {
            return [];
        }

        $decoded = json_decode($notes, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $parsed = [];
        if (preg_match_all('/["\']?([A-Za-z0-9_]+)["\']?\s*[:=]\s*["\']?([^,"\'}\]]+)["\']?/', $notes, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $parsed[trim($m[1])] = trim($m[2]);
            }
        }

        return $parsed;
    }

    protected function block(array $row, string $reason, string $detail = ''): void
    {
        $this->blocked[] = [
            'payment_id' => $row['id'] ?? '',
            'order_id' => $row['order_id'] ?? '',
            'line' => $row['__line'] ?? '',
            'email' => $row['email'] ?? '',
            'amount' => $row['amount'] ?? '',
            'status' => $row['status'] ?? '',
            'reason' => $reason,
            'detail' => $detail,
        ];
    }

    protected function warn(array $row, string $code, string $detail = ''): void
    {
        $this->warnings[] = [
            'payment_id' => $row['id'] ?? '',
            'line' => $row['__line'] ?? '',
            'code' => $code,
            'detail' => $detail,
        ];
    }

    protected function change(string $paymentId, string $action, string $table, $key, string $column, $from, $to): void
    {
        if ($this->same($from, $to)) {
            return;
        }

        $this->plan[] = [
            'payment_id' => $paymentId,
            'action' => $action,
            'table' => $table,
            'key' => (string) $key,
            'column' => $column,
            'from' => $this->str($from),
            'to' => $this->str($to),
        ];
    }

    protected function same($a, $b): bool
    {
        if ($a === null && ($b === null || $b === '')) return true;
        if ($b === null && ($a === null || $a === '')) return true;
        if (is_numeric($a) && is_numeric($b)) {
            return abs((float) $a - (float) $b) < 0.005;
        }
        return $this->str($a) === $this->str($b);
    }

    protected function str($v): string
    {
        if ($v === null) return '';
        if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d H:i:s');
        return (string) $v;
    }

    /**
     * Resolve a Razorpay row to one of our students:
     * notes.student_id -> an order/payment id we already hold -> email.
     * Ambiguity is never guessed through.
     */
    protected function resolveStudent(array $row, array $notes): ?Student
    {
        // 1. The student_id RegistrationController puts into the order notes.
        $noteId = $notes['student_id'] ?? null;
        if ($noteId !== null && is_numeric($noteId)) {
            $student = Student::withLeads()->withTrashed()->find((int) $noteId);
            if ($student) {
                return $student;
            }
            $this->warn($row, 'NOTES_STUDENT_MISSING', "notes.student_id {$noteId} does not exist; falling back to order id / email");
        }

        // 2. A razorpay id we already store.
        $orderId = $row['order_id'] ?? '';
        $paymentId = $row['id'] ?? '';
        if ($orderId !== '' || $paymentId !== '') {
            $matches = Student::withLeads()->withTrashed()
                ->where(function ($q) use ($orderId, $paymentId) {
                    if ($orderId !== '') {
                        $q->orWhere('razorpay_order_id', $orderId);
                    }
                    if ($paymentId !== '') {
                        $q->orWhere('razorpay_payment_id', $paymentId);
                    }
                })->get();

            if ($matches->count() === 1) {
                return $matches->first();
            }
            if ($matches->count() > 1) {
                $this->block($row, 'AMBIGUOUS_STUDENT', 'Several students carry this order/payment id: ' . $matches->pluck('student_id')->implode(', '));
                return null;
            }
        }

        // 3. Payer email.
        $email = strtolower(trim((string) ($row['email'] ?? '')));
        if ($email === '') {
            $this->block($row, 'UNRESOLVED_STUDENT', 'No notes.student_id, no known order id, and no email');
            return null;
        }

        $byEmail = Student::withLeads()->withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])->get();

        if ($byEmail->count() === 1) {
            return $byEmail->first();
        }
        if ($byEmail->isEmpty()) {
            $this->block($row, 'UNRESOLVED_STUDENT', "No student matches email {$email}");
            return null;
        }

        $this->block($row, 'AMBIGUOUS_STUDENT', sprintf(
            '%d students share email %s (ids: %s) and the payment carries no student_id note',
            $byEmail->count(), $email, $byEmail->pluck('student_id')->implode(', ')
        ));

        return null;
    }

    /**
     * Find the invoice this payment belongs to. Razorpay's own `invoice_id`
     * (inv_...) is deliberately ignored - it refers to Razorpay's invoicing
     * product, not our invoice table.
     */
    protected function resolveInvoice(Student $student, array $row, array $notes): ?Invoice
    {
        // An existing payment log carrying this txn is the strongest signal.
        $byTxn = PaymentLog::where('txnid', $row['id'])->orderBy('payment_id')->first();
        if ($byTxn && $byTxn->invoice) {
            return $byTxn->invoice;
        }

        $planName = $this->planNameFor($notes);

        if ($planName) {
            $byPlan = Invoice::where('student_id', $student->student_id)
                ->whereHas('items', fn ($q) => $q->where('description', $planName))
                ->orderByDesc('invoice_id')->first();
            if ($byPlan) {
                return $byPlan;
            }
        }

        // Fall back to a single unambiguous invoice for the student.
        $invoices = Invoice::where('student_id', $student->student_id)->orderBy('invoice_id')->get();
        if ($invoices->count() === 1) {
            return $invoices->first();
        }

        return null;
    }

    protected function planNameFor(array $notes): ?string
    {
        $planId = $notes['plan_id'] ?? null;
        if ($planId !== null && is_numeric($planId)) {
            $plan = $this->feePlansById->get((int) $planId);
            if ($plan) {
                return $plan->fee_name;
            }
        }

        return null;
    }

    protected function buildPlan(array $options): void
    {
        foreach ($this->rows as $row) {
            $paymentId = $row['id'] ?? '';

            if ($paymentId === '') {
                $this->block($row, 'NO_PAYMENT_ID', 'Row has no Razorpay payment id');
                continue;
            }

            if (!$this->isActionable($row, $options)) {
                continue; // status outside the selected set; silently ignored
            }

            $notes = $this->parseNotes((string) ($row['notes'] ?? ''));

            $student = $this->resolveStudent($row, $notes);
            if (!$student) {
                continue; // already blocked
            }

            if ($student->trashed()) {
                $this->block($row, 'STUDENT_SOFT_DELETED', "student_id {$student->student_id} is soft-deleted");
                continue;
            }

            $amount = $this->toRupees((string) $row['amount']);
            $paidAt = $this->reader->normaliseDate((string) ($row['created_at'] ?? ''), true);
            if ($paidAt === '' || !preg_match('/^\d{4}-\d{2}-\d{2}/', $paidAt)) {
                $this->warn($row, 'UNPARSEABLE_DATE', "created_at '{$row['created_at']}' could not be read; keeping the existing date");
                $paidAt = null;
            }

            $refunded = $this->toRupees((string) ($row['amount_refunded'] ?? '0'));
            if ($refunded > 0) {
                $this->warn($row, 'PARTIALLY_REFUNDED', "amount_refunded is {$refunded}; the invoice keeps the gross amount");
            }

            $invoice = $this->resolveInvoice($student, $row, $notes);
            $planName = $this->planNameFor($notes) ?? $student->subscription;

            if (!$invoice && !$options['create_missing_invoices']) {
                $this->block($row, 'NO_INVOICE', "No invoice for student {$student->student_id}; enable invoice creation to generate one");
                continue;
            }

            if (!$invoice && !$planName) {
                $this->block($row, 'NO_PLAN', "Cannot create an invoice: the payment has no plan_id note and student {$student->student_id} has no subscription");
                continue;
            }

            $invoiceKey = $invoice ? $invoice->invoice_id : 'new';
            $action = $invoice ? 'update' : 'create';

            // --- invoice ---
            if ($options['correct_amounts']) {
                $this->change($paymentId, $action, 'invoice', $invoiceKey, 'total_due', $invoice?->total_due, $amount);
                $this->change($paymentId, $action, 'invoice', $invoiceKey, 'total_before_discount', $invoice?->total_before_discount, $amount);
                if ($paidAt) {
                    $this->change($paymentId, $action, 'invoice', $invoiceKey, 'invoice_date', $invoice?->invoice_date?->format('Y-m-d'), substr($paidAt, 0, 10));
                }
            }

            $targetStatus = strtolower($row['status']) === 'refunded' ? 'VOID' : 'PAID';
            if ($invoice && $invoice->status === 'VOID' && $targetStatus === 'PAID') {
                $this->warn($row, 'INVOICE_VOID', "invoice {$invoice->invoice_id} is VOID; leaving its status alone");
            } else {
                $this->change($paymentId, $action, 'invoice', $invoiceKey, 'status', $invoice?->status, $targetStatus);
            }

            if (!$invoice) {
                $this->change($paymentId, 'create', 'invoice', $invoiceKey, 'student_id', null, $student->student_id);
                $this->change($paymentId, 'create', 'invoice_item', 'new', 'description', null, $planName);
            }

            // --- payment log, keyed by txnid then by invoice ---
            $existingPayment = PaymentLog::where('txnid', $paymentId)->orderBy('payment_id')->first();
            if (!$existingPayment && $invoice) {
                $existingPayment = $invoice->payments->sortBy('payment_id')->first();
            }
            $payKey = $existingPayment ? $existingPayment->payment_id : 'new';
            $payAction = $existingPayment ? 'update' : 'create';

            $this->change($paymentId, $payAction, 'paymentlog', $payKey, 'txnid', $existingPayment?->txnid, $paymentId);
            if ($options['correct_amounts']) {
                $this->change($paymentId, $payAction, 'paymentlog', $payKey, 'amount', $existingPayment?->amount, $amount);
                if ($paidAt) {
                    $this->change($paymentId, $payAction, 'paymentlog', $payKey, 'processdatetime', $existingPayment?->processdatetime?->format('Y-m-d H:i:s'), $paidAt);
                }
            }
            $this->change($paymentId, $payAction, 'paymentlog', $payKey, 'status',
                $existingPayment?->status, self::STATUS_MAP[strtolower($row['status'])] ?? '1');

            // --- student ---
            $this->change($paymentId, 'update', 'student', $student->student_id, 'razorpay_payment_id', $student->razorpay_payment_id, $paymentId);
            if (!empty($row['order_id'])) {
                $this->change($paymentId, 'update', 'student', $student->student_id, 'razorpay_order_id', $student->razorpay_order_id, $row['order_id']);
            }
            if ($planName && $planName !== $student->subscription) {
                $this->change($paymentId, 'update', 'student', $student->student_id, 'subscription', $student->subscription, $planName);
            }
            if ($options['mark_paid'] && $targetStatus === 'PAID') {
                $this->change($paymentId, 'update', 'student', $student->student_id, 'fees_status', $student->fees_status, 'Paid');
            }

            // Razorpay's fee/tax are its processing charges, not our GST, so
            // they are recorded for reconciliation but never written.
            if (!empty($row['fee']) && (float) $row['fee'] > 0) {
                $this->warn($row, 'RAZORPAY_FEE', sprintf(
                    'Razorpay fee %s / tax %s not written (these are gateway charges, not our GST)',
                    $row['fee'], $row['tax'] ?? '0'
                ));
            }
        }
    }

    /**
     * Apply the plan. One transaction for the whole file, so a failure leaves
     * the database exactly as it was.
     */
    protected function apply(array $options): void
    {
        $actionable = array_column($this->plan, 'payment_id');
        $actionable = array_flip($actionable);

        DB::transaction(function () use ($options, $actionable) {
            $applied = 0;

            foreach ($this->rows as $row) {
                $paymentId = $row['id'] ?? '';

                if ($paymentId === '' || !isset($actionable[$paymentId]) || !$this->isActionable($row, $options)) {
                    continue;
                }

                $notes = $this->parseNotes((string) ($row['notes'] ?? ''));
                $student = $this->resolveStudent($row, $notes);
                if (!$student || $student->trashed()) {
                    continue;
                }

                $amount = $this->toRupees((string) $row['amount']);
                $paidAt = $this->reader->normaliseDate((string) ($row['created_at'] ?? ''), true);
                $paidAt = preg_match('/^\d{4}-\d{2}-\d{2}/', $paidAt) ? $paidAt : null;
                $paidDate = $paidAt ? substr($paidAt, 0, 10) : null;

                $invoice = $this->resolveInvoice($student, $row, $notes);
                $planName = $this->planNameFor($notes) ?? $student->subscription;
                $targetStatus = strtolower($row['status']) === 'refunded' ? 'VOID' : 'PAID';

                if (!$invoice) {
                    if (!$options['create_missing_invoices'] || !$planName) {
                        continue;
                    }

                    $plan = $this->feePlansByName->get($planName);

                    $invoiceId = DB::table('invoice')->insertGetId([
                        'invoice_date' => $paidDate ?: Carbon::now()->toDateString(),
                        'due_date' => $paidDate ?: Carbon::now()->toDateString(),
                        'notes' => 'Created from Razorpay payment ' . $paymentId,
                        'total_before_discount' => $amount,
                        'discount_total' => 0,
                        'total_due' => $amount,
                        'status' => $targetStatus,
                        'school_id' => $student->school_id,
                        'student_id' => $student->student_id,
                        'created_at' => $paidDate ?: Carbon::now()->toDateString(),
                        'updated_at' => Carbon::now()->toDateString(),
                    ], 'invoice_id');

                    DB::table('invoice_item')->insert([
                        'invoice_id' => $invoiceId,
                        'fee_plan_id' => $plan?->fee_plan_id,
                        'description' => $planName,
                        'qty' => 1,
                        'unit_amount' => $amount,
                        'line_amount' => $amount,
                    ]);

                    $invoice = Invoice::find($invoiceId);
                } else {
                    $update = [];

                    if ($options['correct_amounts']) {
                        $update['total_due'] = $amount;
                        $update['total_before_discount'] = $amount;
                        if ($paidDate) {
                            $update['invoice_date'] = $paidDate;
                        }
                    }

                    // A deliberately voided invoice is left alone.
                    if (!($invoice->status === 'VOID' && $targetStatus === 'PAID')) {
                        $update['status'] = $targetStatus;
                    }

                    if ($update) {
                        $update['updated_at'] = Carbon::now()->toDateString();
                        DB::table('invoice')->where('invoice_id', $invoice->invoice_id)->update($update);
                    }
                }

                // --- payment log ---
                $existingPaymentId = DB::table('paymentlog')->where('txnid', $paymentId)->min('payment_id')
                    ?: DB::table('paymentlog')->where('invoice_id', $invoice->invoice_id)->min('payment_id');

                $paymentAttrs = [
                    'txnid' => $paymentId,
                    'status' => self::STATUS_MAP[strtolower($row['status'])] ?? '1',
                ];
                if ($options['correct_amounts']) {
                    $paymentAttrs['amount'] = $amount;
                    if ($paidAt) {
                        $paymentAttrs['processdatetime'] = $paidAt;
                    }
                }

                if ($existingPaymentId) {
                    DB::table('paymentlog')->where('payment_id', $existingPaymentId)->update($paymentAttrs);
                } else {
                    DB::table('paymentlog')->insert($paymentAttrs + [
                        'invoice_id' => $invoice->invoice_id,
                        'paymode_id' => $this->onlinePaymodeId,
                        'processdatetime' => $paidAt ?: Carbon::now()->toDateTimeString(),
                        'amount' => $amount,
                        'orgresponse' => json_encode([
                            'source' => 'razorpay_dashboard_export',
                            'method' => $row['method'] ?? null,
                            'order_id' => $row['order_id'] ?? null,
                            'fee' => $row['fee'] ?? null,
                            'tax' => $row['tax'] ?? null,
                        ]),
                    ]);
                }

                // --- student ---
                $studentUpdate = ['razorpay_payment_id' => $paymentId];
                if (!empty($row['order_id'])) {
                    $studentUpdate['razorpay_order_id'] = $row['order_id'];
                }
                if ($planName) {
                    $studentUpdate['subscription'] = $planName;
                }
                if ($options['mark_paid'] && $targetStatus === 'PAID') {
                    $studentUpdate['fees_status'] = 'Paid';
                }

                DB::table('student')->where('student_id', $student->student_id)->update($studentUpdate);

                $applied++;
            }

            Log::info('RazorpayPaymentCsvImporter applied', [
                'payments' => $applied,
                'changes' => count($this->plan),
                'amount_unit' => $this->amountUnit,
            ]);
        });
    }

    public function writeReports(string $runId): string
    {
        $dir = storage_path('app/invoice-csv-import/' . $runId);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $this->writeCsv("{$dir}/plan.csv",
            ['payment_id', 'action', 'table', 'key', 'column', 'from', 'to'], $this->plan);
        $this->writeCsv("{$dir}/blocked.csv",
            ['payment_id', 'order_id', 'line', 'email', 'amount', 'status', 'reason', 'detail'], $this->blocked);
        $this->writeCsv("{$dir}/warnings.csv",
            ['payment_id', 'line', 'code', 'detail'], $this->warnings);

        file_put_contents("{$dir}/summary.json", json_encode([
            'run_id' => $runId,
            'source' => 'razorpay_payments_export',
            'counts' => $this->counts,
            'amount_unit' => $this->amountUnit,
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
