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
            ->withCount([
                'products' => fn ($query) => $query->active(),
            ])
            ->with(['latestActiveProduct.images.media'])
            ->orderBy('sort_order')
            ->limit(8)
            ->get();

        /*
         * The independent homepage is fed from the same catalogue as the
         * shop. It uses a bounded query and excludes products without a
         * resolvable image. The hero, cards and 3D mosaic have separate,
         * non-overlapping products so visitors do not see repeated items.
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
                'images.media',
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('is_new')
            ->orderByDesc('updated_at')
            ->limit(50)
            ->get();

        // Prefer a real-image product for the visual hero, but never hide
        // otherwise-active products from the catalogue when an image is absent.
        $heroProduct = $catalogProducts->first(
            fn (Product $product) => $product->images->contains(
                fn ($image) => filled($image->url)
            )
        );

        $featuredProducts = $catalogProducts
            ->reject(fn (Product $product) => $product->id === $heroProduct?->id)
            ->take(8)
            ->values();

        // Keep the first product discoverable in both hero and product-card
        // contexts when it is the only active catalogue item.
        if ($catalogProducts->count() === 1 && $featuredProducts->isEmpty()) {
            $featuredProducts = $catalogProducts->take(1)->values();
        }

        $usedProductIds = $featuredProducts
            ->pluck('id')
            ->push($heroProduct?->id)
            ->filter()
            ->values();

        $newProducts = $catalogProducts
            ->filter(fn (Product $product) => (bool) $product->is_new)
            ->reject(fn (Product $product) => $usedProductIds->contains($product->id))
            ->take(8)
            ->values();

        $usedProductIds = $usedProductIds
            ->merge($newProducts->pluck('id'))
            ->unique()
            ->values();

        $galleryProducts = $catalogProducts
            ->filter(fn (Product $product) => $product->images->contains(
                fn ($image) => filled($image->url)
            ))
            ->reject(fn (Product $product) => $usedProductIds->contains($product->id))
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
            'heroProduct' => $heroProduct,
            'demoImages' => $demoImages,
            'gridGroups' => $demoImages->chunk(9)->values(),
            'featuredProducts' => $featuredProducts,
            'newProducts' => $newProducts,
            'catalogCount' => $catalogCount,
            'galleryCount' => $demoImages->count(),
        ]);
    }
}
