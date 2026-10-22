<?php

namespace Tests\Unit;

use App\Models\Country;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\State;
use App\Models\Student;
use App\Services\InvoiceTaxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTaxServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceTaxService $tax;
    protected $gujaratId;
    protected $maharashtraId;
    protected $indiaId;
    protected $talukoId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        $this->tax = new InvoiceTaxService();

        // MasterDataSeeder stops at states, so add the district/taluka the
        // student FKs require. State ids are seeder-assigned, not the
        // production values, which is fine: the service falls back to matching
        // on the state name.
        $this->gujaratId = \DB::table('ms_state')->where('sname', 'Gujarat')->value('state_id');
        $this->maharashtraId = \DB::table('ms_state')->where('sname', 'Maharashtra')->value('state_id');
        $this->indiaId = \DB::table('ms_country')->where('cname', 'India')->value('country_id');

        $distId = \DB::table('ms_dist')->insertGetId(['state_id' => $this->gujaratId, 'dname' => 'Kheda', 'created_at' => now()]);
        $this->talukoId = \DB::table('ms_taluko')->insertGetId(['dist_id' => $distId, 'tname' => 'Matar', 'created_at' => now()]);
    }

    private function student(array $attrs = []): Student
    {
        static $n = 0;
        $n++;

        return Student::create(array_merge([
            'fname' => 'TEST',
            'mname' => 'X',
            'lname' => 'STUDENT',
            'email' => "tax{$n}@example.com",
            'gender' => 'M',
            'studenttype_id' => 1,
            'eduboard_id' => 1,
            'std_id' => 1,
            'takula_id' => $this->talukoId,
            'country_id' => $this->indiaId,
            'state_id' => $this->gujaratId,
        ], $attrs));
    }

    private function invoiceFor(?Student $student, float $total, array $extra = []): Invoice
    {
        return Invoice::create(array_merge([
            'invoice_date' => now(),
            'total_before_discount' => $total,
            'discount_total' => 0,
            'total_due' => $total,
            'status' => 'PAID',
            'student_id' => $student?->student_id,
            'school_id' => $student?->school_id,
        ], $extra))->fresh();
    }

    public function test_gujarat_student_gets_nine_percent_cgst_and_sgst()
    {
        $student = $this->student(['country_id' => $this->indiaId, 'state_id' => $this->gujaratId]);
        $tax = $this->tax->resolve($this->invoiceFor($student, 2500));

        $this->assertEqualsWithDelta(2118.64, $tax['taxable'], 0.01);
        $this->assertEqualsWithDelta(190.68, $tax['sgst'], 0.01);
        $this->assertEqualsWithDelta(190.68, $tax['cgst'], 0.01);
        $this->assertSame(0.0, $tax['igst']);
        $this->assertFalse($tax['is_foreign']);
    }

    public function test_non_gujarat_indian_student_gets_eighteen_percent_igst()
    {
        $student = $this->student(['country_id' => $this->indiaId, 'state_id' => $this->maharashtraId]);
        $tax = $this->tax->resolve($this->invoiceFor($student, 2500));

        $this->assertEqualsWithDelta(2118.64, $tax['taxable'], 0.01);
        $this->assertEqualsWithDelta(381.36, $tax['igst'], 0.01);
        $this->assertSame(0.0, $tax['sgst']);
        $this->assertSame(0.0, $tax['cgst']);
    }

    public function test_gujarat_student_with_no_country_is_still_taxed_on_their_state()
    {
        // Previously a NULL country_id fell through to "no GST", which is why
        // some Gujarat invoices exported as 2500.00 / 0 / 0 / 0. Tax is now
        // driven by the state, so these get the intra-state split.
        $student = $this->student(['country_id' => null, 'state_id' => $this->gujaratId]);
        $tax = $this->tax->resolve($this->invoiceFor($student, 2500));

        $this->assertEqualsWithDelta(2118.64, $tax['taxable'], 0.01);
        $this->assertEqualsWithDelta(190.68, $tax['sgst'], 0.01);
        $this->assertEqualsWithDelta(190.68, $tax['cgst'], 0.01);
        $this->assertSame(0.0, $tax['igst']);
    }

    public function test_student_with_no_country_and_no_state_falls_back_to_igst()
    {
        $student = $this->student(['country_id' => null, 'state_id' => null]);
        $tax = $this->tax->resolve($this->invoiceFor($student, 2500));

        $this->assertEqualsWithDelta(2118.64, $tax['taxable'], 0.01);
        $this->assertEqualsWithDelta(381.36, $tax['igst'], 0.01);
    }

    public function test_student_in_a_foreign_country_gets_no_gst()
    {
        $usId = \DB::table('ms_country')->where('cname', 'United States')->value('country_id');
        $student = $this->student(['country_id' => $usId, 'state_id' => null]);
        $tax = $this->tax->resolve($this->invoiceFor($student, 2500));

        $this->assertSame(2500.0, $tax['taxable']);
        $this->assertSame(0.0, $tax['sgst']);
        $this->assertSame(0.0, $tax['cgst']);
        $this->assertSame(0.0, $tax['igst']);
        $this->assertTrue($tax['is_foreign']);
        $this->assertFalse($tax['is_india']);
    }

    public function test_item_taxable_backs_gst_out_of_the_line_amount()
    {
        $student = $this->student(['country_id' => $this->indiaId, 'state_id' => $this->gujaratId]);
        $invoice = $this->invoiceFor($student, 2500);
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->invoice_id,
            'description' => 'Semester-01',
            'qty' => 1,
            'unit_amount' => 2500,
            'line_amount' => 2500,
        ]);

        $tax = $this->tax->resolve($invoice);
        $this->assertEqualsWithDelta(2500 / 1.18, $this->tax->itemTaxable($item, $tax), 0.0001);
    }

}
