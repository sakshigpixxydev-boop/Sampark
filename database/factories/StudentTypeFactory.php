<?php

namespace Database\Factories;

use App\Models\StudentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentTypeFactory extends Factory
{
    protected $model = StudentType::class;

    public function definition(): array
    {
        return [
            'studenttypename' => $this->faker->unique()->words(2, true),
        ];
    }
}
