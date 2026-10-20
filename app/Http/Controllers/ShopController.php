<?php

namespace App\Http\Controllers;

use App\Http\Requests\Shop\ShopFilterRequest;
use App\Models\Category;
use App\Models\Product;
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
                'images',
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

        if (!empty($filters['in_stock'])) {
            $query->inStock();
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

        return view('shop.index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
