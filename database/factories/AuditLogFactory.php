<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement([
                'booking.created',
                'booking.updated',
                'vehicle.created',
                'vehicle.updated',
            ]),
            'auditable_type' => null,
            'auditable_id' => null,
            'description' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
        ];
    }
}
