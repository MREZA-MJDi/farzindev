<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ShopFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontSetting;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    public function index(ShopFilterRequest $request, ResponsiveImageService $images): View
    {
        $filters = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Product Query
        |--------------------------------------------------------------------------
        */

        $query = Product::query()
            ->with([
                'category',
                // Product cards only render the first image; avoid loading full galleries for 12 rows.
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'images.media:id,path',
                'variants:id,product_id,type,stock',
            ])
            ->active()
            ->publicCatalogue()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('is_active', true));

        /*
        |--------------------------------------------------------------------------
        | Category Filter - SLUG BASED
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['category'])) {
            $query->whereHas('category', function ($categoryQuery) use ($filters) {
                $categoryQuery
                    ->where('slug', $filters['category'])
                    ->where('is_active', true);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (! empty($filters['width_cm'])) {
            $query->where('width_cm', (int) $filters['width_cm']);
        }

        if (! empty($filters['installation_type'])) {
            $query->where('installation_type', $filters['installation_type']);
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%"));
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Minimum Price
        |--------------------------------------------------------------------------
        */

        if (
            isset($filters['min_price']) &&
            $filters['min_price'] !== null
        ) {
            $query->where(
                'price',
                '>=',
                $filters['min_price']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maximum Price
        |--------------------------------------------------------------------------
        */

        if (
            isset($filters['max_price']) &&
            $filters['max_price'] !== null
        ) {
            $query->where(
                'price',
                '<=',
                $filters['max_price']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Stock Filter
        |--------------------------------------------------------------------------
        */

        if (! empty($filters['in_stock'])) {
            $query->availableForPurchase();
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['new'])) {
            $query->where('is_new', true);
        }

        if (!empty($filters['installment'])) {
            $query->where('installment_enabled', true);
        }

        $hasBestSellerSales = Product::query()
            ->active()
            ->publicCatalogue()
            ->whereHas('orderItems', function ($orderItems): void {
                $orderItems->whereHas('order', function ($orders): void {
                    $orders
                        ->where('payment_status', 'paid')
                        ->where('status', '!=', 'cancelled');
                });
            })
            ->exists();

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        match (($filters['sort'] ?? 'newest') === 'popular' && ! $hasBestSellerSales
            ? 'newest'
            : ($filters['sort'] ?? 'newest')) {

            'price_asc' => $query->orderBy('price')->orderBy('id'),

            'price_desc' => $query->orderByDesc('price')->orderByDesc('id'),

            'name_asc' => $query->orderBy('name')->orderBy('id'),

            'popular' => $query
                ->withSum([
                    'orderItems as sold_quantity' => function ($orderItemQuery): void {
                        $orderItemQuery->whereHas('order', function ($orderQuery): void {
                            $orderQuery
                                ->where('payment_status', 'paid')
                                ->where('status', '!=', 'cancelled');
                        });
                    },
                ], 'quantity')
                ->orderByDesc('sold_quantity')
                ->orderByDesc('is_featured')
                ->orderByDesc('updated_at'),

            default => $query->orderByAvailability()->latest()->orderByDesc('id'),
        };

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $products = $query
            ->paginate(12)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categories = Category::query()
            ->active()
            ->whereHas('products', fn ($query) => $query->active()->publicCatalogue())
            ->withCount([
                'products' => fn ($query) => $query->active()->publicCatalogue(),
            ])
            ->withMin([
                'products as starting_price' => fn ($query) => $query->active()->publicCatalogue()->availableForPurchase(),
            ], 'price')
            // Category::active() adds its own sort order. Reset it so a
            // selected category outside the first 100 is promoted before cap.
            ->reorder()
            ->orderByRaw(
                'CASE WHEN slug = ? THEN 0 ELSE 1 END',
                [$filters['category'] ?? '']
            )
            ->orderBy('sort_order')
            ->limit(100)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

        $widthOptions = Product::query()
            ->active()
            ->publicCatalogue()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
            ->whereNotNull('width_cm')
            ->distinct()
            ->orderBy('width_cm')
            ->pluck('width_cm');

        $installationTypes = Product::query()
            ->active()
            ->publicCatalogue()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('is_active', true))
            ->whereNotNull('installation_type')
            ->where('installation_type', '!=', '')
            ->distinct()
            ->orderBy('installation_type')
            ->pluck('installation_type');

        $settings = StorefrontSetting::query()
            ->whereIn('key', [
                'shop_banner_image',
                'shop_banner_title',
                'shop_banner_subtitle',
            ])
            ->pluck('value', 'key');

        $bannerPath = $settings->get('shop_banner_image');

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'hasBestSellerSales' => $hasBestSellerSales,
            'widthOptions' => $widthOptions,
            'installationTypes' => $installationTypes,
            'shopBanner' => [
                'image_url' => $bannerPath ? Storage::disk('public')->url($bannerPath) : null,
                'image_srcset' => $bannerPath ? $images->srcset($bannerPath) : null,
                'title' => $settings->get('shop_banner_title') ?: 'فروشگاه فرزین',
                'subtitle' => $settings->get('shop_banner_subtitle') ?: 'هود و سینک آشپزخانه؛ انتخابی دقیق برای فضای شما.',
            ],
        ]);
    }
}
