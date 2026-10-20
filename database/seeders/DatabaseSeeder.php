<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
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
