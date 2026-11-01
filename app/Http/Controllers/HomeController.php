<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->with([
                'latestActiveProduct.images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->limit(1),
                'latestActiveProduct.images.media:id,path',
            ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(3)
            ->get();

        $imageRelations = [
            'category:id,name',
            'images' => fn ($query) => $query
                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(1),
            'images.media:id,path',
        ];

        // The hero is a real-product carousel, never a remote illustration.
        $heroProducts = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('images')
            ->with($imageRelations)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(18)
            ->get()
            ->filter(fn (Product $product) => $product->images->contains(
                fn ($image) => filled($image->url)
            ))
            ->take(6)
            ->values();

        $heroProductIds = $heroProducts->pluck('id')->all();

        $catalogCount = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->count();

        // Rank from actual paid orders only; do not repeat the hero products below.
        $rankedProducts = Product::query()
            ->active()
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when($heroProductIds !== [], fn ($query) => $query->whereNotIn('id', $heroProductIds))
            ->with($imageRelations)
            ->withCount('variants')
            ->withSum('variants as active_variants_stock', 'stock')
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
            ->limit(30)
            ->get()
            ->values();

        $hasBestSellerSales = $rankedProducts->contains(
            fn (Product $product) => (int) ($product->sold_quantity ?? 0) > 0
        );

        if ($hasBestSellerSales) {
            $featuredProducts = $rankedProducts
                ->filter(fn (Product $product) => (int) ($product->sold_quantity ?? 0) > 0)
                ->take(4)
                ->values();
        } else {
            // When there are no paid orders yet, fall back to admin-curated products.
            $featuredProducts = Product::query()
                ->active()
                ->where('is_featured', true)
                ->whereHas('category', fn ($query) => $query->where('is_active', true))
                ->when($heroProductIds !== [], fn ($query) => $query->whereNotIn('id', $heroProductIds))
                ->with($imageRelations)
                ->withCount('variants')
                ->withSum('variants as active_variants_stock', 'stock')
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->limit(20)
                ->get()
                ->take(4)
                ->values();
        }

        $blogPosts = BlogPost::query()
            ->published()
            ->with(['author:id,name', 'terms:id,type,name,slug'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('home.index', [
            'categories' => $categories,
            'heroProducts' => $heroProducts,
            'featuredProducts' => $featuredProducts,
            'hasBestSellerSales' => $hasBestSellerSales,
            'blogPosts' => $blogPosts,
            'catalogCount' => $catalogCount,
        ]);
    }
}
