<?php

namespace Tests\\Feature;

use App\\Models\\Category;
use App\\Models\\Product;
use App\\Models\\User;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_open_an_empty_wishlist(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)
            ->get(route('account.wishlist.index'))
            ->assertOk()
            ->assertSee('هنوز چیزی ذخیره نکرده‌ای')
            ->assertSee('کشف محصولات');
    }

    public function test_customer_can_add_a_product_and_see_it_in_the_wishlist(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();

        $this->actingAs($customer)
            ->post(route('account.wishlist.store', $product))
            ->assertRedirect();

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->get(route('account.wishlist.index'))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee('محصول ذخیره شده');
    }

    public function test_adding_the_same_product_twice_does_not_create_duplicate_wishlist_rows(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();

        $this->actingAs($customer);

        $this->post(route('account.wishlist.store', $product))->assertRedirect();
        $this->post(route('account.wishlist.store', $product))->assertRedirect();

        $this->assertSame(
            1,
            $customer->wishlists()->where('product_id', $product->id)->count()
        );
    }

    public function test_customer_can_remove_a_product_from_the_wishlist(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->product();

        $customer->wishlists()->create(['product_id' => $product->id]);

        $this->actingAs($customer)
            ->delete(route('account.wishlist.destroy', $product))
            ->assertRedirect();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $customer->id,
            'product_id' => $product->id,
        ]);

        $this->get(route('account.wishlist.index'))
            ->assertOk()
            ->assertSee('هنوز چیزی ذخیره نکرده‌ای');
    }

    public function test_wishlist_is_paginated_without_losing_the_query_contract(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $products = collect(range(1, 13))->map(fn (int $number) => $this->product($number));

        foreach ($products as $product) {
            $customer->wishlists()->create(['product_id' => $product->id]);
        }

        $this->actingAs($customer)
            ->get(route('account.wishlist.index'))
            ->assertOk()
            ->assertSee($products->first()->name)
            ->assertDontSee($products->last()->name);

        $this->get(route('account.wishlist.index', ['page' => 2]))
            ->assertOk()
            ->assertSee($products->last()->name);
    }

    public function test_guests_and_admins_cannot_access_customer_wishlists(): void
    {
        $product = $this->product();

        $this->get(route('account.wishlist.index'))
            ->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('account.wishlist.index'))
            ->assertForbidden();

        $this->post(route('account.wishlist.store', $product))
            ->assertForbidden();
    }

    private function product(int $sequence = 1): Product
    {
        $category = Category::create([
            'name' => 'دسته آزمایشی',
            'slug' => 'wishlist-category-' . $sequence,
            'description' => 'دسته آزمایشی برای تست علاقه‌مندی‌ها',
            'is_active' => true,
            'sort_order' => $sequence,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'محصول تست علاقه‌مندی ' . $sequence,
            'slug' => 'wishlist-product-' . $sequence,
            'sku' => 'WISHLIST-TEST-' . $sequence,
            'short_description' => 'محصول آزمایشی',
            'description' => 'توضیحات محصول آزمایشی',
            'price' => 10000000,
            'compare_at_price' => null,
            'stock' => 10,
            'status' => 'active',
            'is_featured' => false,
            'is_new' => false,
            'installment_enabled' => false,
        ]);
    }
}
