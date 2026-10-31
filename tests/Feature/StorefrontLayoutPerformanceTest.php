<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StorefrontLayoutPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_layout_uses_one_aggregate_query_for_both_cart_badges(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $category = Category::create([
            'name' => 'هود آشپزخانه',
            'slug' => 'layout-performance-hoods',
            'description' => 'دسته‌بندی آزمایشی',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'هود آزمایشی',
            'slug' => 'layout-performance-hood',
            'sku' => 'FAR-LAYOUT-PERF-01',
            'short_description' => 'محصول آزمایشی',
            'description' => 'توضیحات آزمایشی',
            'price' => 10000000,
            'stock' => 5,
            'status' => 'active',
        ]);
        $cart = Cart::create([
            'user_id' => $customer->id,
            'session_id' => null,
            'status' => 'active',
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'product_variant_id' => null,
            'variant_key' => null,
            'variant_options' => null,
            'quantity' => 3,
            'unit_price' => 10000000,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($customer)
            ->get(route('about'))
            ->assertOk();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $cartSummaryQueries = collect($queries)
            ->filter(fn (array $query): bool => str_contains(strtolower($query['query']), 'from "carts"')
                || str_contains(strtolower($query['query']), 'from `carts`'))
            ->values();

        $this->assertCount(
            1,
            $cartSummaryQueries,
            'The desktop and mobile navigation must share one cart summary query.'
        );

        $html = $response->getContent();

        $this->assertSame(2, substr_count($html, 'data-cart-count'));
        $this->assertSame(2, substr_count($html, '>3</span>'));
    }
}
