<?php

namespace Database\Factories;

use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelationFactory extends Factory
{
    protected $model = Relation::class;

    public function definition(): array
    {
        return [
            'relation' => $this->faker->randomElement(['Father', 'Mother', 'Guardian', 'Uncle', 'Aunt']),
        ];
    }
}
