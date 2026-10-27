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
            ->limit(8)
            ->get();

        /*
         * The hero is a self-contained architectural drawing; it deliberately
         * does not select or load a featured product image. The catalogue
         * remains bounded, with one eager-loaded image per product for cards
         * and the lower product mosaic.
         */
        $catalogCount = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->count();

        $catalogProducts = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with([
                'category',
                // Cards and the product mosaic need only one image per product.
                // Avoid loading full galleries for up to 50 catalogue products.
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
            ->orderByDesc('is_featured')
            ->orderByDesc('is_new')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        // The architecture hero does not consume a catalogue product.
        $featuredProducts = $catalogProducts
            ->take(8)
            ->values();

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
            ->limit(30)
            ->get()
            ->filter(fn (Product $product) => $product->images->contains(
                fn ($image) => filled($image->url)
            ))
            ->values();

        $demoImages = $galleryProducts
            ->map(function (Product $product, int $index): array {
                $image = $product->images->first(
                    fn ($candidate) => filled($candidate->url)
                );

                return [
                    'url' => $image->url,
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
            'gridGroups' => $demoImages->chunk(9)->values(),
            'featuredProducts' => $featuredProducts,
            'newProducts' => $newProducts,
            'catalogCount' => $catalogCount,
            'galleryCount' => $demoImages->count(),
        ]);
    }
}
