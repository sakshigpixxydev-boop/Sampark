<?php

namespace Database\Factories;

use App\Models\Taluko;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

class TalukoFactory extends Factory
{
    protected $model = Taluko::class;

    public function definition(): array
    {
        return [
            'tname' => $this->faker->unique()->city(),
            'dist_id' => District::factory(),
        ];
    }
}
