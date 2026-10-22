<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductImage\StoreProductImageRequest;
use App\Http\Requests\Admin\ProductImage\UpdateProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductImageController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        $productId = $request->integer('product_id');
        if ($productId > 0 && Product::query()->whereKey($productId)->exists()) {
            return redirect()->route('admin.products.edit', $productId);
        }

        return redirect()->route('admin.products.index');
    }

    public function create(Request $request): RedirectResponse
    {
        $productId = $request->integer('product_id');
        if ($productId > 0 && Product::query()->whereKey($productId)->exists()) {
            return redirect()->route('admin.products.edit', $productId);
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Add general product-gallery images from the product editor.
     * These photos are not tied to a single variant; color-specific photos
     * can be attached from the variant forms below the same editor.
     */
    public function storeForProduct(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:min_width=800,min_height=600',
                'max:20480',
            ],
            'alt' => ['nullable', 'string', 'max:255'],
        ], [
            'images.required' => 'حداقل یک تصویر برای گالری محصول انتخاب کنید.',
            'images.max' => 'در هر بار حداکثر ۱۰ تصویر می‌توانید اضافه کنید.',
            'images.*.image' => 'همهٔ فایل‌ها باید تصویر معتبر باشند.',
            'images.*.mimes' => 'فرمت تصویر باید JPG، PNG یا WEBP باشد.',
            'images.*.dimensions' => 'هر تصویر باید حداقل ۸۰۰ در ۶۰۰ پیکسل باشد.',
            'images.*.max' => 'حجم هر تصویر نباید بیشتر از ۲۰ مگابایت باشد.',
        ]);

        $storedPaths = [];

        try {
            foreach ($request->file('images', []) as $file) {
                $path = $file->store('products/images', 'public');

                if (! $path) {
                    throw new RuntimeException('ذخیره یکی از تصاویر گالری انجام نشد.');
                }

                $storedPaths[] = $path;
            }

            DB::transaction(function () use ($product, $validated, $storedPaths): void {
                $lockedProduct = Product::query()
                    ->whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $hasPrimary = ProductImage::query()
                    ->where('product_id', $lockedProduct->id)
                    ->where('is_primary', true)
                    ->exists();

                $nextSortOrder = ((int) ProductImage::query()
                    ->where('product_id', $lockedProduct->id)
                    ->max('sort_order')) + 1;

                foreach ($storedPaths as $index => $path) {
                    ProductImage::create([
                        'product_id' => $lockedProduct->id,
                        'path' => $path,
                        'alt' => trim((string) ($validated['alt'] ?? '')) !== ''
                            ? trim((string) $validated['alt'])
                            : trim($lockedProduct->name . ' - تصویر ' . ($index + 1)),
                        'sort_order' => $nextSortOrder + $index,
                        'is_primary' => ! $hasPrimary && $index === 0,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            if ($exception instanceof RuntimeException && $exception->getMessage() === 'ذخیره یکی از تصاویر گالری انجام نشد.') {
                return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', count($storedPaths) . ' تصویر به گالری محصول اضافه شد.');
    }

    public function store(StoreProductImageRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $file = $request->file('image');
        unset($data['image']);

        $path = $file->store('products/images', 'public');

        if (! $path) {
            return back()
                ->withInput()
                ->withErrors([
                    'image' => 'ذخیره تصویر محصول انجام نشد؛ دوباره تلاش کنید.',
                ]);
        }

        $data['path'] = $path;

        try {
            DB::transaction(function () use (&$data): void {
                $hasExistingImage = ProductImage::query()
                    ->where('product_id', $data['product_id'])
                    ->exists();

                $data['is_primary'] = $data['is_primary'] || ! $hasExistingImage;

                if ($data['is_primary']) {
                    ProductImage::query()
                        ->where('product_id', $data['product_id'])
                        ->update(['is_primary' => false]);
                }

                ProductImage::create($data);
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);

            throw $e;
        }

        return redirect()
            ->route('admin.product-images.index', [
                'product_id' => $data['product_id'],
            ])
            ->with('success', 'تصویر محصول با موفقیت اضافه شد.');
    }

    public function show(ProductImage $productImage): RedirectResponse
    {
        if (Product::query()->whereKey($productImage->product_id)->exists()) {
            return redirect()->route('admin.products.edit', $productImage->product_id);
        }

        return redirect()->route('admin.products.index');
    }

    public function edit(ProductImage $productImage): RedirectResponse
    {
        if (Product::query()->whereKey($productImage->product_id)->exists()) {
            return redirect()->route('admin.products.edit', $productImage->product_id);
        }

        return redirect()->route('admin.products.index');
    }

    public function update(
        UpdateProductImageRequest $request,
        ProductImage $productImage
    ): RedirectResponse {
        $data = $request->validated();

        $oldProductId = $productImage->product_id;
        $oldPath = $productImage->path;
        $oldWasPrimary = (bool) $productImage->is_primary;

        $newPath = null;

        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store(
                'products/images',
                'public'
            );

            if (! $newPath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'ذخیره تصویر محصول انجام نشد؛ تصویر قبلی حفظ شد.',
                    ]);
            }

            $data['path'] = $newPath;
        }

        try {
        DB::transaction(function () use (
            &$data,
            $productImage,
            $oldProductId,
            $oldWasPrimary
        ) {
            $newProductId = (int) $data['product_id'];
            $isMovingProduct = $oldProductId !== $newProductId;

            if ($data['is_primary']) {
                ProductImage::query()
                    ->where('product_id', $newProductId)
                    ->where('id', '!=', $productImage->id)
                    ->update(['is_primary' => false]);
            }

            $productImage->update($data);

            if (
                $oldWasPrimary
                && ($isMovingProduct || ! $data['is_primary'])
            ) {
                $replacement = ProductImage::query()
                    ->where('product_id', $oldProductId)
                    ->where('id', '!=', $productImage->id)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                if ($replacement) {
                    $replacement->update(['is_primary' => true]);
                }
            }

            if (
                $isMovingProduct
                && ! $data['is_primary']
                && ! ProductImage::query()
                    ->where('product_id', $newProductId)
                    ->where('is_primary', true)
                    ->exists()
            ) {
                $productImage->update(['is_primary' => true]);
            }
            });
        } catch (Throwable $e) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        if (
            $newPath !== null
            && $oldPath
            && $oldPath !== $newPath
            && ! Str::startsWith($oldPath, ['http://', 'https://', '//'])
        ) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('admin.product-images.index', [
                'product_id' => $productImage->product_id,
            ])
            ->with('success', 'تصویر محصول با موفقیت بروزرسانی شد.');
    }

    public function destroy(ProductImage $productImage): RedirectResponse
    {
        $productId = $productImage->product_id;
        $path = $productImage->path;
        $wasPrimary = (bool) $productImage->is_primary;

        DB::transaction(function () use (
            $productImage,
            $productId,
            $wasPrimary
        ) {
            $productImage->delete();

            if ($wasPrimary) {
                $replacement = ProductImage::query()
                    ->where('product_id', $productId)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->first();

                if ($replacement) {
                    $replacement->update(['is_primary' => true]);
                }
            }
        });

        if (
            $path
            && ! Str::startsWith($path, ['http://', 'https://', '//'])
        ) {
            Storage::disk('public')->delete($path);
        }

        return redirect()
            ->route('admin.product-images.index', [
                'product_id' => $productId,
            ])
            ->with('success', 'تصویر محصول با موفقیت حذف شد.');
    }
}
