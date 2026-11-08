<?php

namespace Database\Seeders;

use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Economy',
                'description' => 'Affordable and fuel-efficient vehicles.',
            ],
            [
                'name' => 'Sedan',
                'description' => 'Comfortable vehicles for everyday travel.',
            ],
            [
                'name' => 'SUV',
                'description' => 'Spacious vehicles for family and group travel.',
            ],
            [
                'name' => 'Luxury',
                'description' => 'Premium vehicles offering enhanced comfort.',
            ],
            [
                'name' => 'Van',
                'description' => 'Larger vehicles for groups and extra luggage.',
            ],
        ];

        foreach ($categories as $category) {
            VehicleCategory::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]
            );
        }
    }
}
