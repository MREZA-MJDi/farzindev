<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ShopFilterRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorefrontSetting;
use App\Services\Media\ResponsiveImageService;
use App\Support\ProductPurchaseRules;
use App\Support\PersianDigits;
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
                // Load ordered image candidates so cards can skip missing files and use the next available image.
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->limit(5),
                'images.media:id,path,disk',
                'variants:id,product_id,type,name,value,stock,price_adjustment,is_active',
            ])
            ->active()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                ->where('is_active', true))
            ->whereHas('images', fn ($imageQuery) => $imageQuery
                ->where(function ($imageSource): void {
                    $imageSource
                        ->where(fn ($path) => $path->whereNotNull('path')->where('path', '<>', ''))
                        ->orWhereHas('media');
                }));

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

        $selectedCategory = ! empty($filters['category'])
            ? Category::query()->visibleInStorefront()->where('slug', $filters['category'])->first()
            : null;
        $filterableAttributes = $selectedCategory
            ? $selectedCategory->attributes()->wherePivot('is_filterable', true)->with('options')->get()
            : collect();

        foreach (($filters['attributes'] ?? []) as $attributeId => $selectedValue) {
            $attribute = $filterableAttributes->firstWhere('id', (int) $attributeId);
            if (! $attribute || $selectedValue === '' || $selectedValue === null) {
                continue;
            }

            if ($attribute->type === 'number' && is_array($selectedValue)) {
                $minimum = $selectedValue['min'] ?? null;
                $maximum = $selectedValue['max'] ?? null;
                if ((is_numeric($minimum) || is_numeric($maximum)) && (! is_numeric($minimum) || ! is_numeric($maximum) || (float) $minimum <= (float) $maximum)) {
                    $query->whereHas('attributeValues', function ($values) use ($attribute, $minimum, $maximum): void {
                        $values->where('attribute_id', $attribute->id);
                        if (is_numeric($minimum)) $values->where('value_number', '>=', $minimum);
                        if (is_numeric($maximum)) $values->where('value_number', '<=', $maximum);
                    });
                }
                continue;
            }

            if (in_array($attribute->type, ['select', 'multiselect'], true)) {
                $optionIds = array_map('intval', (array) $selectedValue);
                $validIds = $attribute->options->whereIn('id', $optionIds)->pluck('id')->all();
                if ($validIds !== []) {
                    $query->whereHas('attributeValues', fn ($values) => $values
                        ->where('attribute_id', $attribute->id)
                        ->whereIn('option_id', $validIds));
                }
                continue;
            }

            if ($attribute->type === 'boolean' && in_array((string) $selectedValue, ['0', '1'], true)) {
                $query->whereHas('attributeValues', fn ($values) => $values
                    ->where('attribute_id', $attribute->id)
                    ->where('value_boolean', (int) $selectedValue));
                continue;
            }

            if (in_array($attribute->type, ['text', 'textarea'], true) && is_scalar($selectedValue)) {
                $query->whereHas('attributeValues', fn ($values) => $values
                    ->where('attribute_id', $attribute->id)
                    ->where('value_text', 'like', '%' . addcslashes((string) $selectedValue, '%_') . '%'));
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['search'])) {
            $search = trim(str_replace(['ي', 'ى', 'ك', "\u{200C}", "\u{200D}", 'ـ'], ['ی', 'ی', 'ک', ' ', ' ', ''], PersianDigits::toEnglish($filters['search']) ?? $filters['search']));

            $query->where(function ($productQuery) use ($search) {
                $productQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('variants', fn ($variantQuery) => $variantQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"))
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
            ProductPurchaseRules::applyAvailableQuery($query);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['new'])) {
            $query->where('is_new', true);
        }

        if (!empty($filters['installment'])) {
            ProductPurchaseRules::applyInstallmentQuery($query);
        }

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */

        match ($filters['sort'] ?? 'available_first') {

            'available_first' => $query
                ->orderByRaw("CASE WHEN (NOT EXISTS (SELECT 1 FROM product_variants AS any_active_variants WHERE any_active_variants.product_id = products.id AND any_active_variants.is_active = 1) AND products.stock > 0) OR (EXISTS (SELECT 1 FROM product_variants AS active_variants WHERE active_variants.product_id = products.id AND active_variants.is_active = 1) AND NOT EXISTS (SELECT 1 FROM product_variants AS required_variants WHERE required_variants.product_id = products.id AND required_variants.is_active = 1 GROUP BY required_variants.type HAVING SUM(CASE WHEN required_variants.stock > 0 THEN 1 ELSE 0 END) = 0)) THEN 0 ELSE 1 END")
                ->orderByDesc('products.updated_at')
                ->orderByDesc('products.id'),

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

            default => $query->latest()->orderByDesc('id'),
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
            ->visibleInStorefront()
            ->withCount([
                'products' => fn ($query) => $query->active(),
            ])
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

        $settings = StorefrontSetting::query()
            ->whereIn('key', [
                'shop_banner_image',
                'shop_banner_title',
                'shop_banner_subtitle',
            ])
            ->pluck('value', 'key');

        $bannerPath = $settings->get('shop_banner_image');
        $publicDisk = Storage::disk('public');
        $bannerAvailable = filled($bannerPath) && $publicDisk->exists($bannerPath);

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'filterableAttributes' => $filterableAttributes,
            'shopBanner' => [
                'image_url' => $bannerAvailable ? $publicDisk->url($bannerPath) : null,
                'image_srcset' => $bannerAvailable ? $images->srcset($bannerPath) : null,
                'title' => $settings->get('shop_banner_title') ?: 'فروشگاه فرجی‌هوم',
                'subtitle' => $settings->get('shop_banner_subtitle') ?: 'هود، اجاق گاز صفحه‌ای و سینک؛ مشخصات مدل‌ها را بررسی و مقایسه کنید.',
            ],
        ]);
    }
}
