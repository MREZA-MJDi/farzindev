<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * The variant lookup index begins with cart_id and product_id, so it
     * already serves every query covered by the older two-column index.
     * Keep the migration conditional to support partially upgraded databases.
     */
    public function up(): void
    {
        if (
            ! Schema::hasTable('cart_items')
            || ! Schema::hasIndex('cart_items', 'cart_product_variant_idx')
            || ! Schema::hasIndex('cart_items', 'cart_items_cart_id_product_id_index')
        ) {
            return;
        }

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropIndex('cart_items_cart_id_product_id_index');
        });
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('cart_items')
            || ! Schema::hasIndex('cart_items', 'cart_product_variant_idx')
            || Schema::hasIndex('cart_items', 'cart_items_cart_id_product_id_index')
        ) {
            return;
        }

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->index(
                ['cart_id', 'product_id'],
                'cart_items_cart_id_product_id_index'
            );
        });
    }
};
