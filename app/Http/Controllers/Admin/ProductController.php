<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(): View
    {
        $products = Product::query()
            ->with('category')
            ->latest()
            ->paginate(15);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $uploadedImage = $request->file('image');
        unset($data['image']);

        $newPath = null;

        if ($uploadedImage) {
            $newPath = $uploadedImage->store('products/images', 'public');

            if (! $newPath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'ذخیره تصویر محصول انجام نشد؛ دوباره تلاش کنید.',
                    ]);
            }
        }

        try {
            $product = DB::transaction(function () use ($data, $newPath): Product {
                $product = Product::create($data);

                if ($newPath !== null) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $newPath,
                        'alt' => $product->name,
                        'sort_order' => 0,
                        'is_primary' => true,
                    ]);
                }

                return $product;
            });
        } catch (Throwable $e) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'محصول ایجاد شد. حالا می‌توانید رنگ‌ها و تصاویر هر رنگ را همین‌جا اضافه کنید.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): View
    {
        $product->load([
            'category',
            'images',
            'allVariants.images',
        ]);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load(['images', 'allVariants.images']);

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view(
            'admin.products.edit',
            compact('product', 'categories')
        );
    }

    /**
     * Update the specified product.
     */
    public function update(
        UpdateProductRequest $request,
        Product $product
    ): RedirectResponse {
        $data = $request->validated();
        $uploadedImage = $request->file('image');
        unset($data['image']);

        $newPath = null;

        if ($uploadedImage) {
            $newPath = $uploadedImage->store('products/images', 'public');

            if (! $newPath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'ذخیره تصویر محصول انجام نشد؛ تصویر قبلی حفظ شد.',
                    ]);
            }
        }

        $oldPath = null;

        try {
            DB::transaction(function () use ($product, $data, $newPath, &$oldPath): void {
                $lockedProduct = Product::query()
                    ->whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedProduct->update($data);

                if ($newPath === null) {
                    return;
                }

                $primaryImage = ProductImage::query()
                    ->where('product_id', $lockedProduct->id)
                    ->where('is_primary', true)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->first();

                if (! $primaryImage) {
                    $primaryImage = ProductImage::query()
                        ->where('product_id', $lockedProduct->id)
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->first();
                }

                $oldPath = $primaryImage?->path;

                if ($primaryImage) {
                    $primaryImage->update([
                        'path' => $newPath,
                        'alt' => $lockedProduct->name,
                        'is_primary' => true,
                    ]);

                    $lockedProduct->images()
                        ->where('id', '!=', $primaryImage->id)
                        ->update(['is_primary' => false]);
                } else {
                    ProductImage::create([
                        'product_id' => $lockedProduct->id,
                        'path' => $newPath,
                        'alt' => $lockedProduct->name,
                        'sort_order' => 0,
                        'is_primary' => true,
                    ]);
                }
            });
        } catch (Throwable $e) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        // Delete the previous image only after the database change commits.
        if (
            $newPath !== null
            && $oldPath
            && ! str_starts_with($oldPath, 'http://')
            && ! str_starts_with($oldPath, 'https://')
            && ! str_starts_with($oldPath, '//')
            && $oldPath !== $newPath
        ) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'محصول با موفقیت بروزرسانی شد.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'محصول با موفقیت حذف شد.');
    }
}
