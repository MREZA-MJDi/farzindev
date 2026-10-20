<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->select([
                'id',
                'name',
                'slug',
                'description',
                'sort_order',
            ])
            ->withCount([
                'products' => static fn ($query) => $query->active(),
            ])
            ->limit(8)
            ->get();

        /*
         * One bounded collection powers the public homepage.
         * Keep product images and their optional media fallback eager-loaded,
         * and load only one ordered image per product for the visual grid.
         */
        $products = Product::query()
            ->active()
            ->whereHas('images')
            ->with([
                'category:id,name,slug',
                'images' => static function ($query) {
                    $query
                        ->select([
                            'id',
                            'product_id',
                            'media_id',
                            'path',
                            'alt',
                            'sort_order',
                            'is_primary',
                        ])
                        ->orderByDesc('is_primary')
                        ->orderBy('sort_order')
                        ->limit(1);
                },
                'images.media',
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('updated_at')
            ->limit(24)
            ->get()
            ->filter(
                static fn (Product $product) => filled(
                    $product->images->first()?->url
                )
            )
            ->values();

        return view('home.index', [
            'categories' => $categories,
            'products' => $products,
        ]);
    }
}
