<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\StudentType;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\Taluko;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'studenttype_id' => StudentType::factory(),
            'eduboard_id' => EducationalBoard::factory(),
            'std_id' => Standard::factory(),
            'takula_id' => Taluko::factory(),
            'school_id' => School::factory(),
            'fname' => $this->faker->firstName(),
            'mname' => $this->faker->firstName(),
            'lname' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'gender' => $this->faker->randomElement(['M', 'F']),
            'birthdate' => $this->faker->date(),
            'contact_no' => $this->faker->numerify('##########'),
        ];
    }
}
