<?php

namespace Database\Factories;

use App\Models\PaymentLog;
use App\Models\Student;
use App\Models\Invoice;
use App\Models\PaymentMode;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentLogFactory extends Factory
{
    protected $model = PaymentLog::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'invoice_id' => Invoice::factory(),
            'paymentmode_id' => PaymentMode::factory(),
            'payment_datetime' => now(),
            'transaction_id' => $this->faker->uuid(),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'status' => 'PENDING',
        ];
    }
}
