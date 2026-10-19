<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FarzinStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_successfully(): void
    {
        foreach (['/', '/shop', '/categories', '/about', '/contact', '/blog'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_homepage_and_blog_use_farzin_kitchen_product_content(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('فرزین')
            ->assertSee('هود آشپزخانه');

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('بلاگ فرزین')
            ->assertSee('انتخاب هود')
            ->assertSee('شناخت سینک')
            ->assertSee('چک‌لیست');
    }

    public function test_public_categories_only_show_active_categories(): void
    {
        $this->category(['name' => 'هود آشپزخانه', 'slug' => 'hoods']);
        $this->category([
            'name' => 'دسته‌بندی غیرفعال دمو',
            'slug' => 'inactive-demo',
            'is_active' => false,
        ]);

        $this->get(route('categories.index'))
            ->assertOk()
            ->assertSee('هود آشپزخانه')
            ->assertDontSee('دسته‌بندی غیرفعال دمو');
    }

    public function test_inactive_category_is_not_publicly_accessible(): void
    {
        $category = $this->category([
            'name' => 'دسته‌بندی قدیمی',
            'slug' => 'old-category',
            'is_active' => false,
        ]);

        $this->get(route('categories.show', $category->slug))
            ->assertNotFound();
    }

    public function test_shop_search_and_price_filters_exclude_unavailable_products(): void
    {
        $category = $this->category(['slug' => 'hoods']);
        $budget = $this->product($category, [
            'name' => 'Farzin Hood 60',
            'slug' => 'farzin-hood-60',
            'sku' => 'FAR-HOOD-60',
            'price' => 20000000,
            'stock' => 5,
        ]);
        $this->product($category, [
            'name' => 'Farzin Hood 90',
            'slug' => 'farzin-hood-90',
            'sku' => 'FAR-HOOD-90',
            'price' => 80000000,
            'stock' => 3,
        ]);
        $this->product($category, [
            'name' => 'Archived Hood',
            'slug' => 'archived-hood',
            'sku' => 'FAR-HOOD-OLD',
            'status' => 'archived',
            'price' => 1000000,
        ]);

        $response = $this->get(route('shop.index', [
            'search' => 'Hood',
            'max_price' => 50000000,
            'sort' => 'price_asc',
        ]));

        $response->assertOk()
            ->assertSee($budget->name)
            ->assertDontSee('Farzin Hood 90')
            ->assertDontSee('Archived Hood');
    }

    public function test_shop_rejects_invalid_price_ranges_and_sort_options(): void
    {
        $this->from(route('shop.index'))
            ->get(route('shop.index', [
                'min_price' => 50000000,
                'max_price' => 10000000,
            ]))
            ->assertRedirect(route('shop.index'))
            ->assertSessionHasErrors('max_price');

        $this->from(route('shop.index'))
            ->get(route('shop.index', ['sort' => 'untrusted-column']))
            ->assertRedirect(route('shop.index'))
            ->assertSessionHasErrors('sort');
    }

    public function test_only_active_products_have_public_detail_pages(): void
    {
        $category = $this->category(['slug' => 'hoods']);
        $active = $this->product($category, [
            'name' => 'Active Farzin Hood',
            'slug' => 'active-farzin-hood',
            'sku' => 'FAR-ACTIVE-1',
        ]);
        $archived = $this->product($category, [
            'name' => 'Archived Farzin Hood',
            'slug' => 'archived-farzin-hood',
            'sku' => 'FAR-ARCHIVED-1',
            'status' => 'archived',
        ]);

        $this->get(route('product.show', $active->slug))
            ->assertOk()
            ->assertSee($active->name);

        $this->get(route('product.show', $archived->slug))
            ->assertNotFound();
    }

    public function test_guest_can_add_product_to_cart_and_repeated_adds_merge(): void
    {
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Farzin Hood 60',
            'slug' => 'farzin-hood-60',
            'sku' => 'FAR-CART-1',
            'price' => 12500000,
            'stock' => 8,
        ]);

        $this->postJson(route('cart.add', $product), ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('cart_count', 2)
            ->assertJsonPath('subtotal', 25000000);

        $guestCart = Cart::query()
            ->whereNull('user_id')
            ->where('status', 'active')
            ->firstOrFail();

        $this->withCookie(config('session.cookie'), $guestCart->session_id)
            ->postJson(route('cart.add', $product), ['quantity' => 1])
            ->assertOk()
            ->assertJsonPath('cart_count', 3);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 12500000,
        ]);
    }

    public function test_cart_rejects_quantities_above_available_stock(): void
    {
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Limited Stock Sink',
            'slug' => 'limited-stock-sink',
            'sku' => 'FAR-STOCK-1',
            'stock' => 2,
        ]);

        $this->from(route('product.show', $product->slug))
            ->post(route('cart.add', $product), ['quantity' => 3])
            ->assertRedirect(route('product.show', $product->slug))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cart_item_updates_are_restricted_to_the_owning_customer(): void
    {
        $owner = $this->customer();
        $otherCustomer = $this->customer();
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Cart Test Hood',
            'slug' => 'cart-test-hood',
            'sku' => 'FAR-CART-OWNER',
            'stock' => 10,
        ]);
        $cart = Cart::create([
            'user_id' => $owner->id,
            'session_id' => null,
            'status' => 'active',
        ]);
        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
            'variant_key' => 'base',
        ]);

        $this->actingAs($otherCustomer)
            ->patchJson(route('cart.update', $item), ['quantity' => 2])
            ->assertForbidden();

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 1,
        ]);

        $this->actingAs($owner)
            ->patchJson(route('cart.update', $item), ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('cart_count', 2);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item->id,
            'quantity' => 2,
        ]);
    }

    public function test_guest_cart_is_merged_on_login_without_losing_variant_selection(): void
    {
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Variant Hood',
            'slug' => 'variant-hood',
            'sku' => 'FAR-VARIANT-CART',
            'price' => 15000000,
            'stock' => 10,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'type' => 'color',
            'name' => 'رنگ',
            'value' => 'black',
            'sku' => 'FAR-VARIANT-CART-BLACK',
            'price_adjustment' => 500000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->postJson(route('cart.add', $product), [
            'quantity' => 2,
            'variants' => ['color' => $variant->id],
        ])->assertOk()
            ->assertJsonPath('cart_count', 2);

        $guestCart = Cart::query()
            ->whereNull('user_id')
            ->where('status', 'active')
            ->firstOrFail();
        $guestItem = $guestCart->items()->firstOrFail();
        $selectedVariantKey = $guestItem->variant_key;
        $selectedVariantOptions = $guestItem->variant_options;

        $customer = $this->customer([
            'email' => 'cart-owner@farzin.test',
        ]);

        $this->withCookie(config('session.cookie'), $guestCart->session_id)
            ->post(route('login.store'), [
            'email' => $customer->email,
            'password' => 'password',
        ])->assertRedirect(route('account.index'));

        $userCart = Cart::query()
            ->where('user_id', $customer->id)
            ->where('status', 'active')
            ->firstOrFail();
        $userItem = $userCart->items()->firstOrFail();

        $this->assertSame($selectedVariantKey, $userItem->variant_key);
        $this->assertEquals($selectedVariantOptions, $userItem->variant_options);
        $this->assertSame(2, (int) $userItem->quantity);

        $this->assertDatabaseHas('carts', [
            'id' => $guestCart->id,
            'status' => 'converted',
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $guestCart->id,
        ]);
    }

    public function test_customer_registration_creates_a_customer_account(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Farzin',
            'last_name' => 'Customer',
            'email' => 'NEW.CUSTOMER@FARZIN.TEST',
            'phone' => '09123456789',
            'password' => 'FarzinPass123',
            'password_confirmation' => 'FarzinPass123',
            'terms' => '1',
        ])->assertRedirect(route('account.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new.customer@farzin.test',
            'role' => 'customer',
            'name' => 'Farzin Customer',
        ]);

        $user = User::query()->where('email', 'new.customer@farzin.test')->firstOrFail();
        $this->assertTrue(Hash::check('FarzinPass123', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_does_not_authenticate_the_user(): void
    {
        $user = $this->customer(['email' => 'customer@farzin.test']);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_and_customer_areas_enforce_role_boundaries(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->get(route('account.index'))->assertRedirect(route('login'));

        $this->actingAs($this->customer())
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get(route('account.index'))
            ->assertForbidden();

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk();
    }

    public function test_public_contact_form_persists_a_new_unread_message(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'مشتری فرزین',
            'phone' => '09123456789',
            'email' => 'buyer@farzin.test',
            'subject' => 'product',
            'message' => 'برای انتخاب هود مناسب آشپزخانه راهنمایی می‌خواهم.',
        ])->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'مشتری فرزین',
            'email' => 'buyer@farzin.test',
            'subject' => 'product',
            'status' => 'unread',
            'user_id' => null,
        ]);
    }

    public function test_admin_can_create_a_product_with_an_uploaded_image_visible_on_the_storefront(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $category = $this->category(['slug' => 'hoods']);

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'category_id' => $category->id,
                'name' => 'Farzin Hood 90',
                'slug' => 'farzin-hood-90',
                'sku' => 'FAR-HOOD-ADMIN-90',
                'short_description' => 'هود آشپزخانه با طراحی مدرن',
                'description' => 'مشخصات محصول از پنل مدیریت ثبت شده است.',
                'price' => '24500000',
                'stock' => 7,
                'status' => 'active',
                'is_featured' => '1',
                'is_new' => '1',
                'image' => UploadedFile::fake()->image('farzin-hood.png', 120, 80),
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('success');

        $product = Product::query()->where('slug', 'farzin-hood-90')->firstOrFail();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'active',
            'price' => 24500000,
            'stock' => 7,
        ]);

        $image = ProductImage::query()->where('product_id', $product->id)->firstOrFail();
        Storage::disk('public')->assertExists($image->path);

        $this->get(route('product.show', $product->slug))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_customer_can_place_order_and_cart_is_cleared_after_order_snapshot_is_saved(): void
    {
        $customer = $this->customer();
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Checkout Hood',
            'slug' => 'checkout-hood',
            'sku' => 'FAR-CHECKOUT-1',
            'price' => 3000000,
            'stock' => 5,
        ]);

        $cart = Cart::create([
            'user_id' => $customer->id,
            'session_id' => null,
            'status' => 'active',
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 3000000,
            'variant_key' => 'base',
        ]);

        $this->actingAs($customer)
            ->post(route('checkout.place'), [
                'first_name' => 'Farzin',
                'last_name' => 'Buyer',
                'phone' => '09123456789',
                'email' => 'buyer@farzin.test',
                'province' => 'Tehran',
                'city' => 'Tehran',
                'address' => ' خیابان نمونه، پلاک ۱',
                'postal_code' => '1234567890',
                'unit' => '2',
                'notes' => 'تماس قبل از ارسال',
            ])
            ->assertRedirect();

        $order = Order::query()->where('user_id', $customer->id)->firstOrFail();

        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame('online', $order->payment_method);
        $this->assertEquals(6000000, (float) $order->total);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 2,
            'unit_price' => 3000000,
            'total' => 6000000,
        ]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
    }

    public function test_customer_cannot_view_another_customers_payment_page(): void
    {
        $owner = $this->customer();
        $otherCustomer = $this->customer();
        $order = $this->order($owner);

        $this->actingAs($otherCustomer)
            ->get(route('checkout.payment', $order))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('checkout.payment', $order))
            ->assertOk();
    }

    public function test_unknown_payment_callback_gateway_is_rejected(): void
    {
        $this->post('/checkout/payment/callback/unknown-gateway', [])
            ->assertNotFound();
    }

    public function test_payment_callback_is_public_but_failed_gateway_response_never_marks_an_order_paid(): void
    {
        $service = \Mockery::mock(PaymentService::class);
        $service->shouldReceive('handleCallback')
            ->once()
            ->with('digipay', ['transaction_id' => 'test-callback'])
            ->andReturn([
                'success' => false,
                'message' => 'پرداخت تأیید نشد.',
            ]);
        $this->app->instance(PaymentService::class, $service);

        $this->post(route('checkout.payment.callback', ['gateway' => 'digipay']), [
            'transaction_id' => 'test-callback',
        ])->assertRedirect(route('home'))
            ->assertSessionHas('error', 'پرداخت تأیید نشد.');

        $this->assertGuest();
    }

    public function test_customer_can_add_and_remove_a_product_from_wishlist_idempotently(): void
    {
        $customer = $this->customer();
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Wishlist Hood',
            'slug' => 'wishlist-hood',
            'sku' => 'FAR-WISHLIST-1',
        ]);

        $this->actingAs($customer)
            ->from(route('product.show', $product->slug))
            ->post(route('account.wishlist.store', $product))
            ->assertRedirect(route('product.show', $product->slug));

        $this->from(route('product.show', $product->slug))
            ->post(route('account.wishlist.store', $product))
            ->assertRedirect(route('product.show', $product->slug));

        $this->assertDatabaseCount('wishlists', 1);

        $this->delete(route('account.wishlist.destroy', $product))
            ->assertRedirect();

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_default_address_is_unique_and_address_ownership_is_enforced(): void
    {
        $customer = $this->customer();
        $otherCustomer = $this->customer();

        $addressPayload = [
            'first_name' => 'Farzin',
            'last_name' => 'Buyer',
            'phone' => '09123456789',
            'province' => 'Tehran',
            'city' => 'Tehran',
            'address' => 'خیابان نمونه، پلاک ۱۲',
            'postal_code' => '1234567890',
            'is_default' => '1',
        ];

        $this->actingAs($customer)
            ->from(route('account.addresses.index'))
            ->post(route('account.addresses.store'), $addressPayload)
            ->assertRedirect(route('account.addresses.index'));

        $firstAddress = $customer->addresses()->firstOrFail();

        $this->from(route('account.addresses.index'))
            ->post(route('account.addresses.store'), array_merge($addressPayload, [
                'title' => 'آدرس دوم',
                'address' => 'خیابان دوم، پلاک ۲۴',
            ]))
            ->assertRedirect(route('account.addresses.index'));

        $firstAddress->refresh();
        $secondAddress = $customer->addresses()->where('title', 'آدرس دوم')->firstOrFail();

        $this->assertFalse($firstAddress->is_default);
        $this->assertTrue($secondAddress->is_default);

        $foreignAddress = $otherCustomer->addresses()->create([
            'first_name' => 'Other',
            'last_name' => 'Customer',
            'phone' => '09123456789',
            'province' => 'Tehran',
            'city' => 'Tehran',
            'address' => 'Foreign address 123',
            'postal_code' => '1234567890',
            'is_default' => true,
        ]);

        $this->put(route('account.addresses.update', $foreignAddress), $addressPayload)
            ->assertForbidden();

        $this->assertDatabaseHas('addresses', [
            'id' => $foreignAddress->id,
            'address' => 'Foreign address 123',
        ]);
    }

    public function test_admin_can_create_category_and_cannot_delete_a_category_with_products(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'هودهای جزیره‌ای',
                'slug' => 'island-hoods',
                'description' => 'هودهای مناسب نصب جزیره‌ای',
                'is_active' => '1',
                'sort_order' => 3,
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success');

        $category = Category::query()->where('slug', 'island-hoods')->firstOrFail();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'is_active' => true,
        ]);

        $this->product($category, [
            'name' => 'Island Hood',
            'slug' => 'island-hood',
            'sku' => 'FAR-ISLAND-1',
        ]);

        $this->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'slug' => 'island-hoods',
        ]);
    }

    public function test_admin_can_create_product_variant_and_cart_requires_a_valid_selection(): void
    {
        $admin = $this->admin();
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Variant Selector Hood',
            'slug' => 'variant-selector-hood',
            'sku' => 'FAR-VARIANT-SELECTOR',
            'price' => 10000000,
            'stock' => 10,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.product-variants.store'), [
                'product_id' => $product->id,
                'type' => 'color',
                'name' => 'رنگ',
                'value' => 'black',
                'sku' => 'FAR-VARIANT-SELECTOR-BLACK',
                'price_adjustment' => 1000000,
                'stock' => 4,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.product-variants.index', [
                'product_id' => $product->id,
            ]))
            ->assertSessionHas('success');

        $variant = $product->allVariants()->firstOrFail();

        $this->from(route('product.show', $product->slug))
            ->post(route('cart.add', $product), ['quantity' => 1])
            ->assertRedirect(route('product.show', $product->slug))
            ->assertSessionHasErrors('variants.color');

        $this->postJson(route('cart.add', $product), [
            'quantity' => 1,
            'variants' => ['color' => $variant->id],
        ])->assertOk()
            ->assertJsonPath('subtotal', 11000000);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'unit_price' => 11000000,
            'quantity' => 1,
        ]);
    }

    public function test_admin_product_image_upload_keeps_only_one_primary_image(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Multiple Images Hood',
            'slug' => 'multiple-images-hood',
            'sku' => 'FAR-MULTI-IMG',
        ]);
        $firstImage = ProductImage::create([
            'product_id' => $product->id,
            'path' => 'https://cdn.example.test/old-image.webp',
            'alt' => 'Old image',
            'sort_order' => 0,
            'is_primary' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.product-images.store'), [
                'product_id' => $product->id,
                'image' => UploadedFile::fake()->image('hood-angle.png', 100, 80),
                'alt' => 'تصویر جدید هود',
                'sort_order' => 1,
                'is_primary' => '1',
            ])
            ->assertRedirect(route('admin.product-images.index', [
                'product_id' => $product->id,
            ]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('product_images', [
            'id' => $firstImage->id,
            'is_primary' => false,
        ]);
        $this->assertSame(1, ProductImage::query()
            ->where('product_id', $product->id)
            ->where('is_primary', true)
            ->count());

        $newImage = ProductImage::query()
            ->where('product_id', $product->id)
            ->where('is_primary', true)
            ->firstOrFail();
        Storage::disk('public')->assertExists($newImage->path);
    }

    public function test_admin_can_upload_and_delete_media_from_the_media_library(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->create('farzin-catalog.pdf', 100, 'application/pdf'),
                'alt' => 'کاتالوگ فرزین',
                'description' => 'فایل کاتالوگ برای تست',
            ])
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('success');

        $media = \App\Models\Media::query()->firstOrFail();
        Storage::disk('public')->assertExists($media->path);
        $this->assertSame($admin->id, $media->user_id);

        $this->delete(route('admin.media.destroy', $media))
            ->assertRedirect(route('admin.media.index'))
            ->assertSessionHas('success');

        Storage::disk('public')->assertMissing($media->path);
        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_admin_can_update_order_status_and_invalid_status_is_rejected(): void
    {
        $admin = $this->admin();
        $order = $this->order($this->customer());

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), [
                'status' => 'processing',
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);

        $this->from(route('admin.orders.show', $order))
            ->patch(route('admin.orders.status', $order), [
                'status' => 'completed-without-validation',
            ])
            ->assertRedirect(route('admin.orders.show', $order))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }

    public function test_admin_can_read_and_delete_a_contact_message(): void
    {
        $admin = $this->admin();
        $message = ContactMessage::create([
            'name' => 'Farzin Buyer',
            'phone' => '09123456789',
            'email' => 'buyer@farzin.test',
            'subject' => 'product',
            'message' => 'Please provide more product details.',
            'status' => 'unread',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contact-messages.show', $message))
            ->assertOk();

        $this->assertDatabaseHas('contact_messages', [
            'id' => $message->id,
            'status' => 'read',
        ]);

        $this->delete(route('admin.contact-messages.destroy', $message))
            ->assertRedirect(route('admin.contact-messages.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('contact_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_checkout_refuses_to_create_an_order_when_cart_stock_is_insufficient(): void
    {
        $customer = $this->customer();
        $product = $this->product($this->category(['slug' => 'hoods']), [
            'name' => 'Low Stock Checkout Hood',
            'slug' => 'low-stock-checkout-hood',
            'sku' => 'FAR-LOW-STOCK',
            'price' => 3000000,
            'stock' => 2,
        ]);
        $cart = Cart::create([
            'user_id' => $customer->id,
            'session_id' => null,
            'status' => 'active',
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 3000000,
            'variant_key' => 'base',
        ]);

        $this->actingAs($customer)
            ->from(route('checkout.index'))
            ->post(route('checkout.place'), [
                'first_name' => 'Farzin',
                'last_name' => 'Buyer',
                'phone' => '09123456789',
                'email' => 'buyer@farzin.test',
                'province' => 'Tehran',
                'city' => 'Tehran',
                'address' => 'Test address with enough length',
                'postal_code' => '1234567890',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'quantity' => 3,
        ]);
    }

    private function category(array $attributes = []): Category
    {
        return Category::create(array_merge([
            'name' => 'هود آشپزخانه',
            'slug' => 'hoods',
            'description' => 'دسته‌بندی هودهای آشپزخانه فرزین',
            'is_active' => true,
            'sort_order' => 1,
        ], $attributes));
    }

    private function product(Category $category, array $attributes = []): Product
    {
        static $sequence = 0;
        $sequence++;

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Farzin Product '.$sequence,
            'slug' => 'farzin-product-'.$sequence,
            'sku' => 'FAR-TEST-'.$sequence,
            'short_description' => 'محصول آزمایشی فرزین',
            'description' => 'توضیحات آزمایشی محصول فرزین',
            'price' => 10000000,
            'compare_at_price' => null,
            'stock' => 10,
            'status' => 'active',
            'is_featured' => false,
            'is_new' => false,
            'installment_enabled' => false,
        ], $attributes));
    }

    private function customer(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'customer',
        ], $attributes));
    }

    private function admin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'admin',
        ], $attributes));
    }

    private function order(User $user, array $attributes = []): Order
    {
        static $sequence = 0;
        $sequence++;

        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'FAR-ORDER-'.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT),
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'online',
            'payment_provider' => null,
            'subtotal' => 1000000,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 1000000,
            'first_name' => 'Farzin',
            'last_name' => 'Buyer',
            'phone' => '09123456789',
            'email' => 'buyer@farzin.test',
            'province' => 'Tehran',
            'city' => 'Tehran',
            'address' => 'Test address 123',
            'postal_code' => '1234567890',
        ], $attributes));
    }
}
