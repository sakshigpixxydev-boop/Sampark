<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Student;
use App\Models\School;
use App\Models\PaymentLog;
use App\Models\DiscountRule;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_invoice_belongs_to_student()
    {
        $student = Student::factory()->create();
        $invoice = Invoice::factory()->create(['student_id' => $student->student_id]);
        
        $this->assertInstanceOf(Student::class, $invoice->student);
        $this->assertEquals($student->student_id, $invoice->student->student_id);
    }

    public function test_invoice_belongs_to_school()
    {
        $school = School::factory()->create();
        $invoice = Invoice::factory()->create(['school_id' => $school->school_id]);
        
        $this->assertInstanceOf(School::class, $invoice->school);
        $this->assertEquals($school->school_id, $invoice->school->school_id);
    }

    public function test_invoice_has_many_items()
    {
        $invoice = Invoice::factory()->create();
        $item1 = InvoiceItem::factory()->create(['invoice_id' => $invoice->invoice_id]);
        $item2 = InvoiceItem::factory()->create(['invoice_id' => $invoice->invoice_id]);
        
        $this->assertCount(2, $invoice->items);
        $this->assertInstanceOf(InvoiceItem::class, $invoice->items->first());
    }

    public function test_invoice_has_many_payments()
    {
        $invoice = Invoice::factory()->create();
        $payment1 = PaymentLog::factory()->create(['invoice_id' => $invoice->invoice_id]);
        $payment2 = PaymentLog::factory()->create(['invoice_id' => $invoice->invoice_id]);
        
        $this->assertCount(2, $invoice->payments);
        $this->assertInstanceOf(PaymentLog::class, $invoice->payments->first());
    }

    public function test_calculate_total_sums_invoice_items()
    {
        $student = Student::factory()->create();
        $invoice = Invoice::factory()->create([
            'student_id' => $student->student_id,
            'total_before_discount' => 0,
            'discount_total' => 0,
            'total_due' => 0
        ]);
        
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'line_amount' => 500
        ]);
        
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'line_amount' => 300
        ]);
        
        $total = $invoice->calculateTotal();
        
        $this->assertEquals(800, $invoice->total_before_discount);
    }

    public function test_calculate_total_applies_student_discounts()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        
        DiscountRule::factory()->create([
            'school_id' => $school->school_id,
            'discount_type' => 'FLAT',
            'discount_value' => 100,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $invoice = Invoice::factory()->create([
            'student_id' => $student->student_id,
            'total_before_discount' => 0,
            'discount_total' => 0,
            'total_due' => 0
        ]);
        
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'line_amount' => 1000
        ]);
        
        $total = $invoice->calculateTotal();
        
        $this->assertEquals(1000, $invoice->total_before_discount);
        $this->assertEquals(100, $invoice->discount_total);
        $this->assertEquals(900, $invoice->total_due);
    }

    public function test_apply_discount_adds_flat_discount()
    {
        $invoice = Invoice::factory()->create([
            'total_before_discount' => 1000,
            'discount_total' => 0,
            'total_due' => 1000
        ]);
        
        $discount = DiscountRule::factory()->create([
            'discount_type' => 'FLAT',
            'discount_value' => 150
        ]);
        
        $invoice->applyDiscount($discount);
        
        $this->assertEquals(150, $invoice->discount_total);
        $this->assertEquals(850, $invoice->total_due);
    }

    public function test_apply_discount_adds_percent_discount()
    {
        $invoice = Invoice::factory()->create([
            'total_before_discount' => 1000,
            'discount_total' => 0,
            'total_due' => 1000
        ]);
        
        $discount = DiscountRule::factory()->create([
            'discount_type' => 'PERCENT',
            'discount_value' => 20
        ]);
        
        $invoice->applyDiscount($discount);
        
        $this->assertEquals(200, $invoice->discount_total);
        $this->assertEquals(800, $invoice->total_due);
    }

    public function test_by_status_scope_filters_invoices()
    {
        Invoice::factory()->create(['status' => 'ISSUED']);
        Invoice::factory()->create(['status' => 'ISSUED']);
        Invoice::factory()->create(['status' => 'PAID']);
        
        $issuedInvoices = Invoice::byStatus('ISSUED')->get();
        
        $this->assertCount(2, $issuedInvoices);
    }
}
