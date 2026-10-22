<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Student;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'school_id' => School::factory(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'total_before_discount' => $this->faker->randomFloat(2, 500, 5000),
            'discount_total' => 0,
            'total_due' => $this->faker->randomFloat(2, 500, 5000),
            'status' => 'ISSUED',
        ];
    }
}
