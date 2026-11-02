<?php

namespace App\\Http\\Controllers;

use App\\Models\\BlogPost;
use App\\Models\\Category;
use App\\Models\\ContactSetting;
use App\\Models\\Product;
use App\\Models\\StorefrontSetting;
use Illuminate\\Database\\Eloquent\\Builder;
use Illuminate\\Contracts\\View\\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->whereHas('products', fn (Builder $query) => $query->active())
            ->with([
                'hoverMedia:id,path,mime_type,original_name,extension,size',
                'latestActiveProduct.images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'latestActiveProduct.images.media:id,path',
            ])
            ->withCount([
                'products' => fn (Builder $query) => $query->active(),
            ])
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        $productRelations = [
            'category:id,name,slug,description',
            'variants:id,product_id,type,name,value,stock',
            'images' => fn ($query) => $query
                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(1),
            'images.media:id,path',
        ];

        // Products shown as recommendations must have stock on the parent
        // record or on at least one active variant.
        $onlyInStock = static function (Builder $query): void {
            $query->where(function (Builder $stockQuery): void {
                $stockQuery
                    ->whereHas('variants', fn (Builder $variants) => $variants->where('stock', '>', 0))
                    ->orWhere(function (Builder $simpleProduct): void {
                        $simpleProduct
                            ->whereDoesntHave('variants')
                            ->where('stock', '>', 0);
                    });
            });
        };

        $heroProduct = Product::query()
            ->active()
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('images')
            ->tap($onlyInStock)
            ->with($productRelations)
            ->orderByDesc('is_featured')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->first(fn (Product $product) => $product->images->contains(
                fn ($image) => filled($image->url)
            ));

        $featuredProducts = Product::query()
            ->active()
            ->when($heroProduct, fn (Builder $query) => $query->whereKeyNot($heroProduct->id))
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('images')
            ->tap($onlyInStock)
            ->with($productRelations)
            ->withCount('variants')
            ->withSum('variants as active_variants_stock', 'stock')
            ->withSum([
                'orderItems as sold_quantity' => function (Builder $orderItemQuery): void {
                    $orderItemQuery->whereHas('order', function (Builder $orderQuery): void {
                        $orderQuery
                            ->where('payment_status', 'paid')
                            ->where('status', '!=', 'cancelled');
                    });
                },
            ], 'quantity')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('is_featured')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->filter(fn (Product $product) => $product->images->contains(
                fn ($image) => filled($image->url)
            ))
            ->values();

        $hasBestSellerSales = $featuredProducts->contains(
            fn (Product $product): bool => (int) ($product->sold_quantity ?? 0) > 0
        );

        if ($hasBestSellerSales) {
            $featuredProducts = $featuredProducts
                ->filter(fn (Product $product): bool => (int) ($product->sold_quantity ?? 0) > 0)
                ->values();
        }

        $installmentProduct = Product::query()
            ->active()
            ->where('installment_enabled', true)
            ->whereBetween('installment_cash_percent', [1, 99])
            ->where('installment_remainder_method', 'cheque')
            ->where('installment_cheque_count', '>', 0)
            ->where('installment_interval_months', '>', 0)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->tap($onlyInStock)
            ->with(['category:id,name,slug'])
            ->orderByDesc('is_featured')
            ->orderByDesc('updated_at')
            ->first();

        $blogPosts = BlogPost::query()
            ->published()
            ->indexable()
            ->with(['author:id,name', 'terms:id,type,name,slug'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        $shippingSettings = StorefrontSetting::query()
            ->whereIn('key', ['shipping_flat_rate', 'shipping_free_threshold'])
            ->pluck('value', 'key')
            ->all();

        $contactSetting = ContactSetting::query()
            ->where('is_active', true)
            ->first(['phone', 'whatsapp', 'email']);

        return view('home.index', [
            'categories' => $categories,
            'heroProduct' => $heroProduct,
            'featuredProducts' => $featuredProducts,
            'hasBestSellerSales' => $hasBestSellerSales,
            'installmentProduct' => $installmentProduct,
            'shippingSettings' => $shippingSettings,
            'contactSetting' => $contactSetting,
            'blogPosts' => $blogPosts,
        ]);
    }
}
