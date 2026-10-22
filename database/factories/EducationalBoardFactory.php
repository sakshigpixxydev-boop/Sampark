<?php

namespace Database\Factories;

use App\Models\EducationalBoard;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationalBoardFactory extends Factory
{
    protected $model = EducationalBoard::class;

    public function definition(): array
    {
        return [
            'boardname' => $this->faker->unique()->words(2, true) . ' Board',
        ];
    }
}
