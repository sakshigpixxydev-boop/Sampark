<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentLog;
use App\Models\SemesterBatch;
use App\Models\Student;
use App\Services\InvoiceCsvExportService;
use App\Services\InvoicePaymentCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InvoicePaymentCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceCsvExportService $exporter;
    protected $gujaratId;
    protected $indiaId;
    protected $talukoId;
    protected $batch;
    protected string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);

        $this->gujaratId = DB::table('ms_state')->where('sname', 'Gujarat')->value('state_id');
        $this->indiaId = DB::table('ms_country')->where('cname', 'India')->value('country_id');
        $distId = DB::table('ms_dist')->insertGetId(['state_id' => $this->gujaratId, 'dname' => 'Kheda', 'created_at' => now()]);
        $this->talukoId = DB::table('ms_taluko')->insertGetId(['dist_id' => $distId, 'tname' => 'Matar', 'created_at' => now()]);

        $this->batch = SemesterBatch::create([
            'display_name' => 'Semester-2 (July 2026)',
            'start_date' => now(), 'end_date' => now()->addMonths(6), 'capacity' => 100,
        ]);

        $this->exporter = app(InvoiceCsvExportService::class);
        $this->tmpDir = storage_path('app/testing-csv');
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0775, true);
        }
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tmpDir . '/*.*') ?: []);
        parent::tearDown();
    }

    private function student(array $attrs = []): Student
    {
        static $n = 0;
        $n++;

        return Student::create(array_merge([
            'fname' => 'AKSHAR', 'mname' => 'NAVINCHANDRA', 'lname' => 'THAKKAR',
            'email' => "student{$n}@example.com", 'gender' => 'M', 'contact_no' => '9999999999',
            'studenttype_id' => 1, 'eduboard_id' => 1, 'std_id' => 1,
            'takula_id' => $this->talukoId,
            'country_id' => $this->indiaId, 'state_id' => $this->gujaratId,
        ], $attrs));
    }

    private function invoice(Student $student, array $attrs = []): Invoice
    {
        $invoice = Invoice::create(array_merge([
            'invoice_date' => '2026-08-22',
            'total_before_discount' => 2500, 'discount_total' => 0, 'total_due' => 2500,
            'status' => 'PAID', 'student_id' => $student->student_id,
        ], $attrs));

        InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'description' => 'Semester-02 (July 2026 to Dec 2026)',
            'qty' => 1, 'unit_amount' => 2500, 'line_amount' => 2500,
        ]);

        return $invoice->fresh();
    }

    /** Write a CSV with the export's exact header and the given data rows. */
    private function writeCsv(array $rows, string $name = 'input.csv'): string
    {
        $path = $this->tmpDir . '/' . $name;
        $handle = fopen($path, 'w');
        fputcsv($handle, InvoiceCsvExportService::COLUMNS);
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        return $path;
    }

    private function exportAll(): string
    {
        $invoices = $this->exporter->applyOrdering(
            Invoice::with(InvoiceCsvExportService::eagerLoads())
        )->get();

        $path = $this->tmpDir . '/export.csv';
        $handle = fopen($path, 'w');
        $this->exporter->writeTo($handle, $invoices);
        fclose($handle);

        return $path;
    }

    private function importer(): InvoicePaymentCsvImporter
    {
        return app(InvoicePaymentCsvImporter::class);
    }

    public function test_round_trip_export_mutate_import_reproduces_the_original_bytes()
    {
        $student = $this->student(['razorpay_order_id' => 'order_TSoVyCmnUkJQFb']);
        $invoice = $this->invoice($student);
        $student->semesterBatches()->attach($this->batch->sembatches_id, ['status' => 0]);
        PaymentLog::create([
            'invoice_id' => $invoice->invoice_id, 'paymode_id' => 1,
            'processdatetime' => '2026-08-22 00:00:00', 'amount' => 2500,
            'status' => '1', 'txnid' => 'pay_TSoXM1KgZS4UvG',
        ]);

        $original = file_get_contents($this->exportAll());

        // Corrupt the payment data the way a bad migration would.
        DB::table('invoice')->where('invoice_id', $invoice->invoice_id)->update([
            'total_due' => 999, 'total_before_discount' => 999, 'invoice_date' => '2020-01-01',
        ]);
        DB::table('paymentlog')->where('invoice_id', $invoice->invoice_id)->update(['txnid' => 'WRONG']);
        DB::table('student')->where('student_id', $student->student_id)->update(['razorpay_order_id' => null]);

        $this->assertNotSame($original, file_get_contents($this->exportAll()));

        // Re-import the good snapshot.
        $path = $this->tmpDir . '/original.csv';
        file_put_contents($path, $original);
        $counts = $this->importer()->run($path, ['dry_run' => false]);

        $this->assertSame(0, $counts['blocked']);
        $this->assertSame($original, file_get_contents($this->exportAll()));
    }

    public function test_dry_run_writes_nothing()
    {
        $student = $this->student();
        $invoice = $this->invoice($student, ['total_due' => 111, 'total_before_discount' => 111]);

        $path = $this->writeCsv([$this->rowFor($invoice, $student, ['Total Amount' => '2500.00'])]);

        $counts = $this->importer()->run($path, ['dry_run' => true]);

        $this->assertGreaterThan(0, $counts['planned_changes']);
        $this->assertEquals(111, DB::table('invoice')->where('invoice_id', $invoice->invoice_id)->value('total_due'));
    }

    public function test_second_run_plans_no_changes()
    {
        $student = $this->student(['razorpay_order_id' => 'order_X']);
        $invoice = $this->invoice($student);
        $student->semesterBatches()->attach($this->batch->sembatches_id, ['status' => 0]);

        $path = $this->writeCsv([$this->rowFor($invoice, $student)]);

        $this->importer()->run($path, ['dry_run' => false]);
        $second = $this->importer()->run($path, ['dry_run' => true]);

        $this->assertSame(0, $second['planned_changes'], 'Import is not idempotent');
    }

    public function test_two_invoices_sharing_one_payment_id_each_keep_their_own_payment_row()
    {
        $student = $this->student(['razorpay_order_id' => 'order_TBHlmG3YZTN1qb']);
        $a = $this->invoice($student);
        $b = $this->invoice($student);
        $student->semesterBatches()->attach($this->batch->sembatches_id, ['status' => 0]);

        $path = $this->writeCsv([
            $this->rowFor($a, $student, ['Razorpay Payment ID' => 'pay_TBHmAKhMoptHT4']),
            $this->rowFor($b, $student, ['Razorpay Payment ID' => 'pay_TBHmAKhMoptHT4']),
        ]);

        $counts = $this->importer()->run($path, ['dry_run' => false]);

        $this->assertSame(0, $counts['blocked']);
        $this->assertSame(1, DB::table('paymentlog')->where('invoice_id', $a->invoice_id)->count());
        $this->assertSame(1, DB::table('paymentlog')->where('invoice_id', $b->invoice_id)->count());
        $this->assertSame(2, DB::table('paymentlog')->where('txnid', 'pay_TBHmAKhMoptHT4')->count());
    }

    public function test_na_payment_id_and_empty_order_id_round_trip()
    {
        $student = $this->student();
        $invoice = $this->invoice($student);
        $student->semesterBatches()->attach($this->batch->sembatches_id, ['status' => 0]);

        $path = $this->writeCsv([$this->rowFor($invoice, $student, [
            'Razorpay Order ID' => '',
            'Razorpay Payment ID' => 'N/A',
        ])]);

        $this->importer()->run($path, ['dry_run' => false]);

        // Assert on the rendered CSV, not the raw value: a NULL order id is
        // written by fputcsv as an empty field, which is what the snapshot has.
        $handle = fopen($this->exportAll(), 'r');
        fgetcsv($handle);
        $line = fgetcsv($handle);
        fclose($handle);

        $this->assertSame('', $line[19], 'Empty order id should stay empty');
        $this->assertSame('N/A', $line[20]);
    }

    public function test_conflicting_order_ids_for_one_student_are_blocked()
    {
        $student = $this->student();
        $a = $this->invoice($student);
        $b = $this->invoice($student);

        $path = $this->writeCsv([
            $this->rowFor($a, $student, ['Razorpay Order ID' => 'order_AAA']),
            $this->rowFor($b, $student, ['Razorpay Order ID' => 'order_BBB']),
        ]);

        $run = $this->importer();
        $counts = $run->run($path, ['dry_run' => true]);

        $this->assertSame(1, $counts['blocked']);
        $this->assertSame('ORDER_ID_CONFLICT', $run->getBlocked()[0]['reason']);
    }

    public function test_missing_invoice_is_blocked_unless_create_missing_is_set()
    {
        $student = $this->student();
        $invoice = $this->invoice($student);
        $ghost = InvoiceCsvExportService::invoiceNumberFor($invoice->invoice_id + 500);

        $row = $this->rowFor($invoice, $student);
        $row[18] = $ghost;
        $path = $this->writeCsv([$row]);

        $blockedRun = $this->importer();
        $counts = $blockedRun->run($path, ['dry_run' => true, 'create_missing' => false]);
        $this->assertSame(1, $counts['blocked']);
        $this->assertSame('MISSING_INVOICE', $blockedRun->getBlocked()[0]['reason']);

        $this->importer()->run($path, ['dry_run' => false, 'create_missing' => true]);

        $created = Invoice::find($invoice->invoice_id + 500);
        $this->assertNotNull($created, 'Invoice should be created with its explicit id');
        $this->assertSame(
            $ghost,
            InvoiceCsvExportService::invoiceNumberFor($created->invoice_id),
            'Created invoice must reproduce the CSV INV number'
        );
    }

    public function test_unknown_plan_name_still_writes_the_description()
    {
        $student = $this->student();
        $invoice = $this->invoice($student);

        $path = $this->writeCsv([$this->rowFor($invoice, $student, ['Subscription Plan' => 'test-demo'])]);

        $run = $this->importer();
        $run->run($path, ['dry_run' => false]);

        $this->assertSame('test-demo', DB::table('invoice_item')->where('invoice_id', $invoice->invoice_id)->value('description'));
        $this->assertContains('PLAN_UNRESOLVED', array_column($run->getWarnings(), 'code'));
    }

    public function test_header_mismatch_is_a_hard_abort()
    {
        $path = $this->tmpDir . '/bad.csv';
        file_put_contents($path, "Wrong,Header\n1,2\n");

        $this->expectException(\RuntimeException::class);
        $this->importer()->run($path, ['dry_run' => true]);
    }

    /**
     * GST is derived from the student's state at export time, so the same
     * gross amount taxes differently by state - and a missing country no
     * longer suppresses tax the way it did in the 2026-08-24 snapshot.
     */
    public function test_export_derives_tax_from_the_student_state()
    {
        $maharashtra = DB::table('ms_state')->where('sname', 'Maharashtra')->value('state_id');
        $usa = DB::table('ms_country')->where('cname', 'United States')->value('country_id');

        // Gujarat -> intra-state 9% + 9%.
        $gujarat = $this->student();
        $rowGujarat = $this->exporter->rowFor($this->invoice($gujarat)->load(InvoiceCsvExportService::eagerLoads()), 1);
        $this->assertSame(['2118.64', '190.68', '190.68', '0.00', '2500.00'], array_slice($rowGujarat, 11, 5));

        // Maharashtra -> inter-state 18% IGST.
        $other = $this->student(['state_id' => $maharashtra]);
        $rowOther = $this->exporter->rowFor($this->invoice($other)->load(InvoiceCsvExportService::eagerLoads()), 1);
        $this->assertSame(['2118.64', '0.00', '0.00', '381.36', '2500.00'], array_slice($rowOther, 11, 5));

        // Gujarat with NO country: previously exported 2500.00 / 0 / 0 / 0
        // (row 1 of the snapshot). Now taxed on the state like any other
        // Gujarat student.
        $noCountry = $this->student(['country_id' => null]);
        $rowNoCountry = $this->exporter->rowFor($this->invoice($noCountry)->load(InvoiceCsvExportService::eagerLoads()), 1);
        $this->assertSame(['2118.64', '190.68', '190.68', '0.00', '2500.00'], array_slice($rowNoCountry, 11, 5));

        // Explicitly foreign -> export of service, no GST.
        $foreign = $this->student(['country_id' => $usa, 'state_id' => null]);
        $rowForeign = $this->exporter->rowFor($this->invoice($foreign)->load(InvoiceCsvExportService::eagerLoads()), 1);
        $this->assertSame(['2500.00', '0.00', '0.00', '0.00', '2500.00'], array_slice($rowForeign, 11, 5));
    }

    /**
     * The importer cannot write the tax cells, so a CSV whose tax disagrees
     * with what the student's state produces is reported, not silently
     * "fixed" by writing a value the export would ignore.
     */
    public function test_tax_mismatch_is_reported_not_written()
    {
        $student = $this->student(); // Gujarat -> exports 2118.64 / 190.68 / 190.68
        $invoice = $this->invoice($student);
        $student->semesterBatches()->attach($this->batch->sembatches_id, ['status' => 0]);

        // The snapshot's old zero-GST values for this row.
        $path = $this->writeCsv([$this->rowFor($invoice, $student, [
            'Taxable Amount' => '2500.00', 'SGST' => '0.00', 'CGST' => '0.00', 'IGST' => '0.00',
        ])]);

        $run = $this->importer();
        $run->run($path, ['dry_run' => true]);

        $taxDiffs = array_filter($run->getReadonlyDiffs(), fn ($d) => in_array($d['column'], ['Taxable Amount', 'SGST', 'CGST']));
        $this->assertNotEmpty($taxDiffs, 'A tax disagreement should surface as a read-only diff');

        $planColumns = array_column($run->getPlan(), 'column');
        $this->assertNotContains('taxable_amount', $planColumns);
        $this->assertNotContains('sgst', $planColumns);
        $this->assertNotContains('igst', $planColumns);
    }

    /**
     * Ties down the ordering the snapshot was produced with: invoice_date
     * descending, then invoice_id ascending within each date.
     */
    public function test_export_orders_by_date_desc_then_invoice_id_asc()
    {
        $student = $this->student();
        $a = $this->invoice($student, ['invoice_date' => '2026-08-11']);
        $b = $this->invoice($student, ['invoice_date' => '2026-08-11']);
        $c = $this->invoice($student, ['invoice_date' => '2026-08-22']);

        $ids = $this->exporter->applyOrdering(Invoice::query())->pluck('invoice_id')->all();

        $this->assertSame([$c->invoice_id, $a->invoice_id, $b->invoice_id], $ids);
    }

    public function test_xlsx_upload_is_imported_like_a_csv()
    {
        $student = $this->student(['razorpay_order_id' => 'order_X']);
        $invoice = $this->invoice($student, ['total_due' => 111, 'total_before_discount' => 111]);
        $student->semesterBatches()->attach($this->batch->sembatches_id, ['status' => 0]);

        // Deliberately typed the way Excel hands values back: an unformatted
        // number, a real DateTime, and a numeric mobile.
        $row = $this->rowFor($invoice, $student, [
            'Total Amount' => 2500,
            'Taxable Amount' => 2500,
            'Payment Date' => new \DateTime('2026-08-22'),
            'Mobile' => 9999999999,
        ]);

        $path = $this->writeXlsx([$row]);
        $counts = $this->importer()->run($path, ['dry_run' => false]);

        $this->assertSame(0, $counts['blocked']);
        $this->assertEquals(2500, DB::table('invoice')->where('invoice_id', $invoice->invoice_id)->value('total_due'));
        $this->assertSame('2026-08-22', (string) DB::table('invoice')->where('invoice_id', $invoice->invoice_id)->value('invoice_date'));

        // And it is idempotent from the spreadsheet source too.
        $this->assertSame(0, $this->importer()->run($path, ['dry_run' => true])['planned_changes']);
    }

    public function test_xlsx_with_wrong_columns_reports_which_ones()
    {
        $path = $this->tmpDir . '/wrong.xlsx';
        $sheet = (new \PhpOffice\PhpSpreadsheet\Spreadsheet())->getActiveSheet();
        $sheet->fromArray(['entity_id', 'order_id', 'method', 'amount'], null, 'A1');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sheet->getParent()))->save($path);

        try {
            $this->importer()->run($path, ['dry_run' => true]);
            $this->fail('Expected a header mismatch');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('does not look like an invoice export', $e->getMessage());
            $this->assertStringContainsString('entity_id', $e->getMessage());
            $this->assertStringContainsString('Missing expected column(s)', $e->getMessage());
        }
    }

    public function test_trailing_blank_columns_are_tolerated()
    {
        $student = $this->student();
        $invoice = $this->invoice($student);

        // Some CSV writers pad every row with empty trailing fields.
        $path = $this->tmpDir . '/padded.csv';
        $handle = fopen($path, 'w');
        fputcsv($handle, array_merge(InvoiceCsvExportService::COLUMNS, ['', '']));
        fputcsv($handle, $this->rowFor($invoice, $student));
        fclose($handle);

        $counts = $this->importer()->run($path, ['dry_run' => true]);
        $this->assertSame(1, $counts['csv_rows']);
    }

    /** Write the given rows to a real .xlsx file. */
    private function writeXlsx(array $rows, string $name = 'input.xlsx'): string
    {
        $path = $this->tmpDir . '/' . $name;
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(InvoiceCsvExportService::COLUMNS, null, 'A1');

        foreach ($rows as $i => $row) {
            foreach (array_values($row) as $col => $value) {
                $cell = $sheet->getCell([$col + 1, $i + 2]);
                if ($value instanceof \DateTimeInterface) {
                    $cell->setValue(\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($value));
                } else {
                    $cell->setValue($value);
                }
            }
        }

        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }

    /** Build a CSV row from the current DB state, with optional overrides. */
    private function rowFor(Invoice $invoice, Student $student, array $overrides = []): array
    {
        $row = [
            1, $student->fname, $student->mname, $student->lname, $student->email,
            $student->contact_no, 'India', 'Gujarat', 'Kheda', 'Matar',
            'Semester-02 (July 2026 to Dec 2026)',
            '2500.00', '0.00', '0.00', '0.00', '2500.00',
            $student->fname . ' ' . $student->lname,
            '2026-08-22',
            InvoiceCsvExportService::invoiceNumberFor($invoice->invoice_id),
            $student->razorpay_order_id ?? '',
            'pay_TSoXM1KgZS4UvG',
            'Semester-2 (July 2026)',
            $student->created_at?->format('Y-m-d') ?? '',
        ];

        foreach ($overrides as $column => $value) {
            $row[array_search($column, InvoiceCsvExportService::COLUMNS, true)] = $value;
        }

        return $row;
    }
}
