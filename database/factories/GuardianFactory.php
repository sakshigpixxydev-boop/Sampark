<?php

namespace Database\Factories;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'relation_id' => Relation::factory(),
            'fname' => $this->faker->firstName(),
            'mname' => $this->faker->firstName(),
            'lname' => $this->faker->lastName(),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'phone' => $this->faker->numerify('##########'),
            'email' => $this->faker->safeEmail(),
        ];
    }
}
