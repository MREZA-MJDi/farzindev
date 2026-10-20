<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Admin\StorefrontSettingController as AdminStorefrontSettingController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/sitemap.xml', SitemapController::class)
    ->name('sitemap');

Route::get('/shop', [ShopController::class, 'index'])
    ->name('shop.index');

Route::view('/blog', 'blog.index')
    ->name('blog.index');


Route::get('/product/{slug}', [ProductController::class, 'show'])
    ->name('product.show');

Route::get('/categories', [CategoryController::class, 'index'])
    ->name('categories.index');

Route::get(
    '/categories/{category:slug}',
    [CategoryController::class, 'show']
)->name('categories.show');

Route::view('/about', 'about.index')
    ->name('about');


/*
|--------------------------------------------------------------------------
| Contact
|--------------------------------------------------------------------------
|
| Contact page and contact form are public.
| Guests are allowed to send messages.
|
*/

Route::get(
    '/contact',
    [ContactController::class, 'index']
)->name('contact');

Route::post(
    '/contact',
    [ContactController::class, 'store']
)->middleware('throttle:5,1')
    ->name('contact.store');


/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

Route::prefix('cart')
    ->name('cart.')
    ->group(function () {

        Route::get(
            '/',
            [CartController::class, 'index']
        )->name('index');

        Route::post(
            '/{product}/add',
            [CartController::class, 'add']
        )->name('add');

        // Guests can manage only items in the cart bound to their session.
        // Ownership is enforced in CartController for both guests and customers.
        Route::patch(
            '/item/{item}',
            [CartController::class, 'update']
        )->name('update');

        Route::delete(
            '/item/{item}',
            [CartController::class, 'remove']
        )->name('remove');

        Route::delete(
            '/',
            [CartController::class, 'clear']
        )->name('clear');
    });


/*
|--------------------------------------------------------------------------
| Guest Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get(
        '/login',
        [AuthController::class, 'showLogin']
    )->name('login');

    Route::post(
        '/login',
        [AuthController::class, 'login']
    )->middleware('throttle:5,1')
        ->name('login.store');

    Route::get(
        '/register',
        [AuthController::class, 'showRegister']
    )->name('register');

    Route::post(
        '/register',
        [AuthController::class, 'register']
    )->middleware('throttle:10,1')
        ->name('register.store');
});


/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    [AuthController::class, 'logout']
)
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| Payment Callback
|--------------------------------------------------------------------------
|
| IMPORTANT:
| External gateway callbacks MUST NOT require customer authentication.
|
*/

Route::match(
    ['GET', 'POST'],
    '/checkout/payment/callback/{gateway}',
    [CheckoutController::class, 'paymentCallback']
)->name('checkout.payment.callback');


