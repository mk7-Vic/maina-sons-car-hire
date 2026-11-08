<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;
use RuntimeException;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            [
                'registration_number' => 'KDA 123A',
                'category' => 'economy',
                'make' => 'Toyota',
                'model' => 'Vitz',
                'year' => 2022,
                'color' => 'Silver',
                'transmission' => 'Automatic',
                'fuel_type' => 'Petrol',
                'seats' => 5,
                'daily_rate' => 3500,
                'description' => 'Compact and economical city vehicle.',
            ],
            [
                'registration_number' => 'KDB 456B',
                'category' => 'sedan',
                'make' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2023,
                'color' => 'White',
                'transmission' => 'Automatic',
                'fuel_type' => 'Petrol',
                'seats' => 5,
                'daily_rate' => 5500,
                'description' => 'Comfortable sedan for everyday travel.',
            ],
            [
                'registration_number' => 'KDC 789C',
                'category' => 'suv',
                'make' => 'Toyota',
                'model' => 'RAV4',
                'year' => 2024,
                'color' => 'Black',
                'transmission' => 'Automatic',
                'fuel_type' => 'Petrol',
                'seats' => 5,
                'daily_rate' => 8500,
                'description' => 'Versatile SUV for family trips.',
            ],
            [
                'registration_number' => 'KDD 234D',
                'category' => 'suv',
                'make' => 'Nissan',
                'model' => 'X-Trail',
                'year' => 2023,
                'color' => 'Grey',
                'transmission' => 'Automatic',
                'fuel_type' => 'Petrol',
                'seats' => 7,
                'daily_rate' => 9500,
                'description' => 'Spacious SUV with room for passengers and luggage.',
            ],
            [
                'registration_number' => 'KDE 567E',
                'category' => 'luxury',
                'make' => 'Mercedes-Benz',
                'model' => 'E-Class',
                'year' => 2024,
                'color' => 'Black',
                'transmission' => 'Automatic',
                'fuel_type' => 'Diesel',
                'seats' => 5,
                'daily_rate' => 18000,
                'description' => 'Premium sedan for executive travel.',
            ],
            [
                'registration_number' => 'KDF 890F',
                'category' => 'van',
                'make' => 'Toyota',
                'model' => 'Hiace',
                'year' => 2022,
                'color' => 'White',
                'transmission' => 'Manual',
                'fuel_type' => 'Diesel',
                'seats' => 8,
                'daily_rate' => 12000,
                'description' => 'Spacious van for group transportation.',
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            $category = VehicleCategory::where(
                'slug',
                $vehicleData['category']
            )->first();

            if (! $category) {
                throw new RuntimeException(
                    "Vehicle category '{$vehicleData['category']}' was not found."
                );
            }

            unset($vehicleData['category']);

            Vehicle::updateOrCreate(
                ['registration_number' => $vehicleData['registration_number']],
                [
                    ...$vehicleData,
                    'vehicle_category_id' => $category->id,
                    'status' => 'available',
                ]
            );
        }
    }
}
