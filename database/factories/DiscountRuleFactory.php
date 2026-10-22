<?php

namespace Database\Factories;

use App\Models\DiscountRule;
use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountRuleFactory extends Factory
{
    protected $model = DiscountRule::class;

    public function definition(): array
    {
        return [
            'scope' => $this->faker->randomElement(['SCHOOL', 'STUDENT']),
            'discount_type' => $this->faker->randomElement(['FLAT', 'PERCENT']),
            'discount_value' => $this->faker->randomFloat(2, 5, 500),
            'applies_to' => 'ALL',
            'valid_from' => now(),
            'valid_to' => now()->addMonths(6),
            'active' => true,
            'school_id' => null,
            'student_id' => null,
        ];
    }
}
