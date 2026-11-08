<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Nairobi Branch',
                'location' => 'Nairobi',
                'phone' => '0700000001',
                'email' => 'nairobi@mainasons.example',
            ],
            [
                'name' => 'Mombasa Branch',
                'location' => 'Mombasa',
                'phone' => '0700000002',
                'email' => 'mombasa@mainasons.example',
            ],
            [
                'name' => 'Kisumu Branch',
                'location' => 'Kisumu',
                'phone' => '0700000003',
                'email' => 'kisumu@mainasons.example',
            ],
        ];

        foreach ($branches as $branch) {
            Branch::updateOrCreate(
                ['name' => $branch['name']],
                [
                    'location' => $branch['location'],
                    'phone' => $branch['phone'],
                    'email' => $branch['email'],
                    'is_active' => true,
                ]
            );
        }
    }
}
