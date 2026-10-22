<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FeePlanService;
use App\Models\FeePlan;
use App\Models\DiscountRule;
use App\Models\School;
use App\Models\SemesterBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeePlanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected FeePlanService $feePlanService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        $this->feePlanService = new FeePlanService();
    }

    public function test_calculate_fees_returns_correct_structure()
    {
        $school = School::factory()->create();
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        $studentData = [
            'school_id' => $school->school_id,
            'std_id' => 1
        ];
        
        $result = $this->feePlanService->calculateFees($studentData, $batch->sembatches_id);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('subtotal', $result);
        $this->assertArrayHasKey('discount_total', $result);
        $this->assertArrayHasKey('total_due', $result);
        $this->assertArrayHasKey('fee_plans', $result);
    }

    public function test_calculate_fees_sums_multiple_fee_plans()
    {
        $school = School::factory()->create();
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        FeePlan::factory()->create([
            'school_id' => null,
            'std_id' => null,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 500,
            'valid_from' => now()->subDay()
        ]);
        
        $studentData = [
            'school_id' => $school->school_id,
            'std_id' => 1
        ];
        
        $result = $this->feePlanService->calculateFees($studentData, $batch->sembatches_id);
        
        $this->assertEquals(1500, $result['subtotal']);
        $this->assertEquals(1500, $result['total_due']);
    }

    public function test_calculate_fees_applies_school_discounts()
    {
        $school = School::factory()->create();
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        DiscountRule::factory()->create([
            'school_id' => $school->school_id,
            'discount_type' => 'FLAT',
            'discount_value' => 150,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $studentData = [
            'school_id' => $school->school_id,
            'std_id' => 1
        ];
        
        $result = $this->feePlanService->calculateFees($studentData, $batch->sembatches_id);
        
        $this->assertEquals(1000, $result['subtotal']);
        $this->assertEquals(150, $result['discount_total']);
        $this->assertEquals(850, $result['total_due']);
    }

    public function test_calculate_fees_applies_percent_discounts()
    {
        $school = School::factory()->create();
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        DiscountRule::factory()->create([
            'school_id' => $school->school_id,
            'discount_type' => 'PERCENT',
            'discount_value' => 20,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $studentData = [
            'school_id' => $school->school_id,
            'std_id' => 1
        ];
        
        $result = $this->feePlanService->calculateFees($studentData, $batch->sembatches_id);
        
        $this->assertEquals(1000, $result['subtotal']);
        $this->assertEquals(200, $result['discount_total']);
        $this->assertEquals(800, $result['total_due']);
    }

    public function test_calculate_fees_ensures_non_negative_total()
    {
        $school = School::factory()->create();
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 100,
            'valid_from' => now()->subDay()
        ]);
        
        DiscountRule::factory()->create([
            'school_id' => $school->school_id,
            'discount_type' => 'FLAT',
            'discount_value' => 200,
            'active' => true,
            'valid_from' => now()->subDay()
        ]);
        
        $studentData = [
            'school_id' => $school->school_id,
            'std_id' => 1
        ];
        
        $result = $this->feePlanService->calculateFees($studentData, $batch->sembatches_id);
        
        $this->assertEquals(0, $result['total_due']);
    }

    public function test_calculate_batch_total_sums_all_students()
    {
        $school = School::factory()->create();
        $batch = SemesterBatch::factory()->create();
        
        FeePlan::factory()->create([
            'school_id' => $school->school_id,
            'sembatches_id' => $batch->sembatches_id,
            'amount' => 1000,
            'valid_from' => now()->subDay()
        ]);
        
        $students = [
            ['data' => ['school_id' => $school->school_id, 'std_id' => 1]],
            ['data' => ['school_id' => $school->school_id, 'std_id' => 1]],
            ['data' => ['school_id' => $school->school_id, 'std_id' => 1]],
        ];
        
        $result = $this->feePlanService->calculateBatchTotal($students, $batch->sembatches_id);
        
        $this->assertEquals(3000, $result['total_fees']);
        $this->assertEquals(1000, $result['fee_per_student']);
        $this->assertEquals(3, $result['student_count']);
    }
}
