<?php

namespace Database\Factories;

use App\Models\SemesterBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class SemesterBatchFactory extends Factory
{
    protected $model = SemesterBatch::class;

    public function definition(): array
    {
        return [
            'code_prefix' => $this->faker->words(3, true),
            'start_date' => now(),
            'end_date' => now()->addMonths(6),
            'capacity' => $this->faker->numberBetween(50, 200),
            'enrolled_count' => 0,
            'status' => 'Active',
        ];
    }
}
