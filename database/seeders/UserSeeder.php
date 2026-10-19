<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminPassword = env('ADMIN_PASSWORD');

        if (blank($adminPassword)) {
            if (app()->environment(['local', 'testing'])) {
                $adminPassword = 'password';
            } else {
                throw new \RuntimeException(
                    'ADMIN_PASSWORD must be configured before running the user seeder in production.'
                );
            }
        }

        User::updateOrCreate(
            [
                'email' => env('ADMIN_EMAIL', 'admin@farzin.test'),
            ],
            [
                'name' => env('ADMIN_NAME', 'Farzin Admin'),
                'role' => 'admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );
    }
}
