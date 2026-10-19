<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Prepare the two real starting categories and archive inherited
        // fictional furniture products. Never seed demo photos, variants,
        // carts, customers or orders into the Farzin catalogue.
        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            UserSeeder::class,
        ]);
    }
}
