<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\ContactSetting;
use App\Models\Product;
use App\Models\StorefrontSetting;
use App\Services\StorefrontPageContent;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(StorefrontPageContent $pageContent): View
    {
        $copy = $pageContent->all();

        /*
         * The public catalogue must never surface fixture/placeholder products,
         * including in local and testing environments where Product::active()
         * intentionally permits seeded fixtures for other test scenarios.
         */
        $applyPublicProductRules = static function (Builder $query): void {
            $query
                ->active()
                ->whereNotNull('name')
                ->whereRaw("TRIM(name) <> ''")
                ->where('price', '>', 0)
                ->where(function (Builder $skuQuery): void {
                    $skuQuery->whereNull('sku')->orWhere(function (Builder $realSku): void {
                        $realSku
                            ->where('sku', 'not like', 'FZ-SEED-%')
                            ->where('sku', 'not like', 'LIV-%')
                            ->where('sku', 'not like', 'E2E-%');
                    });
                })
                ->where(function (Builder $titleQuery): void {
                    foreach (['%تست%', '%آزمایشی%', '%پیش‌نمایش%', '%نمونه%', '%placeholder%', '%preview%', '%sample%'] as $pattern) {
                        $titleQuery->where('name', 'not like', $pattern);
                    }
                })
                ->whereHas('category', fn (Builder $category): Builder => $category->where('is_active', true))
                ->whereHas('images');
        };

        $productRelations = [
            'category:id,name,slug',
            'variants:id,product_id,type,name,value,stock,is_active',
            'images' => fn ($query) => $query
                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(1),
            'images.media:id,path',
        ];

        $hasPublicImage = static fn (Product $product): bool => $product->images->contains(
            static fn ($image): bool => filled($image->url)
        );

        $isPurchasable = static function (Product $product): bool {
            $groups = $product->variants->groupBy('type');

            return $groups->isNotEmpty()
                ? $groups->every(static fn ($options): bool => $options->contains(
                    static fn ($option): bool => (int) $option->stock > 0
                ))
                : (int) $product->stock > 0;
        };

        $baseProducts = static function () use ($applyPublicProductRules, $productRelations): Builder {
            $query = Product::query();
            $applyPublicProductRules($query);

            return $query->with($productRelations);
        };

        // Categories are shown only when at least one real, priced, image-backed
        // active product belongs to them. A product image is used as the fallback.
        $categories = Category::query()
            ->active()
            ->whereHas('products', function (Builder $products) use ($applyPublicProductRules): void {
                $applyPublicProductRules($products);
            })
            ->with([
                'products' => function ($products) use ($applyPublicProductRules): void {
                    // Eager-load constraints receive the HasMany relation, not the Builder.
                    $query = $products->getQuery();
                    $applyPublicProductRules($query);
                    $query
                        ->with([
                            'images' => fn ($images) => $images
                                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                                ->orderByDesc('is_primary')
                                ->orderBy('sort_order')
                                ->limit(1),
                            'images.media:id,path',
                        ])
                        ->orderByDesc('updated_at')
                        ->limit(1);
                },
            ])
            ->withCount([
                'products' => function (Builder $products) use ($applyPublicProductRules): void {
                    $applyPublicProductRules($products);
                },
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        foreach ($categories as $category) {
            $fallbackImage = $category->products->first()?->images->first();
            $category->setAttribute(
                'home_category_image_url',
                $category->image_url ?: $fallbackImage?->url
            );
            $category->setAttribute(
                'home_category_image_srcset',
                $category->image_url ? $category->image_srcset : $fallbackImage?->responsive_srcset
            );
        }

        $discountProducts = $baseProducts()
            ->whereNotNull('compare_at_price')
            ->whereColumn('compare_at_price', '>', 'price')
            ->where('compare_at_price', '>', 0)
            ->orderByRaw('((compare_at_price - price) / compare_at_price) DESC')
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->filter($hasPublicImage)
            ->sortBy([
                [fn (Product $product): int => $isPurchasable($product) ? 0 : 1, 'asc'],
                [static fn (Product $product): float => -1 * (float) $product->discount_percentage, 'asc'],
            ])
            ->take(12)
            ->values();

        $newestProducts = $baseProducts()
            ->where('is_new', true)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(30)
            ->get()
            ->filter($hasPublicImage)
            ->sortBy(fn (Product $product): int => $isPurchasable($product) ? 0 : 1)
            ->take(12)
            ->values();

        $bestSellingProducts = $baseProducts()
            ->withSum([
                'orderItems as sold_quantity' => static function (Builder $items): void {
                    $items->whereHas('order', static function (Builder $orders): void {
                        $orders
                            ->where('payment_status', 'paid')
                            ->where('status', '!=', 'cancelled');
                    });
                },
            ], 'quantity')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('updated_at')
            ->limit(40)
            ->get()
            ->filter(fn (Product $product): bool => $hasPublicImage($product) && (int) $product->sold_quantity > 0)
            ->sort(static function (Product $first, Product $second) use ($isPurchasable): int {
                $availability = (int) $isPurchasable($second) <=> (int) $isPurchasable($first);

                return $availability !== 0
                    ? $availability
                    : ((int) $second->sold_quantity <=> (int) $first->sold_quantity);
            })
            ->take(12)
            ->values();

        // Retain the existing editorial selection from the admin product form.
        $selectedProducts = $baseProducts()
            ->where('show_in_home_gallery', true)
            ->withSum([
                'orderItems as sold_quantity' => static function (Builder $items): void {
                    $items->whereHas('order', static function (Builder $orders): void {
                        $orders
                            ->where('payment_status', 'paid')
                            ->where('status', '!=', 'cancelled');
                    });
                },
            ], 'quantity')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('is_featured')
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->filter($hasPublicImage)
            ->sortBy(fn (Product $product): int => $isPurchasable($product) ? 0 : 1)
            ->take(8)
            ->values();

        $installmentProduct = $baseProducts()
            ->where('installment_enabled', true)
            ->whereBetween('installment_cash_percent', [1, 99])
            ->where('installment_remainder_method', 'cheque')
            ->where('installment_cheque_count', '>', 0)
            ->where('installment_interval_months', '>', 0)
            ->get()
            ->first($isPurchasable);

        $storeSettings = StorefrontSetting::query()
            ->whereIn('key', [
                'shipping_text',
                'installation_text',
                'warranty_text',
                'shipping_flat_rate',
                'shipping_free_threshold',
            ])
            ->pluck('value', 'key')
            ->all();

        $benefits = [];
        $shippingText = trim((string) ($storeSettings['shipping_text'] ?? ''));
        $shippingThreshold = $storeSettings['shipping_free_threshold'] ?? null;
        $shippingRate = $storeSettings['shipping_flat_rate'] ?? null;

        if ($shippingText !== '') {
            $benefits[] = ['key' => 'shipping', 'title' => 'ارسال', 'description' => $shippingText];
        } elseif (filled($shippingThreshold) || filled($shippingRate)) {
            $details = [];
            if (is_numeric($shippingThreshold) && (float) $shippingThreshold > 0) {
                $details[] = 'ارسال رایگان برای خریدهای بالاتر از ' . \App\Helpers\PersianNumberHelper::format((float) $shippingThreshold) . ' تومان';
            }
            if (is_numeric($shippingRate)) {
                $details[] = 'هزینه ارسال ' . \App\Helpers\PersianNumberHelper::format((float) $shippingRate) . ' تومان';
            }
            if ($details !== []) {
                $benefits[] = ['key' => 'shipping', 'title' => 'ارسال', 'description' => implode(' · ', $details)];
            }
        }

        $installationText = trim((string) ($storeSettings['installation_text'] ?? ''));
        if ($installationText !== '') {
            $benefits[] = ['key' => 'installation', 'title' => 'نصب', 'description' => $installationText];
        }

        $warrantyText = trim((string) ($storeSettings['warranty_text'] ?? ''));
        if ($warrantyText === '') {
            $warrantyText = trim((string) ($copy['page_home_warranty_text'] ?? ''));
        }
        if ($warrantyText !== '') {
            $benefits[] = ['key' => 'warranty', 'title' => 'گارانتی', 'description' => $warrantyText];
        }

        if ($installmentProduct) {
            $cashPercent = (int) $installmentProduct->installment_cash_percent;
            $cashAmount = round((float) $installmentProduct->price * ($cashPercent / 100));
            $benefits[] = [
                'key' => 'installment',
                'title' => 'پرداخت اقساطی',
                'description' => $installmentProduct->name . ' · پیش‌پرداخت '
                    . \App\Helpers\PersianNumberHelper::format($cashPercent) . '٪ ('
                    . \App\Helpers\PersianNumberHelper::format($cashAmount) . ' تومان) · '
                    . \App\Helpers\PersianNumberHelper::format((int) $installmentProduct->installment_cheque_count)
                    . ' چک',
            ];
        }

        $resolveImageUrl = static function (?string $path): ?string {
            $path = trim((string) $path);
            if ($path === '') {
                return null;
            }

            if (Str::startsWith($path, ['http://', 'https://', '//'])) {
                return $path;
            }

            return Storage::disk('public')->url(
                preg_replace('#^(public/|storage/)#', '', ltrim($path, '/')) ?? $path
            );
        };

        $safeBannerUrl = static function (?string $target): string {
            $target = trim((string) $target);
            if ($target === '' || str_starts_with($target, '//')) {
                return route('shop.index');
            }

            if (str_starts_with($target, '/') && ! str_starts_with($target, '//')) {
                return $target;
            }

            $scheme = parse_url($target, PHP_URL_SCHEME);
            if (filter_var($target, FILTER_VALIDATE_URL) && in_array($scheme, ['http', 'https'], true)) {
                return $target;
            }

            return route('shop.index');
        };

        $bannerSlides = collect(range(1, 4))
            ->map(static function (int $index) use ($copy, $resolveImageUrl, $safeBannerUrl): ?array {
                $image = $resolveImageUrl($copy["page_home_banner_{$index}_image_url"] ?? null);
                $title = trim((string) ($copy["page_home_banner_{$index}_title"] ?? ''));

                if (! $image || $title === '') {
                    return null;
                }

                return [
                    'image' => $image,
                    'alt' => $title,
                    'eyebrow' => trim((string) ($copy["page_home_banner_{$index}_eyebrow"] ?? '')),
                    'title' => $title,
                    'description' => trim((string) ($copy["page_home_banner_{$index}_description"] ?? '')),
                    'button_label' => trim((string) ($copy["page_home_banner_{$index}_button_label"] ?? '')),
                    'button_url' => $safeBannerUrl($copy["page_home_banner_{$index}_button_url"] ?? null),
                ];
            })
            ->filter()
            ->values();

        if ($bannerSlides->isEmpty()) {
            $bannerSlides = collect([[
                'image' => $resolveImageUrl($copy['page_home_hero_image_url'] ?? null),
                'alt' => trim((string) ($copy['page_home_hero_title'] ?? 'تجهیزات آشپزخانه فرزین')),
                'eyebrow' => trim((string) ($copy['page_home_hero_eyebrow'] ?? '')),
                'title' => trim((string) ($copy['page_home_hero_title'] ?? 'هود و سینک آشپزخانه فرزین')),
                'description' => trim((string) ($copy['page_home_hero_description'] ?? '')),
                'button_label' => trim((string) ($copy['page_home_hero_primary_label'] ?? '')),
                'button_url' => route('shop.index'),
            ]]);
        }

        $blogPosts = BlogPost::query()
            ->published()
            ->indexable()
            ->whereNotNull('cover_image')
            ->where('cover_image', '<>', '')
            ->with(['author:id,name', 'terms:id,type,name,slug'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->filter(static fn (BlogPost $post): bool => filled($post->cover_image_url))
            ->take(3)
            ->values();

        $contactSetting = ContactSetting::query()
            ->where('is_active', true)
            ->first([
                'phone',
                'whatsapp',
                'email',
                'address',
                'instagram_url',
                'telegram_url',
            ]);

        return view('home.index', [
            'categories' => $categories,
            'bannerSlides' => $bannerSlides,
            'discountProducts' => $discountProducts,
            'newestProducts' => $newestProducts,
            'bestSellingProducts' => $bestSellingProducts,
            'selectedProducts' => $selectedProducts,
            'benefits' => collect($benefits),
            'blogPosts' => $blogPosts,
            'contactSetting' => $contactSetting,
            'enamadUrl' => trim((string) ($copy['page_home_enamad_url'] ?? '')),
        ]);
    }
}
