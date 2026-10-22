<?php

namespace Tests\Feature;

use App\Models\FeePlan;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PaymentLog;
use App\Models\Student;
use App\Services\RazorpayPaymentCsvImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RazorpayPaymentCsvImporterTest extends TestCase
{
    use RefreshDatabase;

    /** The exact header of the user's Razorpay payments export. */
    private const HEADER = [
        'id', 'amount', 'currency', 'status', 'order_id', 'invoice_id', 'international', 'method',
        'amount_refunded', 'amount_transferred', 'refund_status', 'captured', 'description',
        'card_id', 'card', 'bank', 'wallet', 'vpa', 'email', 'contact', 'notes', 'fee', 'tax',
        'error_code', 'error_description', 'created_at', 'card_type', 'card_network', 'Auth_code',
        'Payments_ARN', 'Payments_RRN', 'flow', 'source_channel',
    ];

    protected string $tmpDir;
    protected $gujaratId;
    protected $indiaId;
    protected $talukoId;
    protected FeePlan $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);

        $this->gujaratId = DB::table('ms_state')->where('sname', 'Gujarat')->value('state_id');
        $this->indiaId = DB::table('ms_country')->where('cname', 'India')->value('country_id');
        $distId = DB::table('ms_dist')->insertGetId(['state_id' => $this->gujaratId, 'dname' => 'Kheda', 'created_at' => now()]);
        $this->talukoId = DB::table('ms_taluko')->insertGetId(['dist_id' => $distId, 'tname' => 'Matar', 'created_at' => now()]);

        $this->plan = FeePlan::create([
            'fee_name' => 'Semester-01 (July 2026 to Dec 2026)',
            'amount' => 2500, 'valid_from' => now()->subYear(), 'valid_to' => now()->addYear(),
        ]);

        $this->tmpDir = storage_path('app/testing-razorpay');
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
            'fname' => 'TEST', 'mname' => 'X', 'lname' => 'STUDENT',
            'email' => "rzp{$n}@example.com", 'gender' => 'M', 'contact_no' => '9999999999',
            'studenttype_id' => 1, 'eduboard_id' => 1, 'std_id' => 1,
            'takula_id' => $this->talukoId, 'country_id' => $this->indiaId, 'state_id' => $this->gujaratId,
        ], $attrs));
    }

    /** Build a Razorpay row, defaults matching a normal captured payment. */
    private function row(array $overrides = []): array
    {
        $row = array_fill_keys(self::HEADER, '');
        $row = array_merge($row, [
            'id' => 'pay_TEST0000000001',
            'amount' => '2500',
            'currency' => 'INR',
            'status' => 'captured',
            'order_id' => 'order_TEST000000001',
            'captured' => 'TRUE',
            'method' => 'upi',
            'email' => 'rzp1@example.com',
            'contact' => '9999999999',
            'notes' => '{}',
            'fee' => '59',
            'tax' => '9',
            'created_at' => '2026-08-22 10:15:00',
        ], $overrides);

        return array_values($row);
    }

    private function writeCsv(array $rows, string $delimiter = ','): string
    {
        $path = $this->tmpDir . '/payments.csv';
        $handle = fopen($path, 'w');
        fputcsv($handle, self::HEADER, $delimiter);
        foreach ($rows as $row) {
            fputcsv($handle, $row, $delimiter);
        }
        fclose($handle);

        return $path;
    }

    private function importer(): RazorpayPaymentCsvImporter
    {
        return app(RazorpayPaymentCsvImporter::class);
    }

    public function test_header_is_recognised_as_a_razorpay_export()
    {
        $this->assertTrue(RazorpayPaymentCsvImporter::matchesHeader(self::HEADER));
        $this->assertFalse(RazorpayPaymentCsvImporter::matchesHeader(['No.', 'First Name', 'Email']));
    }

    public function test_matches_student_via_notes_student_id_and_creates_invoice_and_payment_log()
    {
        $student = $this->student(['subscription' => null]);

        $path = $this->writeCsv([$this->row([
            'email' => 'someone-else@example.com', // notes must win over email
            'notes' => json_encode(['student_id' => (string) $student->student_id, 'plan_id' => (string) $this->plan->fee_plan_id]),
        ])]);

        $counts = $this->importer()->run($path, ['dry_run' => false]);

        $this->assertSame(0, $counts['blocked']);

        $invoice = Invoice::where('student_id', $student->student_id)->first();
        $this->assertNotNull($invoice, 'An invoice should have been created');
        $this->assertEquals(2500, $invoice->total_due);
        $this->assertSame('PAID', $invoice->status);
        $this->assertSame('2026-08-22', $invoice->invoice_date->format('Y-m-d'));

        $this->assertSame('Semester-01 (July 2026 to Dec 2026)',
            DB::table('invoice_item')->where('invoice_id', $invoice->invoice_id)->value('description'));

        $payment = PaymentLog::where('invoice_id', $invoice->invoice_id)->first();
        $this->assertSame('pay_TEST0000000001', $payment->txnid);
        $this->assertSame('1', $payment->status);

        $student->refresh();
        $this->assertSame('pay_TEST0000000001', $student->razorpay_payment_id);
        $this->assertSame('order_TEST000000001', $student->razorpay_order_id);
        $this->assertSame('Paid', $student->fees_status);
    }

    public function test_paise_amounts_are_detected_and_converted()
    {
        $student = $this->student();

        // Razorpay's API unit: 2500 rupees == 250000 paise.
        $path = $this->writeCsv([
            $this->row(['id' => 'pay_A', 'amount' => '250000', 'notes' => json_encode(['student_id' => (string) $student->student_id, 'plan_id' => (string) $this->plan->fee_plan_id])]),
            $this->row(['id' => 'pay_B', 'amount' => '450000', 'order_id' => 'order_B', 'notes' => json_encode(['student_id' => (string) $student->student_id, 'plan_id' => (string) $this->plan->fee_plan_id])]),
        ]);

        $importer = $this->importer();
        $counts = $importer->run($path, ['dry_run' => true]);

        $this->assertSame('paise', $counts['amount_unit']);
        $this->assertSame('paise', $importer->getAmountUnit());
    }

    public function test_rupee_amounts_are_left_alone()
    {
        $student = $this->student();
        $path = $this->writeCsv([$this->row([
            'amount' => '2500.00',
            'notes' => json_encode(['student_id' => (string) $student->student_id]),
        ])]);

        $this->assertSame('rupees', $this->importer()->run($path, ['dry_run' => true])['amount_unit']);
    }

    public function test_amount_unit_can_be_forced()
    {
        $student = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);
        $path = $this->writeCsv([$this->row([
            'amount' => '250000',
            'notes' => json_encode(['student_id' => (string) $student->student_id]),
        ])]);

        $this->importer()->run($path, ['dry_run' => false, 'amount_unit' => 'rupees']);

        $this->assertEquals(250000, Invoice::where('student_id', $student->student_id)->value('total_due'));
    }

    public function test_failed_payments_are_ignored_by_default()
    {
        $student = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);
        $path = $this->writeCsv([$this->row([
            'status' => 'failed', 'captured' => 'FALSE', 'error_code' => 'BAD_REQUEST_ERROR',
            'notes' => json_encode(['student_id' => (string) $student->student_id]),
        ])]);

        $counts = $this->importer()->run($path, ['dry_run' => false]);

        $this->assertSame(0, $counts['actionable_rows']);
        $this->assertSame(0, Invoice::where('student_id', $student->student_id)->count());
    }

    public function test_razorpay_fee_and_tax_are_never_written_as_gst()
    {
        $student = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);
        $path = $this->writeCsv([$this->row([
            'fee' => '59', 'tax' => '9',
            'notes' => json_encode(['student_id' => (string) $student->student_id]),
        ])]);

        $importer = $this->importer();
        $importer->run($path, ['dry_run' => false]);

        // GST is derived from the student's state, so the exported tax must be
        // the Gujarat 9+9 split of 2500 - never Razorpay's 59/9 gateway charges.
        $invoice = Invoice::where('student_id', $student->student_id)
            ->with(\App\Services\InvoiceCsvExportService::eagerLoads())->first();
        $row = app(\App\Services\InvoiceCsvExportService::class)->rowFor($invoice, 1);

        $this->assertSame(['2118.64', '190.68', '190.68', '0.00', '2500.00'], array_slice($row, 11, 5));
        $this->assertContains('RAZORPAY_FEE', array_column($importer->getWarnings(), 'code'));
    }

    public function test_razorpay_invoice_id_is_not_used_for_matching()
    {
        $student = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);
        $other = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);
        $decoy = Invoice::create([
            'invoice_date' => '2026-01-01', 'total_before_discount' => 1, 'discount_total' => 0,
            'total_due' => 1, 'status' => 'ISSUED', 'student_id' => $other->student_id,
        ]);

        // Razorpay's invoice_id looks like inv_..., and here deliberately
        // collides with our numeric invoice id.
        $path = $this->writeCsv([$this->row([
            'invoice_id' => (string) $decoy->invoice_id,
            'notes' => json_encode(['student_id' => (string) $student->student_id]),
        ])]);

        $this->importer()->run($path, ['dry_run' => false]);

        $decoy->refresh();
        $this->assertEquals(1, $decoy->total_due, "The other student's invoice must not be touched");
        $this->assertEquals(1, Invoice::where('student_id', $student->student_id)->count());
    }

    public function test_falls_back_to_email_when_notes_are_empty()
    {
        // student.email is UNIQUE, so an email match is unambiguous by
        // construction; the ambiguity guard only fires for order/payment ids.
        $student = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);

        $path = $this->writeCsv([$this->row(['email' => $student->email, 'notes' => ''])]);

        $counts = $this->importer()->run($path, ['dry_run' => false]);

        $this->assertSame(0, $counts['blocked']);
        $this->assertSame($student->student_id, Invoice::first()->student_id);
    }

    public function test_unknown_email_is_blocked_not_guessed()
    {
        $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);

        $path = $this->writeCsv([$this->row(['email' => 'nobody@nowhere.test', 'notes' => ''])]);

        $importer = $this->importer();
        $counts = $importer->run($path, ['dry_run' => true]);

        $this->assertSame(1, $counts['blocked']);
        $this->assertSame('UNRESOLVED_STUDENT', $importer->getBlocked()[0]['reason']);
        $this->assertSame(0, Invoice::count());
    }

    public function test_existing_invoice_is_updated_rather_than_duplicated()
    {
        $student = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);
        $invoice = Invoice::create([
            'invoice_date' => '2020-01-01', 'total_before_discount' => 111, 'discount_total' => 0,
            'total_due' => 111, 'status' => 'ISSUED', 'student_id' => $student->student_id,
        ]);
        InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id, 'description' => 'Semester-01 (July 2026 to Dec 2026)',
            'qty' => 1, 'unit_amount' => 111, 'line_amount' => 111,
        ]);

        $path = $this->writeCsv([$this->row([
            'notes' => json_encode(['student_id' => (string) $student->student_id, 'plan_id' => (string) $this->plan->fee_plan_id]),
        ])]);

        $this->importer()->run($path, ['dry_run' => false]);

        $this->assertSame(1, Invoice::where('student_id', $student->student_id)->count());
        $invoice->refresh();
        $this->assertEquals(2500, $invoice->total_due);
        $this->assertSame('PAID', $invoice->status);
    }

    public function test_dry_run_writes_nothing_and_run_is_idempotent()
    {
        $student = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);
        $path = $this->writeCsv([$this->row([
            'notes' => json_encode(['student_id' => (string) $student->student_id, 'plan_id' => (string) $this->plan->fee_plan_id]),
        ])]);

        $dry = $this->importer()->run($path, ['dry_run' => true]);
        $this->assertGreaterThan(0, $dry['planned_changes']);
        $this->assertSame(0, Invoice::count());

        $this->importer()->run($path, ['dry_run' => false]);
        $this->assertSame(0, $this->importer()->run($path, ['dry_run' => true])['planned_changes'], 'Import is not idempotent');
    }

    public function test_tab_separated_file_is_read()
    {
        $student = $this->student(['subscription' => 'Semester-01 (July 2026 to Dec 2026)']);
        $path = $this->writeCsv([$this->row([
            'notes' => json_encode(['student_id' => (string) $student->student_id]),
        ])], "\t");

        $counts = $this->importer()->run($path, ['dry_run' => true]);

        $this->assertSame(1, $counts['file_rows']);
        $this->assertSame(0, $counts['blocked']);
    }

    public function test_non_razorpay_file_is_rejected_with_the_missing_columns()
    {
        $path = $this->tmpDir . '/other.csv';
        file_put_contents($path, "No.,First Name,Email\n1,A,a@b.c\n");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Missing column\(s\).*(id|amount|status|order_id)/s');
        $this->importer()->run($path, ['dry_run' => true]);
    }
}
