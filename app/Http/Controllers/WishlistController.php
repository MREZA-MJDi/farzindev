<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(): View
    {
        $products = Auth::user()
            ->wishlistProducts()
            ->with([
                'category',
                'images',
            ])
            ->latest('wishlists.created_at')
            ->paginate(12)
            ->withQueryString();

        return view('wishlist.index', [
            'products' => $products,
        ]);
    }

    public function store(Product $product): RedirectResponse
    {
        Auth::user()
            ->wishlists()
            ->firstOrCreate([
                'product_id' => $product->id,
            ]);

        return back()->with(
            'success',
            'محصول به علاقه‌مندی‌ها اضافه شد.'
        );
    }

    public function destroy(Product $product): RedirectResponse
    {
        Auth::user()
            ->wishlists()
            ->where('product_id', $product->id)
            ->delete();

        return back()->with(
            'success',
            'محصول از علاقه‌مندی‌ها حذف شد.'
        );
    }
}
