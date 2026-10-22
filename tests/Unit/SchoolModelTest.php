<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\School;
use App\Models\Student;
use App\Models\SchoolType;
use App\Models\EducationalBoard;
use App\Models\FeePlan;
use App\Models\DiscountRule;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchoolModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_school_belongs_to_school_type()
    {
        $schoolType = SchoolType::first();
        $school = School::factory()->create(['schooltype_id' => $schoolType->schooltype_id]);
        
        $this->assertInstanceOf(SchoolType::class, $school->schoolType);
        $this->assertEquals($schoolType->schooltype_id, $school->schoolType->schooltype_id);
    }

    public function test_school_belongs_to_educational_board()
    {
        $board = EducationalBoard::first();
        $school = School::factory()->create(['eduboard_id' => $board->eduboard_id]);
        
        $this->assertInstanceOf(EducationalBoard::class, $school->educationalBoard);
        $this->assertEquals($board->eduboard_id, $school->educationalBoard->eduboard_id);
    }

    public function test_school_has_many_students()
    {
        $school = School::factory()->create();
        $student1 = Student::factory()->create(['school_id' => $school->school_id]);
        $student2 = Student::factory()->create(['school_id' => $school->school_id]);
        
        $this->assertCount(2, $school->students);
        $this->assertInstanceOf(Student::class, $school->students->first());
    }

    public function test_school_has_many_fee_plans()
    {
        $school = School::factory()->create();
        $feePlan1 = FeePlan::factory()->create(['school_id' => $school->school_id]);
        $feePlan2 = FeePlan::factory()->create(['school_id' => $school->school_id]);
        
        $this->assertCount(2, $school->feePlans);
        $this->assertInstanceOf(FeePlan::class, $school->feePlans->first());
    }

    public function test_school_has_many_discount_rules()
    {
        $school = School::factory()->create();
        $discount1 = DiscountRule::factory()->create(['school_id' => $school->school_id]);
        $discount2 = DiscountRule::factory()->create(['school_id' => $school->school_id]);
        
        $this->assertCount(2, $school->discountRules);
        $this->assertInstanceOf(DiscountRule::class, $school->discountRules->first());
    }

    public function test_active_scope_filters_active_schools()
    {
        School::factory()->create(['inactive' => false]);
        School::factory()->create(['inactive' => false]);
        School::factory()->create(['inactive' => true]);
        
        $activeSchools = School::active()->get();
        
        $this->assertCount(2, $activeSchools);
    }

    public function test_final_scope_filters_final_schools()
    {
        School::factory()->create(['status' => 1]);
        School::factory()->create(['status' => 1]);
        School::factory()->create(['status' => 0]);
        
        $finalSchools = School::final()->get();
        
        $this->assertCount(2, $finalSchools);
    }

    public function test_lead_scope_filters_lead_schools()
    {
        School::factory()->create(['status' => 0]);
        School::factory()->create(['status' => 0]);
        School::factory()->create(['status' => 1]);
        
        $leadSchools = School::lead()->get();
        
        $this->assertCount(2, $leadSchools);
    }
}
