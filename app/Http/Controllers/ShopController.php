<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ShopFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    public function index(ShopFilterRequest $request): View
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
            ])
            ->active()
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

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
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
            // Stock belongs to the parent product only when no variants are
            // configured. Variant-backed products must use option-level stock.
            $query->where(function ($stockQuery): void {
                $stockQuery
                    ->where(function ($baseStock): void {
                        $baseStock
                            ->whereDoesntHave('variants', fn ($variantQuery) => $variantQuery
                                ->where('is_active', true))
                            ->where('products.stock', '>', 0);
                    })
                    ->orWhereHas('variants', fn ($variantQuery) => $variantQuery
                        ->where('is_active', true)
                        ->where('stock', '>', 0));
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        match ($filters['sort'] ?? 'newest') {

            'price_asc' => $query->orderBy('price'),

            'price_desc' => $query->orderByDesc('price'),

            'name_asc' => $query->orderBy('name'),

            'popular' => $query
                ->orderByDesc('is_featured')
                ->latest(),

            default => $query->latest(),
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
            ->withCount([
                'products' => fn ($query) => $query->active(),
            ])
            ->orderBy('sort_order')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | View
        |--------------------------------------------------------------------------
        */

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
            'shopBanner' => [
                'image_url' => $bannerPath ? Storage::disk('public')->url($bannerPath) : null,
                'title' => $settings->get('shop_banner_title') ?: 'فروشگاه فرزین',
                'subtitle' => $settings->get('shop_banner_subtitle') ?: 'هود و سینک آشپزخانه؛ انتخابی دقیق برای فضای شما.',
            ],
        ]);
    }
}
