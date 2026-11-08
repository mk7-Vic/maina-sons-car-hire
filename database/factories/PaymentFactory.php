<?php

namespace Database\Factories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'amount' => fake()->numberBetween(4000, 100000),
            'payment_method' => fake()->randomElement(['cash', 'card', 'mpesa', 'bank_transfer']),
            'transaction_reference' => null,
            'status' => 'pending',
            'paid_at' => null,
        ];
    }
}
