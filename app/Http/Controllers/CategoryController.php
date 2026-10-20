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
            ->withCount([
                'products' => fn ($query) => $query->active(),
            ])
            ->orderBy('sort_order')
            ->get();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $products = $category
            ->products()
            ->active()
            ->with([
                'category',
                'images.media',
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
