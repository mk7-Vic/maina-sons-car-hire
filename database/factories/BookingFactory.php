<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookingFactory extends Factory
{
    public function definition(): array
    {
        $pickupAt = now()->addDays(fake()->numberBetween(1, 30));
        $rentalDays = fake()->numberBetween(1, 7);
        $dailyRate = fake()->numberBetween(4000, 20000);

        return [
            'booking_reference' => 'MS-'.Str::upper(Str::random(10)),
            'user_id' => User::factory(),
            'vehicle_id' => Vehicle::factory(),
            'pickup_branch_id' => Branch::factory(),
            'return_branch_id' => Branch::factory(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->numerify('07########'),
            'pickup_at' => $pickupAt,
            'return_at' => $pickupAt->copy()->addDays($rentalDays),
            'rental_days' => $rentalDays,
            'daily_rate' => $dailyRate,
            'total_amount' => $dailyRate * $rentalDays,
            'status' => 'pending',
            'notes' => null,
        ];
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}
