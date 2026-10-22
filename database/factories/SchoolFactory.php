<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolType;
use App\Models\EducationalBoard;
use App\Models\Taluko;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'schooltype_id' => SchoolType::factory(),
            'eduboard_id' => EducationalBoard::factory(),
            'takula_id' => Taluko::factory(),
            'schoolname' => $this->faker->company() . ' School',
            'adminname' => $this->faker->name(),
            'admincontact' => $this->faker->numerify('##########'),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->numerify('##########'),
            'address1' => $this->faker->streetAddress(),
            'city_village' => $this->faker->city(),
            'pincode' => $this->faker->numerify('######'),
            'inactive' => false,
            'status' => 1,
        ];
    }
}
