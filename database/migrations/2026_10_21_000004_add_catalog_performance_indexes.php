<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexesIfMissing('categories', [
            [
                'columns' => ['is_active', 'sort_order'],
                'name' => 'categories_active_sort_idx',
            ],
        ]);

        /*
         * Add replacement indexes before removing the old ones. In particular,
         * products.category_id is covered by a foreign key, so MySQL must always
         * have a usable index whose leading column is category_id.
         *
         * This migration is deliberately resumable: MySQL commits many ALTER
         * TABLE statements independently, and a failed attempt may have already
         * created some indexes or removed a non-FK index.
         */
        $this->addIndexesIfMissing('products', [
            [
                'columns' => ['status', 'is_featured', 'is_new', 'updated_at'],
                'name' => 'products_status_featured_new_updated_idx',
            ],
            [
                'columns' => ['status', 'updated_at'],
                'name' => 'products_status_updated_idx',
            ],
            [
                'columns' => ['category_id', 'status', 'updated_at'],
                'name' => 'products_category_status_updated_idx',
            ],
            [
                'columns' => ['status', 'price'],
                'name' => 'products_status_price_idx',
            ],
            [
                'columns' => ['status', 'name'],
                'name' => 'products_status_name_idx',
            ],
        ]);

        // The category composite above now covers the foreign-key requirement.
        $this->dropIndexesIfPresent('products', [
            'products_status_is_featured_index',
            'products_category_id_status_index',
        ]);

        $this->addIndexesIfMissing('product_images', [
            [
                'columns' => ['product_id', 'is_primary', 'sort_order'],
                'name' => 'product_images_product_primary_sort_idx',
            ],
        ]);

        $this->addIndexesIfMissing('product_variants', [
            [
                'columns' => ['product_id', 'is_active', 'stock'],
                'name' => 'product_variants_product_active_stock_idx',
            ],
        ]);

        $this->addIndexesIfMissing('orders', [
            [
                'columns' => ['user_id', 'created_at'],
                'name' => 'orders_user_created_idx',
            ],
            [
                'columns' => ['status', 'created_at'],
                'name' => 'orders_status_created_idx',
            ],
            [
                'columns' => ['payment_status', 'created_at'],
                'name' => 'orders_payment_status_created_idx',
            ],
        ]);

        $this->addIndexesIfMissing('users', [
            [
                'columns' => ['role', 'created_at'],
                'name' => 'users_role_created_idx',
            ],
        ]);
    }

    public function down(): void
    {
        /*
         * Restore the original products indexes before dropping their
         * replacements. The category_id index is required by its FK.
         */
        $this->addIndexesIfMissing('products', [
            [
                'columns' => ['status', 'is_featured'],
                'name' => 'products_status_is_featured_index',
            ],
            [
                'columns' => ['category_id', 'status'],
                'name' => 'products_category_id_status_index',
            ],
        ]);

        $this->dropIndexesIfPresent('products', [
            'products_status_featured_new_updated_idx',
            'products_status_updated_idx',
            'products_category_status_updated_idx',
            'products_status_price_idx',
            'products_status_name_idx',
        ]);

        $this->dropIndexesIfPresent('product_variants', [
            'product_variants_product_active_stock_idx',
        ]);

        $this->dropIndexesIfPresent('product_images', [
            'product_images_product_primary_sort_idx',
        ]);

        $this->dropIndexesIfPresent('orders', [
            'orders_user_created_idx',
            'orders_status_created_idx',
            'orders_payment_status_created_idx',
        ]);

        $this->dropIndexesIfPresent('users', [
            'users_role_created_idx',
        ]);

        $this->dropIndexesIfPresent('categories', [
            'categories_active_sort_idx',
        ]);
    }

    /**
     * @param array<int, array{columns: array<int, string>, name: string}> $indexes
     */
    private function addIndexesIfMissing(string $tableName, array $indexes): void
    {
        $missing = array_values(array_filter(
            $indexes,
            static fn (array $index): bool => ! Schema::hasIndex($tableName, $index['name'])
        ));

        if ($missing === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($missing): void {
            foreach ($missing as $index) {
                $table->index($index['columns'], $index['name']);
            }
        });
    }

    /**
     * @param array<int, string> $indexNames
     */
    private function dropIndexesIfPresent(string $tableName, array $indexNames): void
    {
        $present = array_values(array_filter(
            $indexNames,
            static fn (string $indexName): bool => Schema::hasIndex($tableName, $indexName)
        ));

        if ($present === []) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($present): void {
            foreach ($present as $indexName) {
                $table->dropIndex($indexName);
            }
        });
    }
};
