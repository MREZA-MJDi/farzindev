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
        $email = config('farzin.admin.email');
        $adminPassword = config('farzin.admin.password');

        if (blank($email)) {
            if (app()->environment(['local', 'testing'])) {
                $email = 'admin@farzin.test';
            } else {
                throw new \RuntimeException(
                    'ADMIN_EMAIL must be configured before creating the production administrator.'
                );
            }
        }

        if (blank($adminPassword)) {
            if (app()->environment(['local', 'testing'])) {
                $adminPassword = 'password';
            } else {
                throw new \RuntimeException(
                    'ADMIN_PASSWORD must be configured before creating the production administrator.'
                );
            }
        }

        $existingAdmin = User::query()->where('email', $email)->first();

        if ($existingAdmin) {
            if ($existingAdmin->role !== 'admin') {
                throw new \RuntimeException(
                    'The configured ADMIN_EMAIL already belongs to a non-admin user. Choose a dedicated administrator email; the existing account was not changed.'
                );
            }

            // Never reset a live admin password or silently alter the account on
            // every normal db:seed run. Use the documented password-reset flow
            // or a controlled one-time operation to rotate administrator access.
            return;
        }

        User::create([
            'name' => config('farzin.admin.name', 'Farzin Admin'),
            'email' => $email,
            'role' => 'admin',
            'password' => Hash::make($adminPassword),
            'email_verified_at' => now(),
        ]);
    }
}
