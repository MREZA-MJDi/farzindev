<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Use config() so this seeder also works after config:cache.
        // Credentials must be explicitly provisioned; never create a weak demo admin.
        $email = mb_strtolower(trim((string) config('farzin.admin.email', '')));
        $adminPassword = (string) config('farzin.admin.password', '');

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \\RuntimeException(
                'Set a valid ADMIN_EMAIL before creating the administrator.'
            );
        }

        if ($adminPassword === '' || mb_strlen($adminPassword) < 12) {
            throw new \\RuntimeException(
                'Set a strong ADMIN_PASSWORD of at least 12 characters before creating the administrator.'
            );
        }

        $existingAdmin = User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])
            ->first();

        if ($existingAdmin) {
            if (! $existingAdmin->isAdmin()) {
                throw new \\RuntimeException(
                    'The configured ADMIN_EMAIL already belongs to a non-admin user. Choose a dedicated administrator email; the existing account was not changed.'
                );
            }

            // Existing admin accounts are intentionally not rotated by db:seed.
            // Use the explicit admin:sync command to apply credential changes.
            return;
        }

        User::create([
            'name' => trim((string) config('farzin.admin.name', 'Farzin Admin')) ?: 'Farzin Admin',
            'email' => $email,
            'role' => 'admin',
            'password' => Hash::make($adminPassword),
            'email_verified_at' => now(),
        ]);
    }
}
