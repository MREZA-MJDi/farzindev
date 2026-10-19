<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Use config() instead of env() so seeding also works if config is cached.
        $adminPassword = config('farzin.admin.password');

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
                'email' => config('farzin.admin.email', 'admin@farajihome.com'),
            ],
            [
                'name' => config('farzin.admin.name', 'Farzin Admin'),
                'role' => 'admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );
    }
}
