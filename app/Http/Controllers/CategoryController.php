<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->whereHas('products', fn ($query) => $query->active()->publicCatalogue()->availableForPurchase())
            ->with([
                'hoverMedia',
                'latestActiveProduct.images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'latestActiveProduct.images.media:id,path',
            ])
            ->withCount([
                'products' => fn ($query) => $query->active()->publicCatalogue()->availableForPurchase(),
            ])
            ->withMin([
                'products as starting_price' => fn ($query) => $query->active()->publicCatalogue()->availableForPurchase(),
            ], 'price')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(Category $category): View
    {
        abort_unless(
            $category->is_active
            && $category->products()->active()->publicCatalogue()->exists(),
            404
        );

        $category->loadMissing([
            'latestActiveProduct.images' => fn ($query) => $query
                ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                ->limit(1),
            'latestActiveProduct.images.media:id,path',
        ]);

        $products = $category
            ->products()
            ->active()
            ->publicCatalogue()
            ->with([
                'category',
                // Product cards only render the first image; avoid loading full galleries for 12 rows.
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'images.media:id,path',
                'variants:id,product_id,stock',
            ])
            ->orderByAvailability()
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
