<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Fail before mutating the catalogue if production admin credentials
        // are missing; otherwise the seed would stop halfway through.
        if (app()->environment('production')) {
            $adminEmail = config('farzin.admin.email');

            if (blank($adminEmail)) {
                throw new \RuntimeException(
                    'Set ADMIN_EMAIL before seeding the production database.'
                );
            }

            $existingAdmin = User::query()
                ->where('email', $adminEmail)
                ->first();

            if ($existingAdmin && $existingAdmin->role !== 'admin') {
                throw new \RuntimeException(
                    'ADMIN_EMAIL belongs to a non-admin account. Choose a dedicated administrator email before seeding.'
                );
            }

            // The one-time password is required only when no admin exists.
            // Existing administrators can safely re-run idempotent catalogue seeds
            // after ADMIN_PASSWORD has been removed from the environment.
            if (! $existingAdmin && blank(config('farzin.admin.password'))) {
                throw new \RuntimeException(
                    'Set ADMIN_PASSWORD before creating the initial production administrator.'
                );
            }
        }

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
        ]);

        // The former demo carts/orders referenced retired furniture records
        // and an unseeded Livora customer. Keep normal seeding deterministic:
        // only the Farzin catalogue and the configured admin are created.
    }
}
