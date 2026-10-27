<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Media;
use App\Services\Catalog\ProductMediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
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
        $mediaImages = Media::query()
            ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])
            ->latest('id')
            ->limit(60)
            ->get();

        return view('admin.products.create', compact('categories', 'mediaImages'));
    }

    /**
     * Store a newly created product.
     */
    public function store(
        StoreProductRequest $request,
        ProductMediaLibrary $mediaLibrary
    ): RedirectResponse {
        $data = $request->validated();
        $uploadedImage = $request->file('image');
        $primaryMediaId = $data['primary_media_id'] ?? null;
        unset($data['image'], $data['primary_media_id']);

        $newPath = null;

        if ($uploadedImage) {
            try {
                $newPath = $uploadedImage->store('products/images', 'public');
            } catch (Throwable $exception) {
                $requestId = (string) $request->attributes->get('request_id', 'unavailable');
                Log::error('Product image storage failed during creation.', [
                    'request_id' => $requestId,
                    'sku' => $data['sku'] ?? null,
                    'exception' => $exception,
                ]);

                return back()->withInput()->withErrors([
                    'image' => 'ذخیره تصویر محصول انجام نشد؛ فضای دیسک و محدودیت آپلود را بررسی کنید. کد پیگیری: ' . $requestId,
                ]);
            }

            if (! $newPath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'ذخیره تصویر محصول انجام نشد؛ دوباره تلاش کنید.',
                    ]);
            }
        }

        try {
            $product = DB::transaction(function () use ($data, $newPath, $primaryMediaId, $mediaLibrary): Product {
                $product = Product::create($data);

                if ($newPath !== null) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'media_id' => null,
                        'path' => $newPath,
                        'alt' => $product->name,
                        'sort_order' => 0,
                        'is_primary' => true,
                    ]);
                } elseif ($primaryMediaId !== null) {
                    $mediaLibrary->attachToProduct(
                        $product->id,
                        [(int) $primaryMediaId],
                        $product->name
                    );
                }

                return $product;
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            $requestId = (string) $request->attributes->get('request_id', 'unavailable');
            Log::error('Product creation failed.', [
                'request_id' => $requestId,
                'sku' => $data['sku'] ?? null,
                'primary_media_id' => $primaryMediaId,
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'name' => 'ذخیره محصول انجام نشد؛ هیچ تغییر ناقصی ثبت نشد. کد پیگیری: ' . $requestId,
            ]);
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
            'images.media',
            'allVariants.images.media',
        ]);

        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product): View
    {
        $product->load(['images.media', 'allVariants.images.media']);

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        // Keep the picker responsive on large libraries: the newest 60 supported
        // raster images are enough for common workflows; the full library remains
        // available from Admin > Media.
        $mediaImages = Media::query()
            ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])
            ->latest('id')
            ->limit(60)
            ->get();

        return view(
            'admin.products.edit',
            compact('product', 'categories', 'mediaImages')
        );
    }

    /**
     * Update the specified product.
     */
    public function update(
        UpdateProductRequest $request,
        Product $product,
        ProductMediaLibrary $mediaLibrary
    ): RedirectResponse {
        $data = $request->validated();
        $uploadedImage = $request->file('image');
        $primaryMediaId = $data['primary_media_id'] ?? null;
        unset($data['image'], $data['primary_media_id']);

        $newPath = null;

        if ($uploadedImage) {
            try {
                $newPath = $uploadedImage->store('products/images', 'public');
            } catch (Throwable $exception) {
                $requestId = (string) $request->attributes->get('request_id', 'unavailable');
                Log::error('Product image storage failed during update.', [
                    'request_id' => $requestId,
                    'product_id' => $product->id,
                    'exception' => $exception,
                ]);

                return back()->withInput()->withErrors([
                    'image' => 'ذخیره تصویر جدید انجام نشد؛ تصویر قبلی حفظ شد. کد پیگیری: ' . $requestId,
                ]);
            }

            if (! $newPath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'ذخیره تصویر محصول انجام نشد؛ تصویر قبلی حفظ شد.',
                    ]);
            }
        }

        $oldPathToDelete = null;

        try {
            DB::transaction(function () use (
                $product,
                $data,
                $newPath,
                $primaryMediaId,
                $mediaLibrary,
                &$oldPathToDelete
            ): void {
                $lockedProduct = Product::query()
                    ->whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedProduct->update($data);

                if ($newPath === null && $primaryMediaId === null) {
                    return;
                }

                if ($primaryMediaId !== null) {
                    $mediaImageIds = $mediaLibrary->attachToProduct(
                        $lockedProduct->id,
                        [(int) $primaryMediaId],
                        $lockedProduct->name
                    );
                    $selectedImage = ProductImage::query()
                        ->whereKey($mediaImageIds[0])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $lockedProduct->images()->update(['is_primary' => false]);
                    $selectedImage->update(['is_primary' => true]);

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

                // A primary image may also be attached to a color variant.
                // Never rewrite/delete that shared record when the product's
                // default image changes, or the variant gallery silently changes too.
                $isPrimaryAttachedToVariant = $primaryImage
                    ? $primaryImage->variants()->exists()
                    : false;

                $lockedProduct->images()->update(['is_primary' => false]);

                if ($primaryImage && ! $isPrimaryAttachedToVariant) {
                    // Media Library assets are shared. Detach the ProductImage
                    // from its Media record without deleting the shared file.
                    $isSharedMediaAsset = $primaryImage->media_id !== null
                        || Media::query()->where('path', $primaryImage->path)->exists();
                    $oldPathToDelete = $isSharedMediaAsset ? null : $primaryImage->path;
                    $primaryImage->update([
                        'path' => $newPath,
                        'media_id' => null,
                        'alt' => $lockedProduct->name,
                        'is_primary' => true,
                    ]);
                } else {
                    $nextSortOrder = ((int) ProductImage::query()
                        ->where('product_id', $lockedProduct->id)
                        ->max('sort_order')) + 1;

                    ProductImage::create([
                        'product_id' => $lockedProduct->id,
                        'path' => $newPath,
                        'alt' => $lockedProduct->name,
                        'sort_order' => $nextSortOrder,
                        'is_primary' => true,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            $requestId = (string) $request->attributes->get('request_id', 'unavailable');
            Log::error('Product update failed.', [
                'request_id' => $requestId,
                'product_id' => $product->id,
                'primary_media_id' => $primaryMediaId,
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'name' => 'ذخیره تغییرات محصول انجام نشد؛ نسخه قبلی حفظ شد. کد پیگیری: ' . $requestId,
            ]);
        }

        // Delete the previous image only after the database change commits.
        if (
            $newPath !== null
            && $oldPathToDelete
            && ! str_starts_with($oldPathToDelete, 'http://')
            && ! str_starts_with($oldPathToDelete, 'https://')
            && ! str_starts_with($oldPathToDelete, '//')
            && $oldPathToDelete !== $newPath
        ) {
            Storage::disk('public')->delete($oldPathToDelete);
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
