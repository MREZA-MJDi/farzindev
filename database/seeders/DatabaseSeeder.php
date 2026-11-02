<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Validate administrator provisioning before any local catalogue
        // seeders run, so a missing credential cannot leave a partial seed.
        $adminEmail = mb_strtolower(trim((string) config('farzin.admin.email', '')));
        $adminPassword = (string) config('farzin.admin.password', '');

        if ($adminEmail === '' || ! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'Set a valid ADMIN_EMAIL before running the database seeder.'
            );
        }

        $emailOwner = User::query()
            ->whereRaw('LOWER(TRIM(email)) = ?', [$adminEmail])
            ->first();

        if ($emailOwner && ! $emailOwner->isAdmin()) {
            throw new RuntimeException(
                'ADMIN_EMAIL belongs to a non-admin account. Choose a dedicated administrator email before seeding.'
            );
        }

        $hasAnyAdmin = User::query()->where('role', 'admin')->exists();

        if (! $hasAnyAdmin && ($adminPassword === '' || mb_strlen($adminPassword) < 12)) {
            throw new RuntimeException(
                'Set a strong ADMIN_PASSWORD of at least 12 characters before creating the initial administrator.'
            );
        }

        if (app()->environment('local')) {
            $this->call([
                CategorySeeder::class,
            ]);
        }

        // ProductSeeder creates preview catalogue records whose specifications
        // and prices still require staff verification. Never create them in
        // the production database; publish verified products through the admin.
        if (app()->environment('local')) {
            $this->call(ProductSeeder::class);
        }

        $this->call([
            UserSeeder::class,
        ]);

        // The former demo carts/orders referenced retired furniture records
        // and an unseeded Livora customer. Local-only runs create preview
        // catalogue data; production seeding only provisions the configured admin.
    }
}
