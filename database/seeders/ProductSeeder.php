<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * The inherited catalogue contained fictional furniture products and
         * prices. Archive only that known demo SKU namespace; do not invent
         * Farzin stock, product specifications, or prices.
         *
         * The real hood/sink catalogue is managed from the admin dashboard.
         * Existing order records remain untouched.
         */
        Product::query()
            ->where('sku', 'like', 'LIV-%')
            ->update([
                'status' => 'archived',
                'is_featured' => false,
                'is_new' => false,
            ]);
    }
}
