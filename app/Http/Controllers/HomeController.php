<?php

namespace App\Http\Controllers;

use App\Helpers\PersianNumberHelper;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->with([
                'hoverMedia:id,path,mime_type,original_name,extension,size',
                // The category image fallback reads the latest product's first image.
                // Eager-load it for the bounded category set to avoid N+1 queries.
                'latestActiveProduct.images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'latestActiveProduct.images.media:id,path',
            ])
            ->withCount([
                'products' => fn ($query) => $query->active(),
            ])
            ->orderBy('sort_order')
            ->limit(4)
            ->get();

        // Keep the home page to eight distinct catalog products: two editorial images,
        // four bestsellers and two new arrivals. All others remain in the paginated shop.
        $catalogCount = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->count();

        // The mosaic is independently curated from the product editor.
        // Keep the result bounded and eager-load only one (primary) image per item.
        $galleryProducts = Product::query()
            ->active()
            ->where('show_in_home_gallery', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('images')
            ->with([
                'category',
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'images.media:id,path',
            ])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(2)
            ->get()
            ->filter(fn (Product $product) => $product->images->contains(
                fn ($image) => filled($image->url)
            ))
            ->values();

        $galleryProductIds = $galleryProducts->pluck('id')->values();

        // The architecture hero does not consume a catalogue product.
        $featuredProducts = Product::query()
            ->active()
            ->when(
                $galleryProductIds->isNotEmpty(),
                fn ($query) => $query->whereNotIn('id', $galleryProductIds)
            )
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with([
                'category',
                'images' => fn ($query) => $query
                    ->select([
                        'id',
                        'product_id',
                        'media_id',
                        'path',
                        'alt',
                        'sort_order',
                        'is_primary',
                    ])
                    ->limit(1),
                'images.media:id,path',
            ])
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
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get();

        $hasBestSellerSales = $featuredProducts->contains(
            fn (Product $product): bool => (int) ($product->sold_quantity ?? 0) > 0
        );

        $usedProductIds = $galleryProductIds
            ->merge($featuredProducts->pluck('id'))
            ->unique()
            ->values();

        $newProducts = Product::query()
            ->active()
            ->where('is_new', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when(
                $usedProductIds->isNotEmpty(),
                fn ($query) => $query->whereNotIn('id', $usedProductIds)
            )
            ->with([
                'category',
                'images' => fn ($query) => $query
                    ->select([
                        'id',
                        'product_id',
                        'media_id',
                        'path',
                        'alt',
                        'sort_order',
                        'is_primary',
                    ])
                    ->limit(1),
                'images.media:id,path',
            ])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(2)
            ->get();


        $demoImages = $galleryProducts
            ->map(function (Product $product, int $index): array {
                $image = $product->images->first(
                    fn ($candidate) => filled($candidate->url)
                );

                return [
                    'url' => $image->url,
                    'srcset' => $image->responsive_srcset,
                    'alt' => $image->alt ?: $product->name,
                    'product_url' => route('product.show', $product->slug),
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => PersianNumberHelper::format($product->price),
                    'product_stock' => (int) $product->stock,
                    'cart_url' => route('cart.add', $product),
                    'category_name' => $product->category?->name ?? 'فرزین',
                    'index' => $index,
                ];
            })
            ->values();

        return view('home.index', [
            'categories' => $categories,
            'demoImages' => $demoImages,
            'gridGroups' => $demoImages->chunk(6)->values(),
            'featuredProducts' => $featuredProducts,
            'hasBestSellerSales' => $hasBestSellerSales,
            'newProducts' => $newProducts,
            'catalogCount' => $catalogCount,
            'galleryCount' => $demoImages->count(),
        ]);
    }
}
