<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Seed only the Farzin catalog structure and safe product cleanup.
         * Real product data, specifications, variants and images are managed
         * through the admin dashboard instead of fabricated sample records.
         */
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
        ]);

        // Fictional carts, orders and payments remain local/testing-only.
        if (app()->environment(['local', 'testing'])) {
            $this->call([
                AddressSeeder::class,
                CartSeeder::class,
                WishlistSeeder::class,
                OrderSeeder::class,
                OrderItemSeeder::class,
                PaymentSeeder::class,
            ]);
        }
    }
}
