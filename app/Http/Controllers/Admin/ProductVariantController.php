<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Services\Catalog\ProductMediaLibrary;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of product variants.
     */
    public function index(Request $request): RedirectResponse
    {
        $productId = $request->integer('product_id');
        if ($productId > 0 && Product::query()->whereKey($productId)->exists()) {
            return redirect()->route('admin.products.edit', $productId);
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Show the form for creating a new product variant.
     */
    public function create(Request $request): RedirectResponse
    {
        $productId = $request->integer('product_id');
        if ($productId > 0 && Product::query()->whereKey($productId)->exists()) {
            return redirect()->route('admin.products.edit', $productId);
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Store a newly created product variant.
     */
    public function store(
        StoreProductVariantRequest $request,
        ProductMediaLibrary $mediaLibrary,
        ResponsiveImageService $images
    ): RedirectResponse {
        $data = $request->validated();

        $imageIds = $data['image_ids'] ?? [];
        $mediaIds = $data['media_ids'] ?? [];
        $uploads = $request->file('images', []);
        unset($data['image_ids'], $data['media_ids'], $data['images']);

        $productId = (int) $data['product_id'];
        $storedPaths = [];
        $newImageIds = [];

        foreach ($uploads as $upload) {
            try {
                $path = $images->store($upload, 'products/images');
            } catch (Throwable $exception) {
                foreach ($storedPaths as $storedPath) {
                    $images->delete($storedPath);
                }

                $requestId = (string) $request->attributes->get('request_id', 'unavailable');
                Log::error('Variant image upload failed.', [
                    'request_id' => $requestId,
                    'product_id' => $productId,
                    'exception' => $exception,
                ]);

                return back()->withInput()->withErrors([
                    'images' => 'آپلود تصویر تنوع انجام نشد؛ فضای دیسک و محدودیت آپلود را بررسی کنید. کد پیگیری: ' . $requestId,
                ]);
            }

            if (! $path) {
                foreach ($storedPaths as $storedPath) {
                    $images->delete($storedPath);
                }

                return back()->withInput()->withErrors([
                    'images' => 'ذخیره یکی از تصاویر تنوع انجام نشد؛ فضای دیسک و دسترسی نوشتن را بررسی کنید.',
                ]);
            }
            $storedPaths[] = $path;
        }

        try {
            DB::transaction(function () use ($data, $imageIds, $mediaIds, $productId, $storedPaths, &$newImageIds, $mediaLibrary): void {
                // Serialize variant changes with checkout's product/stock locks.
                $product = Product::query()
                    ->whereKey($productId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $productVariant = ProductVariant::create($data);
                $nextSort = ((int) ProductImage::query()
                    ->where('product_id', $productId)
                    ->max('sort_order')) + 1;

                foreach ($storedPaths as $offset => $path) {
                    $image = ProductImage::create([
                        'product_id' => $productId,
                        'path' => $path,
                        'alt' => trim($data['name'] . ' ' . $data['value']),
                        'sort_order' => $nextSort + $offset,
                        'is_primary' => false,
                    ]);
                    $newImageIds[] = $image->id;
                }

                $libraryImageIds = $mediaLibrary->attachToProduct(
                    $productId,
                    $mediaIds,
                    trim($data['name'] . ' ' . $data['value'])
                );
                $this->syncVariantImages($productVariant, $imageIds, array_merge($newImageIds, $libraryImageIds));
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            $requestId = (string) $request->attributes->get('request_id', 'unavailable');
            Log::error('Variant creation failed.', [
                'request_id' => $requestId,
                'product_id' => $productId,
                'media_ids' => $mediaIds,
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'images' => 'ذخیره تنوع یا تصاویر آن انجام نشد؛ گالری قبلی حفظ شد. اگر مشکل تکرار شد، لاگ Laravel را با کد پیگیری بررسی کنید: ' . $requestId,
            ]);
        }

        if ($request->boolean('return_to_product')) {
            return redirect()
                ->route('admin.products.edit', $productId)
                ->with('success', 'تنوع و تصاویر وابسته به آن ذخیره شدند.');
        }

        return redirect()
            ->route('admin.product-variants.index', ['product_id' => $productId])
            ->with('success', 'تنوع محصول با موفقیت ایجاد شد.');
    }

    /**
     * Display the specified product variant.
     */
    public function show(ProductVariant $productVariant): RedirectResponse
    {
        if (Product::query()->whereKey($productVariant->product_id)->exists()) {
            return redirect()->route('admin.products.edit', $productVariant->product_id);
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Show the form for editing the specified product variant.
     */
    public function edit(ProductVariant $productVariant): RedirectResponse
    {
        if (Product::query()->whereKey($productVariant->product_id)->exists()) {
            return redirect()->route('admin.products.edit', $productVariant->product_id);
        }

        return redirect()->route('admin.products.index');
    }

    /**
     * Update the specified product variant.
     */
    public function update(
        UpdateProductVariantRequest $request,
        ProductVariant $productVariant,
        ProductMediaLibrary $mediaLibrary,
        ResponsiveImageService $images
    ): RedirectResponse {
        $data = $request->validated();

        $imageIds = $data['image_ids'] ?? [];
        $mediaIds = $data['media_ids'] ?? [];
        $uploads = $request->file('images', []);
        unset($data['image_ids'], $data['media_ids'], $data['images']);

        $oldProductId = (int) $productVariant->product_id;
        $newProductId = (int) $data['product_id'];
        $storedPaths = [];
        $newImageIds = [];

        foreach ($uploads as $upload) {
            try {
                $path = $images->store($upload, 'products/images');
            } catch (Throwable $exception) {
                foreach ($storedPaths as $storedPath) {
                    Storage::disk('public')->delete($storedPath);
                }

                $requestId = (string) $request->attributes->get('request_id', 'unavailable');
                Log::error('Variant image replacement upload failed.', [
                    'request_id' => $requestId,
                    'variant_id' => $productVariant->id,
                    'product_id' => $newProductId,
                    'exception' => $exception,
                ]);

                return back()->withInput()->withErrors([
                    'images' => 'آپلود تصویر تنوع انجام نشد؛ تصاویر قبلی حفظ شدند. کد پیگیری: ' . $requestId,
                ]);
            }

            if (! $path) {
                foreach ($storedPaths as $storedPath) {
                    Storage::disk('public')->delete($storedPath);
                }

                return back()->withInput()->withErrors([
                    'images' => 'ذخیره یکی از تصاویر تنوع انجام نشد؛ فضای دیسک و دسترسی نوشتن را بررسی کنید. تصاویر قبلی حفظ شدند.',
                ]);
            }
            $storedPaths[] = $path;
        }

        try {
            DB::transaction(function () use (
                $productVariant,
                $data,
                $imageIds,
                $oldProductId,
                $newProductId,
                $storedPaths,
                $mediaIds,
                &$newImageIds,
                $mediaLibrary
            ): void {
                // Lock product rows in a deterministic order to reduce deadlocks.
                $lockedProducts = Product::query()
                    ->whereIn('id', array_values(array_unique([
                        $oldProductId,
                        $newProductId,
                    ])))
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if (! $lockedProducts->has($newProductId)) {
                    abort(404);
                }

                $lockedVariant = ProductVariant::query()
                    ->whereKey($productVariant->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedVariant->update($data);
                $nextSort = ((int) ProductImage::query()
                    ->where('product_id', $newProductId)
                    ->max('sort_order')) + 1;

                foreach ($storedPaths as $offset => $path) {
                    $image = ProductImage::create([
                        'product_id' => $newProductId,
                        'path' => $path,
                        'alt' => trim($data['name'] . ' ' . $data['value']),
                        'sort_order' => $nextSort + $offset,
                        'is_primary' => false,
                    ]);
                    $newImageIds[] = $image->id;
                }

                $libraryImageIds = $mediaLibrary->attachToProduct(
                    $newProductId,
                    $mediaIds,
                    trim($data['name'] . ' ' . $data['value'])
                );
                $this->syncVariantImages($lockedVariant, $imageIds, array_merge($newImageIds, $libraryImageIds));
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            $requestId = (string) $request->attributes->get('request_id', 'unavailable');
            Log::error('Variant update failed.', [
                'request_id' => $requestId,
                'variant_id' => $productVariant->id,
                'old_product_id' => $oldProductId,
                'new_product_id' => $newProductId,
                'media_ids' => $mediaIds,
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'images' => 'ذخیره تنوع یا تصاویر آن انجام نشد؛ تغییرات قبلی حفظ شدند. کد پیگیری برای بررسی لاگ: ' . $requestId,
            ]);
        }

        if ($request->boolean('return_to_product')) {
            return redirect()
                ->route('admin.products.edit', $newProductId)
                ->with('success', 'تغییرات تنوع و تصاویر ذخیره شدند.');
        }

        return redirect()
            ->route('admin.product-variants.index', ['product_id' => $newProductId])
            ->with('success', 'تنوع محصول با موفقیت بروزرسانی شد.');
    }

    /**
     * Prefer freshly uploaded, color-specific photos in the variant gallery.
     * Product images are shared records; unchecking a photo only removes this
     * pivot relation and never deletes the file or the product's main image.
     *
     * @param array<int, int|string> $selectedImageIds
     * @param array<int, int|string> $uploadedImageIds
     */
    private function syncVariantImages(
        ProductVariant $variant,
        array $selectedImageIds,
        array $uploadedImageIds
    ): void {
        $syncData = [];
        $sortOrder = 0;
        $selected = array_values(array_unique(array_map('intval', $selectedImageIds)));
        $uploaded = array_values(array_unique(array_map('intval', $uploadedImageIds)));

        foreach ($uploaded as $imageId) {
            $syncData[$imageId] = ['sort_order' => $sortOrder++];
        }

        // Preserve the order of images already associated with this color.
        // This keeps the color-specific photo ahead of a shared main image
        // when the product is saved again without uploading new files.
        $previouslyAttached = DB::table('product_variant_images')
            ->where('product_variant_id', $variant->id)
            ->orderBy('sort_order')
            ->orderBy('product_image_id')
            ->pluck('product_image_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($previouslyAttached as $imageId) {
            if (in_array($imageId, $selected, true) && ! array_key_exists($imageId, $syncData)) {
                $syncData[$imageId] = ['sort_order' => $sortOrder++];
            }
        }

        foreach ($selected as $imageId) {
            if (! array_key_exists($imageId, $syncData)) {
                $syncData[$imageId] = ['sort_order' => $sortOrder++];
            }
        }

        $variant->images()->sync($syncData);
    }

    /**
     * Remove the specified product variant.
     */
    public function destroy(
        Request $request,
        ProductVariant $productVariant
    ): RedirectResponse {
        $productId = (int) $productVariant->product_id;

        DB::transaction(function () use ($productVariant, $productId): void {
            // Serialize deletion with checkout and variant edits.
            Product::withTrashed()
                ->whereKey($productId)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedVariant = ProductVariant::query()
                ->whereKey($productVariant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedVariant->delete();
        });

        if ($request->boolean('return_to_product')) {
            return redirect()
                ->route('admin.products.edit', $productId)
                ->with('success', 'تنوع حذف شد.');
        }

        return redirect()
            ->route('admin.product-variants.index', ['product_id' => $productId])
            ->with('success', 'تنوع محصول با موفقیت حذف شد.');
    }
}
