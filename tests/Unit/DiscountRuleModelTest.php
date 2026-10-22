<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\DiscountRule;
use App\Models\School;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DiscountRuleModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_discount_rule_belongs_to_school()
    {
        $school = School::factory()->create();
        $discount = DiscountRule::factory()->create(['school_id' => $school->school_id]);
        
        $this->assertInstanceOf(School::class, $discount->school);
        $this->assertEquals($school->school_id, $discount->school->school_id);
    }

    public function test_discount_rule_belongs_to_student()
    {
        $student = Student::factory()->create();
        $discount = DiscountRule::factory()->create(['student_id' => $student->student_id]);
        
        $this->assertInstanceOf(Student::class, $discount->student);
        $this->assertEquals($student->student_id, $discount->student->student_id);
    }

    public function test_active_scope_filters_active_discounts()
    {
        DiscountRule::factory()->create(['active' => true]);
        DiscountRule::factory()->create(['active' => true]);
        DiscountRule::factory()->create(['active' => false]);
        
        $activeDiscounts = DiscountRule::active()->get();
        
        $this->assertCount(2, $activeDiscounts);
    }

    public function test_for_school_scope_filters_school_discounts()
    {
        $school1 = School::factory()->create();
        $school2 = School::factory()->create();
        
        DiscountRule::factory()->create([
            'scope' => 'SCHOOL',
            'school_id' => $school1->school_id
        ]);
        DiscountRule::factory()->create([
            'scope' => 'SCHOOL',
            'school_id' => $school1->school_id
        ]);
        DiscountRule::factory()->create([
            'scope' => 'SCHOOL',
            'school_id' => $school2->school_id
        ]);
        
        $school1Discounts = DiscountRule::forSchool($school1->school_id)->get();
        
        $this->assertCount(2, $school1Discounts);
    }

    public function test_for_student_scope_filters_student_discounts()
    {
        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();
        
        DiscountRule::factory()->create([
            'scope' => 'STUDENT',
            'student_id' => $student1->student_id
        ]);
        DiscountRule::factory()->create([
            'scope' => 'STUDENT',
            'student_id' => $student1->student_id
        ]);
        DiscountRule::factory()->create([
            'scope' => 'STUDENT',
            'student_id' => $student2->student_id
        ]);
        
        $student1Discounts = DiscountRule::forStudent($student1->student_id)->get();
        
        $this->assertCount(2, $student1Discounts);
    }
}
