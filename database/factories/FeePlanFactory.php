<?php

namespace Database\Factories;

use App\Models\FeePlan;
use App\Models\School;
use App\Models\Standard;
use App\Models\SemesterBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeePlanFactory extends Factory
{
    protected $model = FeePlan::class;

    public function definition(): array
    {
        return [
            'school_id' => null,
            'std_id' => null,
            'sembatches_id' => SemesterBatch::factory(),
            'fee_name' => $this->faker->words(3, true),
            'amount' => $this->faker->randomFloat(2, 100, 2000),
            'valid_from' => now(),
            'valid_to' => now()->addMonths(6),
        ];
    }
}
