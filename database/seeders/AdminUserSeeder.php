<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('admin.name');
        $email = config('admin.email');
        $password = config('admin.password');

        if (! $name || ! $email || ! $password) {
            throw new RuntimeException(
                'ADMIN_NAME, ADMIN_EMAIL and ADMIN_PASSWORD must be configured.'
            );
        }

        $admin = User::firstOrNew(['email' => $email]);

        $admin->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'email_verified_at' => now(),
        ])->save();
    }
}
