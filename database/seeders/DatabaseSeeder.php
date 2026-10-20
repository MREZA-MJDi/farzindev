<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Fail before mutating the catalogue if production admin credentials
        // are missing; otherwise the seed would stop halfway through.
        if (
            app()->environment('production')
            && (
                blank(config('farzin.admin.email'))
                || blank(config('farzin.admin.password'))
            )
        ) {
            throw new \RuntimeException(
                'Set ADMIN_EMAIL and ADMIN_PASSWORD before seeding the initial production database.'
            );
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
