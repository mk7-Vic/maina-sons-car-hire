<?php

namespace Database\Factories;

use App\Models\VehicleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_category_id' => VehicleCategory::factory(),
            'registration_number' => fake()->unique()->bothify('K?? ###?'),
            'make' => fake()->randomElement(['Toyota', 'Nissan', 'Mazda', 'Subaru', 'Honda']),
            'model' => fake()->word(),
            'year' => fake()->numberBetween(2018, 2026),
            'color' => fake()->safeColorName(),
            'transmission' => fake()->randomElement(['Automatic', 'Manual']),
            'fuel_type' => fake()->randomElement(['Petrol', 'Diesel', 'Hybrid']),
            'seats' => fake()->randomElement([5, 7, 8]),
            'daily_rate' => fake()->numberBetween(4000, 20000),
            'description' => fake()->sentence(),
            'image_path' => null,
            'status' => 'available',
        ];
    }
}
