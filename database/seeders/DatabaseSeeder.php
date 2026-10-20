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

        // Fictional carts, orders and payments are limited to local/testing
        // work; they are never created as customer history in production.
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
