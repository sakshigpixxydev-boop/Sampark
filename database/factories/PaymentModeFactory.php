<?php

namespace Database\Factories;

use App\Models\PaymentMode;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentModeFactory extends Factory
{
    protected $model = PaymentMode::class;

    public function definition(): array
    {
        return [
            'paymentmodename' => $this->faker->randomElement(['Cash', 'Credit Card', 'Debit Card', 'Bank Transfer', 'UPI']),
        ];
    }
}
