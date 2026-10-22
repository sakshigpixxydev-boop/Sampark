<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Collection;

/**
 * The invoice CSV export: one column list, one row builder.
 *
 * Both the web export (InvoiceController::export) and the artisan exporter go
 * through here, which is what makes "re-export the CSV and compare it to the
 * input file" a meaningful verification step.
 */
class InvoiceCsvExportService
{
    public const COLUMNS = [
        'No.',
        'First Name',
        'Middle Name',
        'Last Name',
        'Email',
        'Mobile',
        'Country',
        'State',
        'District',
        'Taluka',
        'Subscription Plan',
        'Taxable Amount',
        'SGST',
        'CGST',
        'IGST',
        'Total Amount',
        'Invoice Name',
        'Payment Date',
        'Invoice Number',
        'Razorpay Order ID',
        'Razorpay Payment ID',
        'Batch Name',
        'Registration Date',
    ];

    /**
     * Columns the CSV importer is allowed to write.
     *
     * The four tax columns are NOT writable: they are derived from the
     * student's state by InvoiceTaxService at export time, so the only way to
     * change them is to correct the student's state. A mismatch is reported
     * instead of written.
     */
    public const WRITABLE_COLUMNS = [
        'Subscription Plan',
        'Total Amount',
        'Payment Date',
        'Razorpay Order ID',
        'Razorpay Payment ID',
        'Batch Name',
    ];

    public function __construct(protected InvoiceTaxService $tax)
    {
    }

    /**
     * Eager loads for the export. The two ordered closures matter: both
     * payments->first() and semesterBatches->first() are otherwise
     * query-plan dependent, and every 1-year plan links two sembatches.
     */
    public static function eagerLoads(): array
    {
        return [
            'student.state',
            'student.district',
            'student.taluka',
            'student.country',
            'student.semesterBatches' => fn ($q) => $q->orderBy('student_sembatches.std_batch_id'),
            'school',
            'payments' => fn ($q) => $q->orderBy('payment_id'),
            'payments.paymentMode',
            'items',
        ];
    }

    /**
     * Apply the export's canonical ordering. invoice_date alone leaves most
     * rows tied, and MySQL's filesort is not stable, so the invoice_id
     * tiebreaker is what makes the output reproducible.
     */
    public function applyOrdering($query)
    {
        return $query->orderBy('invoice_date', 'desc')->orderBy('invoice_id', 'asc');
    }

    /**
     * Build the 23 cells for one invoice.
     */
    public function rowFor(Invoice $invoice, int $sequenceNumber): array
    {
        $student = $invoice->student;
        $tax = $this->tax->resolve($invoice);

        $payment = $invoice->payments->first();
        $paymentDate = $invoice->invoice_date
            ? $invoice->invoice_date->format('Y-m-d')
            : ($payment && $payment->processdatetime ? $payment->processdatetime->format('Y-m-d') : '');

        $invoiceNo = self::invoiceNumberFor($invoice->invoice_id);
        $razorpayPaymentId = $payment ? $payment->txnid : ($student->razorpay_payment_id ?? 'N/A');
        $batch = $student ? $student->semesterBatches->first() : null;
        $batchName = $batch ? ($batch->display_name ?: $batch->edmingle_batch_name) : 'N/A';
        $regDate = $student && $student->created_at ? $student->created_at->format('Y-m-d') : '';

        return [
            $sequenceNumber,
            $student ? $student->fname : '',
            $student ? $student->mname : '',
            $student ? $student->lname : '',
            $student ? $student->email : '',
            $student ? $student->contact_no : '',
            $student && $student->country ? $student->country->cname : 'India',
            $student && $student->state ? $student->state->sname : '',
            $student && $student->district ? $student->district->dname : '',
            $student && $student->taluka ? $student->taluka->tname : '',
            $invoice->items->first() ? $invoice->items->first()->description : ($student ? $student->subscription : 'N/A'),
            number_format($tax['taxable'], 2, '.', ''),
            number_format($tax['sgst'], 2, '.', ''),
            number_format($tax['cgst'], 2, '.', ''),
            number_format($tax['igst'], 2, '.', ''),
            number_format($tax['total'], 2, '.', ''),
            $student ? $student->fname . ' ' . $student->lname : '',
            $paymentDate,
            $invoiceNo,
            $student ? $student->razorpay_order_id : 'N/A',
            $razorpayPaymentId ?: 'N/A',
            $batchName,
            $regDate,
        ];
    }

    /**
     * INV-000601 for invoice_id 601. The invoice table has no invoice number
     * column; the export derives it, so the importer must invert this.
     */
    public static function invoiceNumberFor($invoiceId): string
    {
        return 'INV-' . str_pad((string) $invoiceId, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Invert invoiceNumberFor(). Returns null when the string is not a well
     * formed invoice number that round-trips back to itself.
     */
    public static function invoiceIdFrom(string $invoiceNumber): ?int
    {
        $invoiceNumber = trim($invoiceNumber);

        if (!preg_match('/^INV-(\d{6})$/', $invoiceNumber, $m)) {
            return null;
        }

        $invoiceId = (int) ltrim($m[1], '0');

        if ($invoiceId <= 0 || self::invoiceNumberFor($invoiceId) !== $invoiceNumber) {
            return null;
        }

        return $invoiceId;
    }

    /**
     * Streaming callback for the HTTP download.
     */
    public function streamCallback(Collection $invoices): \Closure
    {
        return function () use ($invoices) {
            $file = fopen('php://output', 'w');
            $this->writeTo($file, $invoices);
            fclose($file);
        };
    }

    /**
     * Write the header plus every row to an open stream.
     */
    public function writeTo($handle, Collection $invoices): void
    {
        fputcsv($handle, self::COLUMNS);

        $sequenceNumber = 1;

        foreach ($invoices as $invoice) {
            fputcsv($handle, $this->rowFor($invoice, $sequenceNumber++));
        }
    }
}
