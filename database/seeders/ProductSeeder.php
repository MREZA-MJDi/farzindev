<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * This project no longer seeds a placeholder catalogue.
     *
     * Preserve rows and order history, but archive the exact legacy placeholder
     * SKU prefixes that the former preview seeder created.
     */
    public function run(): void
    {
        Product::query()
            ->where(function ($query): void {
                $query
                    ->where('sku', 'like', 'FZ-SEED-%')
                    ->orWhere('sku', 'like', 'LIV-%');
            })
            ->update([
                'status' => 'archived',
                'stock' => 0,
                'is_featured' => false,
                'is_new' => false,
                'show_in_home_gallery' => false,
            ]);
    }
}
