<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SeoRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->active()
            ->with('hoverMedia')
            ->withCount([
                'products' => fn ($query) => $query->active(),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $category = Category::query()->where('slug', $slug)->first();

        if (! $category) {
            $redirect = SeoRedirect::query()
                ->where('source_path', route('categories.show', $slug, false))
                ->where('is_active', true)
                ->first();

            if ($redirect) {
                return redirect()->to($redirect->target_url, $redirect->status_code);
            }

            abort(404);
        }

        // Publication belongs to the category itself. Do not make its URL
        // disappear merely because its products are still drafts or not added.
        abort_unless(
            Category::query()->active()->whereKey($category->getKey())->exists(),
            404
        );

        $products = $category
            ->products()
            ->active()
            ->whereHas('images', fn ($imageQuery) => $imageQuery
                ->whereNotNull('path')
                ->where('path', '<>', ''))
            ->with([
                'category',
                // Product cards only render the first image; avoid loading full galleries for 12 rows.
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'images.media:id,path',
                'variants:id,product_id,type,name,value,stock,price_adjustment,is_active',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('categories.show', [
            'category' => $category,
            'products' => $products,
        ]);
    }
}