/*
|--------------------------------------------------------------------------
| Customer Area
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'customer'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Checkout
    |--------------------------------------------------------------------------
    */

    Route::prefix('checkout')
        ->name('checkout.')
        ->group(function () {

            Route::get(
                '/',
                [CheckoutController::class, 'index']
            )->name('index');

            Route::post(
                '/',
                [CheckoutController::class, 'placeOrder']
            )->name('place');

            Route::get(
                '/payment/{order}',
                [CheckoutController::class, 'payment']
            )->name('payment');

            Route::post(
                '/payment/{order}/installment',
                [CheckoutController::class, 'startInstallmentPayment']
            )->name('payment.installment');

            Route::get(
                '/installment/{order}',
                [CheckoutController::class, 'installment']
            )->name('installment');

            Route::post(
                '/payment/{order}/livora-installment',
                [CheckoutController::class, 'startInternalInstallment']
            )->name('payment.livora-installment');
        });


    /*
    |--------------------------------------------------------------------------
    | Account
    |--------------------------------------------------------------------------
    */

    Route::prefix('account')
        ->name('account.')
        ->group(function () {

            Route::get(
                '/',
                [AccountController::class, 'index']
            )->name('index');


            /*
            | Profile
            */

            Route::get(
                '/profile',
                [AccountController::class, 'editProfile']
            )->name('profile.edit');

            Route::put(
                '/profile',
                [AccountController::class, 'updateProfile']
            )->name('profile.update');


            /*
            | Addresses
            */

            Route::get(
                '/addresses',
                [AccountController::class, 'addresses']
            )->name('addresses.index');

            Route::post(
                '/addresses',
                [AccountController::class, 'storeAddress']
            )->name('addresses.store');

            Route::put(
                '/addresses/{address}',
                [AccountController::class, 'updateAddress']
            )->name('addresses.update');

            Route::delete(
                '/addresses/{address}',
                [AccountController::class, 'deleteAddress']
            )->name('addresses.destroy');

            Route::patch(
                '/addresses/{address}/default',
                [AccountController::class, 'setDefaultAddress']
            )->name('addresses.default');


            /*
            | Orders
            */

            Route::get(
                '/orders',
                [OrderController::class, 'index']
            )->name('orders.index');

            Route::get(
                '/orders/{order}',
                [OrderController::class, 'show']
            )->name('orders.show');


            /*
            | Wishlist
            */

            Route::get(
                '/wishlist',
                [WishlistController::class, 'index']
            )->name('wishlist.index');

            Route::post(
                '/wishlist/{product}',
                [WishlistController::class, 'store']
            )->name('wishlist.store');

            Route::delete(
                '/wishlist/{product}',
                [WishlistController::class, 'destroy']
            )->name('wishlist.destroy');
        });

});


/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get(
            'storefront-settings',
            [AdminStorefrontSettingController::class, 'edit']
        )->name('storefront-settings.edit');

        Route::put(
            'storefront-settings',
            [AdminStorefrontSettingController::class, 'update']
        )->name('storefront-settings.update');

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/',
            [AdminDashboardController::class, 'index']
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'categories',
            AdminCategoryController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'products',
            AdminProductController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Product Images
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'product-images',
            AdminProductImageController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Product Variants
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'product-variants',
            AdminProductVariantController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Orders
        |--------------------------------------------------------------------------
        */

        Route::get(
            'orders',
            [AdminOrderController::class, 'index']
        )->name('orders.index');

        Route::get(
            'orders/{order}',
            [AdminOrderController::class, 'show']
        )->name('orders.show');

        Route::patch(
            'orders/{order}/status',
            [AdminOrderController::class, 'updateStatus']
        )->name('orders.status');


        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */

        Route::get(
            'customers',
            [AdminCustomerController::class, 'index']
        )->name('customers.index');

        Route::get(
            'customers/{customer}',
            [AdminCustomerController::class, 'show']
        )->name('customers.show');

        Route::get(
            'customers/{customer}/edit',
            [AdminCustomerController::class, 'edit']
        )->name('customers.edit');

        Route::put(
            'customers/{customer}',
            [AdminCustomerController::class, 'update']
        )->name('customers.update');


        /*
        |--------------------------------------------------------------------------
        | Media
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/media',
            [MediaController::class, 'index']
        )->name('media.index');

        Route::get(
            '/media/create',
            [MediaController::class, 'create']
        )->name('media.create');

        Route::post(
            '/media',
            [MediaController::class, 'store']
        )->name('media.store');

        Route::delete(
            '/media/{media}',
            [MediaController::class, 'destroy']
        )->name('media.destroy');


        /*
        |--------------------------------------------------------------------------
        | Contact Messages
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/contact-messages',
            [ContactMessageController::class, 'index']
        )->name('contact-messages.index');

        Route::get(
            '/contact-messages/{contactMessage}',
            [ContactMessageController::class, 'show']
        )->name('contact-messages.show');

        Route::patch(
            '/contact-messages/{contactMessage}/read',
            [ContactMessageController::class, 'markAsRead']
        )->name('contact-messages.read');

        Route::delete(
            '/contact-messages/{contactMessage}',
            [ContactMessageController::class, 'destroy']
        )->name('contact-messages.destroy');
    });
