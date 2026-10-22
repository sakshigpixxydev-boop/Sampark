<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\InvoiceService;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\School;
use App\Models\FeePlan;
use App\Models\DiscountRule;
use App\Models\SemesterBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected InvoiceService $invoiceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        $this->invoiceService = new InvoiceService();
    }

    public function test_generate_invoice_creates_invoice_with_items()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addMonth()
        ]);
        
        $invoice = $this->invoiceService->generateInvoice($student->student_id, $batch->sembatches_id);
        
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($student->student_id, $invoice->student_id);
        $this->assertEquals(1000, $invoice->total_before_discount);
        $this->assertCount(1, $invoice->items);
    }

    public function test_generate_invoice_applies_school_discounts()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        DiscountRule::factory()->create([
            'scope' => 'SCHOOL',
            'school_id' => $school->school_id,
            'discount_type' => 'FLAT',
            'discount_value' => 200,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $invoice = $this->invoiceService->generateInvoice($student->student_id, $batch->sembatches_id);
        
        $this->assertEquals(1000, $invoice->total_before_discount);
        $this->assertEquals(200, $invoice->discount_total);
        $this->assertEquals(800, $invoice->total_due);
    }

    public function test_generate_invoice_applies_student_discounts()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        DiscountRule::factory()->create([
            'scope' => 'STUDENT',
            'student_id' => $student->student_id,
            'discount_type' => 'PERCENT',
            'discount_value' => 10,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $invoice = $this->invoiceService->generateInvoice($student->student_id, $batch->sembatches_id);
        
        $this->assertEquals(1000, $invoice->total_before_discount);
        $this->assertEquals(100, $invoice->discount_total);
        $this->assertEquals(900, $invoice->total_due);
    }

    public function test_generate_invoice_throws_exception_when_no_fee_plans()
    {
        $student = Student::factory()->create();
        $batch = SemesterBatch::factory()->create();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No applicable fee plans found');
        
        $this->invoiceService->generateInvoice($student->student_id, $batch->sembatches_id);
    }

    public function test_update_status_changes_invoice_status()
    {
        $invoice = Invoice::factory()->create(['status' => 'DRAFT']);
        
        $updatedInvoice = $this->invoiceService->updateStatus($invoice->invoice_id, 'ISSUED');
        
        $this->assertEquals('ISSUED', $updatedInvoice->status);
    }

    public function test_update_status_throws_exception_for_invalid_status()
    {
        $invoice = Invoice::factory()->create();
        
        $this->expectException(\InvalidArgumentException::class);
        
        $this->invoiceService->updateStatus($invoice->invoice_id, 'INVALID');
    }

    public function test_void_invoice_sets_status_to_void()
    {
        $invoice = Invoice::factory()->create(['status' => 'ISSUED']);
        
        $voidedInvoice = $this->invoiceService->voidInvoice($invoice->invoice_id);
        
        $this->assertEquals('VOID', $voidedInvoice->status);
    }

    public function test_recalculate_invoice_updates_totals()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
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
        
        $recalculatedInvoice = $this->invoiceService->recalculateInvoice($invoice->invoice_id);
        
        $this->assertEquals(800, $recalculatedInvoice->total_before_discount);
        $this->assertEquals(800, $recalculatedInvoice->total_due);
    }
}
