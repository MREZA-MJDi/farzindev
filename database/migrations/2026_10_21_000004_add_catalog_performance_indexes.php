<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->index(
                ['is_active', 'sort_order'],
                'categories_active_sort_idx'
            );
        });

        Schema::table('products', function (Blueprint $table): void {
            // Replace indexes whose prefixes are now covered by more useful composites.
            $table->dropIndex('products_status_is_featured_index');
            $table->dropIndex('products_category_id_status_index');

            $table->index(
                ['status', 'is_featured', 'is_new', 'updated_at'],
                'products_status_featured_new_updated_idx'
            );
            $table->index(
                ['status', 'updated_at'],
                'products_status_updated_idx'
            );
            $table->index(
                ['category_id', 'status', 'updated_at'],
                'products_category_status_updated_idx'
            );
            $table->index(
                ['status', 'price'],
                'products_status_price_idx'
            );
            $table->index(
                ['status', 'name'],
                'products_status_name_idx'
            );
        });

        Schema::table('product_images', function (Blueprint $table): void {
            $table->index(
                ['product_id', 'is_primary', 'sort_order'],
                'product_images_product_primary_sort_idx'
            );
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->index(
                ['product_id', 'is_active', 'stock'],
                'product_variants_product_active_stock_idx'
            );
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->index(
                ['user_id', 'created_at'],
                'orders_user_created_idx'
            );
            $table->index(
                ['status', 'created_at'],
                'orders_status_created_idx'
            );
            $table->index(
                ['payment_status', 'created_at'],
                'orders_payment_status_created_idx'
            );
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index(
                ['role', 'created_at'],
                'users_role_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_created_idx');
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex('orders_user_created_idx');
            $table->dropIndex('orders_status_created_idx');
            $table->dropIndex('orders_payment_status_created_idx');
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropIndex('product_variants_product_active_stock_idx');
        });

        Schema::table('product_images', function (Blueprint $table): void {
            $table->dropIndex('product_images_product_primary_sort_idx');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex('products_status_featured_new_updated_idx');
            $table->dropIndex('products_status_updated_idx');
            $table->dropIndex('products_category_status_updated_idx');
            $table->dropIndex('products_status_price_idx');
            $table->dropIndex('products_status_name_idx');

            $table->index(['status', 'is_featured']);
            $table->index(['category_id', 'status']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex('categories_active_sort_idx');
        });
    }
};
