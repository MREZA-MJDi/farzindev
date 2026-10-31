<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopPaginationAndBestSellersTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_paginates_twelve_products_and_preserves_sort_on_next_page(): void
    {
        $category = $this->category();

        for ($index = 1; $index <= 13; $index++) {
            $this->product($category, [
                'name' => 'Pagination Product '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'slug' => 'pagination-product-'.$index,
                'sku' => 'FAR-PAGINATION-'.$index,
            ]);
        }

        $this->get(route('shop.index', ['sort' => 'price_desc']))
            ->assertOk()
            ->assertSee('page=2', false)
            ->assertSee('sort=price_desc', false)
            ->assertSee('Pagination Product 13')
            ->assertDontSee('Pagination Product 01');

        $this->get(route('shop.index', ['page' => 2, 'sort' => 'price_desc']))
            ->assertOk()
            ->assertSee('Pagination Product 01');
    }

    public function test_popular_sort_uses_paid_order_quantities(): void
    {
        $category = $this->category();
        $lowSales = $this->product($category, [
            'name' => 'Low Sales Hood',
            'slug' => 'low-sales-hood',
            'sku' => 'FAR-LOW-SALES',
            'is_featured' => true,
        ]);
        $bestSeller = $this->product($category, [
            'name' => 'Best Seller Hood',
            'slug' => 'best-seller-hood',
            'sku' => 'FAR-BEST-SELLER',
        ]);

        $order = $this->paidOrder();
        $this->addOrderItem($order, $lowSales, 1);
        $this->addOrderItem($order, $bestSeller, 7);

        $response = $this->get(route('shop.index', ['sort' => 'popular']))
            ->assertOk();

        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Low Sales Hood'),
            strpos($html, 'Best Seller Hood'),
            'The product with more units sold should appear first in popular sorting.'
        );
    }

    public function test_homepage_uses_paid_sales_for_best_seller_ordering(): void
    {
        $category = $this->category();
        $lowerRanked = $this->product($category, [
            'name' => 'Lower Ranked Hood',
            'slug' => 'lower-ranked-hood',
            'sku' => 'FAR-LOWER-RANKED',
        ]);
        $bestSeller = $this->product($category, [
            'name' => 'Homepage Best Seller Hood',
            'slug' => 'homepage-best-seller-hood',
            'sku' => 'FAR-HOME-BEST-SELLER',
        ]);

        $order = $this->paidOrder();
        $this->addOrderItem($order, $lowerRanked, 1);
        $this->addOrderItem($order, $bestSeller, 5);

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('پرفروش‌ترین محصولات');

        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Lower Ranked Hood'),
            strpos($html, 'Homepage Best Seller Hood'),
            'The homepage best-seller section should use paid order quantities.'
        );
    }

    public function test_shop_applies_featured_new_and_installment_filters(): void
    {
        $category = $this->category();
        $featured = $this->product($category, [
            'name' => 'Featured Filter Hood',
            'slug' => 'featured-filter-hood',
            'sku' => 'FAR-FEATURED-FILTER',
            'is_featured' => true,
        ]);
        $new = $this->product($category, [
            'name' => 'New Filter Hood',
            'slug' => 'new-filter-hood',
            'sku' => 'FAR-NEW-FILTER',
            'is_new' => true,
        ]);
        $installment = $this->product($category, [
            'name' => 'Installment Filter Hood',
            'slug' => 'installment-filter-hood',
            'sku' => 'FAR-INSTALLMENT-FILTER',
            'installment_enabled' => true,
        ]);

        $this->get(route('shop.index', ['featured' => 1]))
            ->assertOk()
            ->assertSee($featured->name)
            ->assertDontSee($new->name)
            ->assertDontSee($installment->name);

        $this->get(route('shop.index', ['new' => 1]))
            ->assertOk()
            ->assertSee($new->name)
            ->assertDontSee($featured->name)
            ->assertDontSee($installment->name);

        $this->get(route('shop.index', ['installment' => 1]))
            ->assertOk()
            ->assertSee($installment->name)
            ->assertDontSee($featured->name)
            ->assertDontSee($new->name);
    }

    private function category(): Category
    {
        return Category::create([
            'name' => 'دسته آزمایشی هود',
            'slug' => 'test-hoods',
            'description' => 'دسته‌بندی برای آزمون فروشگاه',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function product(Category $category, array $attributes = []): Product
    {
        static $sequence = 0;
        $sequence++;

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Test Product '.$sequence,
            'slug' => 'test-product-'.$sequence,
            'sku' => 'FAR-TEST-PRODUCT-'.$sequence,
            'short_description' => 'محصول آزمایشی',
            'description' => 'توضیحات محصول آزمایشی',
            'price' => 25000000,
            'compare_at_price' => null,
            'stock' => 10,
            'status' => 'active',
            'is_featured' => false,
            'is_new' => false,
            'installment_enabled' => false,
        ], $attributes));
    }

    private function paidOrder(): Order
    {
        static $sequence = 0;
        $sequence++;

        return Order::create([
            'user_id' => null,
            'order_number' => 'FAR-TEST-PAID-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT),
            'status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'online',
            'payment_provider' => null,
            'subtotal' => 25000000,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 25000000,
            'first_name' => 'Test',
            'last_name' => 'Buyer',
            'phone' => '09123456789',
            'email' => 'buyer@example.test',
            'province' => 'Tehran',
            'city' => 'Tehran',
            'address' => 'Test address',
            'postal_code' => '1234567890',
        ]);
    }

    private function addOrderItem(Order $order, Product $product, int $quantity): void
    {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'total' => $product->price * $quantity,
        ]);
    }
    public function test_homepage_shows_four_best_sellers_and_four_new_arrivals_with_a_separate_four_image_mosaic(): void
    {
        $category = $this->category();

        for ($index = 1; $index <= 4; $index++) {
            $galleryProduct = $this->product($category, [
                'name' => 'Editorial Gallery Product '.$index,
                'slug' => 'editorial-gallery-product-'.$index,
                'sku' => 'FAR-EDITORIAL-GALLERY-'.$index,
                'show_in_home_gallery' => true,
            ]);

            ProductImage::create([
                'product_id' => $galleryProduct->id,
                'path' => 'https://cdn.example.test/editorial-gallery-'.$index.'.webp',
                'alt' => $galleryProduct->name,
                'sort_order' => 0,
                'is_primary' => true,
            ]);
        }

        $order = $this->paidOrder();

        for ($index = 1; $index <= 4; $index++) {
            $bestSeller = $this->product($category, [
                'name' => 'Homepage Bestseller '.$index,
                'slug' => 'homepage-bestseller-'.$index,
                'sku' => 'FAR-HOMEPAGE-BESTSELLER-'.$index,
            ]);
            $this->addOrderItem($order, $bestSeller, 10 - $index);

            $this->product($category, [
                'name' => 'Homepage New Arrival '.$index,
                'slug' => 'homepage-new-arrival-'.$index,
                'sku' => 'FAR-HOMEPAGE-NEW-'.$index,
                'is_new' => true,
            ]);
        }

        $response = $this->get(route('home'))->assertOk();
        $html = $response->getContent();

        $this->assertSame(4, substr_count($html, 'class="grid__image"'));
        $this->assertSame(8, substr_count($html, 'class="fz-product-card fz-reveal"'));

        for ($index = 1; $index <= 4; $index++) {
            $response->assertSee('Editorial Gallery Product '.$index)
                ->assertSee('Homepage Bestseller '.$index)
                ->assertSee('Homepage New Arrival '.$index);
        }
    }

}
