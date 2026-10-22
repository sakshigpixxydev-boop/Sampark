<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Student;
use App\Models\School;
use App\Models\Guardian;
use App\Models\SemesterBatch;
use App\Models\Invoice;
use App\Models\FeePlan;
use App\Models\DiscountRule;
use App\Models\StudentType;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\Taluko;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_student_belongs_to_student_type()
    {
        $studentType = StudentType::first();
        $student = Student::factory()->create(['studenttype_id' => $studentType->studenttype_id]);
        
        $this->assertInstanceOf(StudentType::class, $student->studentType);
        $this->assertEquals($studentType->studenttype_id, $student->studentType->studenttype_id);
    }

    public function test_student_belongs_to_school()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        
        $this->assertInstanceOf(School::class, $student->school);
        $this->assertEquals($school->school_id, $student->school->school_id);
    }

    public function test_student_has_many_guardians()
    {
        $student = Student::factory()->create();
        $guardian1 = Guardian::factory()->create(['student_id' => $student->student_id]);
        $guardian2 = Guardian::factory()->create(['student_id' => $student->student_id]);
        
        $this->assertCount(2, $student->guardians);
        $this->assertInstanceOf(Guardian::class, $student->guardians->first());
    }

    public function test_student_has_many_invoices()
    {
        $student = Student::factory()->create();
        $invoice1 = Invoice::factory()->create(['student_id' => $student->student_id]);
        $invoice2 = Invoice::factory()->create(['student_id' => $student->student_id]);
        
        $this->assertCount(2, $student->invoices);
        $this->assertInstanceOf(Invoice::class, $student->invoices->first());
    }

    public function test_student_belongs_to_many_semester_batches()
    {
        $student = Student::factory()->create();
        $batch = SemesterBatch::factory()->create();
        
        $student->semesterBatches()->attach($batch->sembatches_id, [
            'status' => 'Active',
            'assigned_at' => now()
        ]);
        
        $this->assertCount(1, $student->semesterBatches);
        $this->assertEquals($batch->sembatches_id, $student->semesterBatches->first()->sembatches_id);
    }

    public function test_calculate_fees_returns_sum_of_fee_plans()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000
        ]);
        
        FeePlan::factory()->create([
            'std_id' => $student->std_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 500
        ]);
        
        $totalFees = $student->calculateFees($batch->sembatches_id);
        
        $this->assertEquals(1500, $totalFees);
    }

    public function test_apply_discounts_calculates_flat_discount()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        
        DiscountRule::factory()->create([
            'school_id' => $school->school_id,
            'discount_type' => 'FLAT',
            'discount_value' => 200,
            'active' => true,
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addDay()
        ]);
        
        $discount = $student->applyDiscounts(1000);
        
        $this->assertEquals(200, $discount);
    }

    public function test_apply_discounts_calculates_percent_discount()
    {
        $school = School::factory()->create();
        $student = Student::factory()->create(['school_id' => $school->school_id]);
        
        DiscountRule::factory()->create([
            'school_id' => $school->school_id,
            'discount_type' => 'PERCENT',
            'discount_value' => 10,
            'active' => true,
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addDay()
        ]);
        
        $discount = $student->applyDiscounts(1000);
        
        $this->assertEquals(100, $discount);
    }

    public function test_apply_discounts_combines_school_and_student_discounts()
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
        
        DiscountRule::factory()->create([
            'student_id' => $student->student_id,
            'discount_type' => 'PERCENT',
            'discount_value' => 5,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $discount = $student->applyDiscounts(1000);
        
        $this->assertEquals(150, $discount);
    }
}
