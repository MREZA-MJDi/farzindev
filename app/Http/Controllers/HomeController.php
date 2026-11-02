<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContactSetting;
use App\Models\Product;
use App\Models\StorefrontSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Only show categories that currently contain at least one active product.
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

        // The lead hero pick is purchasable; the selected-products grid below
        // may include sold-out items, but always sorts purchasable ones first.
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

        $isPurchasable = static function (Product $product): bool {
            $groups = $product->variants->groupBy('type');

            return $groups->isNotEmpty()
                ? $groups->every(fn ($options) => $options->contains(
                    fn ($option) => (int) $option->stock > 0
                ))
                : (int) $product->stock > 0;
        };

        $hasPublicImage = static fn (Product $product): bool => $product->images->contains(
            fn ($image) => filled($image->url)
        );

        $featuredProducts = Product::query()
            ->active()
            ->where('show_in_home_gallery', true)
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->whereHas('images')
            ->with($productRelations)
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
            ->limit(60)
            ->get()
            ->filter(fn (Product $product) => $hasPublicImage($product))
            ->sortByDesc(fn (Product $product) => $isPurchasable($product))
            ->take(8)
            ->values();

        $installmentProduct = Product::query()
            ->active()
            ->where('installment_enabled', true)
            ->whereBetween('installment_cash_percent', [1, 99])
            ->where('installment_remainder_method', 'cheque')
            ->where('installment_cheque_count', '>', 0)
            ->where('installment_interval_months', '>', 0)
            ->whereDoesntHave('variants', fn (Builder $query) => $query->where('price_adjustment', '!=', 0))
            ->whereHas('category', fn (Builder $query) => $query->where('is_active', true))
            ->when(true, $onlyInStock)
            ->with(['category:id,name,slug', 'variants:id,product_id,type,stock'])
            ->orderByDesc('is_featured')
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->first(fn (Product $product) => $isPurchasable($product));

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
            ->first(['phone', 'whatsapp', 'email', 'address']);

        // Guide links are data-driven from real, active catalogue categories.
        // The view must never reference an undefined optional collection.
        $homeGuides = $categories
            ->filter(fn (Category $category): bool => filled(trim((string) $category->description)))
            ->take(2)
            ->values();

        return view('home.index', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'installmentProduct' => $installmentProduct,
            'shippingSettings' => $shippingSettings,
            'contactSetting' => $contactSetting,
            'blogPosts' => $blogPosts,
            'homeGuides' => $homeGuides,
        ]);
    }
}
