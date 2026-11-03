<?php

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CartIndexOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_variant_lookup_index_replaces_the_redundant_cart_product_prefix_index(): void
    {
        $this->assertTrue(Schema::hasIndex('cart_items', 'cart_product_variant_idx'));
        $this->assertFalse(Schema::hasIndex('cart_items', 'cart_items_cart_id_product_id_index'));
    }
}
