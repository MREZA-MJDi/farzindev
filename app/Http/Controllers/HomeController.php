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
            ->with([
                'hoverMedia:id,path,mime_type,original_name,extension,size',
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

        $productRelations = [
            'category:id,name',
            'variants:id,product_id,stock',
            'images' => fn ($query) => $query
                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(1),
            'images.media:id,path',
        ];

        // One real catalogue product in the hero. No carousel or duplicate
        // editorial gallery is needed for the first decision.
        $heroProduct = Product::query()
            ->active()
            ->where('show_in_home_gallery', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->whereHas('images')
            ->with($productRelations)
            ->orderByDesc('is_featured')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(12)
            ->get()
            ->first(fn (Product $product) => $product->images->contains(
                fn ($image) => filled($image->url)
            ));

        // Rank by paid, non-cancelled order quantity. Featured and recently
        // updated products are tie-breakers when sales data is equal.
        $featuredProducts = Product::query()
            ->active()
            ->where('show_in_home_gallery', true)
            ->when($heroProduct, fn ($query) => $query->whereKeyNot($heroProduct->id))
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->with($productRelations)
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
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $hasBestSellerSales = $featuredProducts->contains(
            fn (Product $product): bool => (int) ($product->sold_quantity ?? 0) > 0
        );

        // Never label zero-sale catalogue items as best sellers. Keep the
        // six-product editorial fallback only when the store has no paid sales.
        if ($hasBestSellerSales) {
            $featuredProducts = $featuredProducts
                ->filter(fn (Product $product): bool => (int) ($product->sold_quantity ?? 0) > 0)
                ->values();
        }

        $blogPosts = BlogPost::query()
            ->published()
            ->indexable()
            ->with(['author:id,name', 'terms:id,type,name,slug'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->limit(3)
            ->get();

        return view('home.index', [
            'categories' => $categories,
            'heroProduct' => $heroProduct,
            'featuredProducts' => $featuredProducts,
            'hasBestSellerSales' => $hasBestSellerSales,
            'blogPosts' => $blogPosts,
        ]);
    }
}
