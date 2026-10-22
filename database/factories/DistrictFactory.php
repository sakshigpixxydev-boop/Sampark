<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

class DistrictFactory extends Factory
{
    protected $model = District::class;

    public function definition(): array
    {
        return [
            'distname' => $this->faker->unique()->city() . ' District',
            'state_id' => State::factory(),
        ];
    }
}
