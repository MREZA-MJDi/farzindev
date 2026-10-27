<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductImage\StoreProductImageRequest;
use App\Http\Requests\Admin\ProductImage\UpdateProductImageRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Media;
use App\Services\Catalog\ProductMediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;
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
    public function storeForProduct(
        Request $request,
        Product $product,
        ProductMediaLibrary $mediaLibrary
    ): RedirectResponse {
        $validated = $request->validate([
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:min_width=800,min_height=600',
                'max:20480',
            ],
            'media_ids' => ['nullable', 'array', 'max:30'],
            'media_ids.*' => [
                'required',
                'integer',
                Rule::exists('media', 'id')->where(
                    fn ($query) => $query->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])
                ),
            ],
            'alt' => ['nullable', 'string', 'max:255'],
        ], [
            'images.max' => 'در هر بار حداکثر ۱۰ تصویر می‌توانید آپلود کنید.',
            'images.*.image' => 'همهٔ فایل‌ها باید تصویر معتبر باشند.',
            'images.*.mimes' => 'فرمت تصویر باید JPG، PNG یا WEBP باشد.',
            'images.*.dimensions' => 'هر تصویر باید حداقل ۸۰۰ در ۶۰۰ پیکسل باشد.',
            'images.*.max' => 'حجم هر تصویر نباید بیشتر از ۲۰ مگابایت باشد.',
            'media_ids.array' => 'فهرست رسانه‌های انتخاب‌شده معتبر نیست.',
            'media_ids.max' => 'در هر بار حداکثر ۳۰ تصویر از کتابخانه می‌توانید انتخاب کنید.',
            'media_ids.*.exists' => 'یکی از رسانه‌های انتخاب‌شده حذف شده یا تصویر قابل استفاده نیست.',
        ]);

        $uploads = $request->file('images', []);
        $mediaIds = $validated['media_ids'] ?? [];

        if ($uploads === [] && $mediaIds === []) {
            return back()->withInput()->withErrors([
                'media_ids' => 'حداقل یک فایل از کتابخانه انتخاب کنید یا تصویر جدید آپلود کنید.',
            ]);
        }

        $storedPaths = [];

        try {
            foreach ($uploads as $file) {
                try {
            $path = $file->store('products/images', 'public');
        } catch (Throwable $exception) {
            $requestId = (string) $request->attributes->get('request_id', 'unavailable');
            Log::error('Legacy product image upload failed.', [
                'request_id' => $requestId,
                'product_id' => $request->input('product_id'),
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'image' => 'ذخیره تصویر محصول انجام نشد؛ فضای دیسک و دسترسی نوشتن را بررسی کنید. کد پیگیری: ' . $requestId,
            ]);
        }

                if (! $path) {
                    throw new RuntimeException('ذخیره یکی از تصاویر گالری انجام نشد.');
                }

                $storedPaths[] = $path;
            }

            DB::transaction(function () use (
                $product,
                $validated,
                $storedPaths,
                $mediaIds,
                $mediaLibrary
            ): void {
                $lockedProduct = Product::query()
                    ->whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $mediaLibrary->attachToProduct(
                    $lockedProduct->id,
                    $mediaIds,
                    $validated['alt'] ?? null
                );

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
                        'media_id' => null,
                        'path' => $path,
                        'alt' => trim((string) ($validated['alt'] ?? '')) !== ''
                            ? trim((string) $validated['alt'])
                            : trim($lockedProduct->name . ' - تصویر ' . ($index + 1)),
                        'sort_order' => $nextSortOrder + $index,
                        'is_primary' => ! $hasPrimary && $index === 0,
                    ]);
                    $hasPrimary = true;
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }

            $requestId = (string) $request->attributes->get('request_id', 'unavailable');
            Log::error('Product gallery attach failed.', [
                'request_id' => $requestId,
                'product_id' => $product->id,
                'media_ids' => $mediaIds,
                'exception' => $exception,
            ]);

            if ($exception instanceof RuntimeException && $exception->getMessage() === 'ذخیره یکی از تصاویر گالری انجام نشد.') {
                return back()->withInput()->withErrors(['images' => $exception->getMessage()]);
            }

            return back()->withInput()->withErrors([
                'images' => 'ذخیره گالری انجام نشد؛ تصاویر قبلی حفظ شدند. کد پیگیری: ' . $requestId,
            ]);
        }

        $addedCount = count($storedPaths) + count(array_unique(array_map('intval', $mediaIds)));

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', $addedCount . ' تصویر از کتابخانه یا آپلود به گالری محصول اضافه/متصل شد.');
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
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);

            $requestId = (string) $request->attributes->get('request_id', 'unavailable');
            Log::error('Legacy product image database write failed.', [
                'request_id' => $requestId,
                'product_id' => $data['product_id'] ?? null,
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'image' => 'ثبت تصویر محصول انجام نشد؛ تغییرات ذخیره نشدند. کد پیگیری: ' . $requestId,
            ]);
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
        // Variant-linked images are shared catalogue records. Mutating their
        // path/product or deleting them from the legacy endpoint would silently
        // alter every color using the image, so keep changes in the product editor.
        if ($productImage->variants()->exists()) {
            return redirect()
                ->route('admin.products.edit', $productImage->product_id)
                ->with('error', 'این تصویر به یک یا چند تنوع متصل است؛ مدیریت آن را از ویرایش همان محصول انجام دهید.');
        }

        $data = $request->validated();

        $oldProductId = $productImage->product_id;
        $oldPath = $productImage->path;
        $oldMediaId = $productImage->media_id;
        $oldPathIsMediaAsset = $oldMediaId !== null || Media::query()->where('path', $oldPath)->exists();
        $oldWasPrimary = (bool) $productImage->is_primary;

        $newPath = null;

        if ($request->hasFile('image')) {
            try {
                $newPath = $request->file('image')->store(
                    'products/images',
                    'public'
                );
            } catch (Throwable $exception) {
                $requestId = (string) $request->attributes->get('request_id', 'unavailable');
                Log::error('Product image replacement upload failed.', [
                    'request_id' => $requestId,
                    'product_image_id' => $productImage->id,
                    'exception' => $exception,
                ]);

                return back()->withInput()->withErrors([
                    'image' => 'ذخیره تصویر جدید انجام نشد؛ تصویر فعلی حفظ شد. کد پیگیری: ' . $requestId,
                ]);
            }

            if (! $newPath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'ذخیره تصویر محصول انجام نشد؛ تصویر قبلی حفظ شد.',
                    ]);
            }

            $data['path'] = $newPath;
            // The replacement upload is now product-owned rather than a shared
            // Media Library asset.
            $data['media_id'] = null;
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
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            $requestId = (string) $request->attributes->get('request_id', 'unavailable');
            Log::error('Product image update failed.', [
                'request_id' => $requestId,
                'product_image_id' => $productImage->id,
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'image' => 'ذخیره تغییرات تصویر انجام نشد؛ تصویر فعلی حفظ شد. کد پیگیری: ' . $requestId,
            ]);
        }

        if (
            $newPath !== null
            && ! $oldPathIsMediaAsset
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
        // Prevent legacy routes from deleting a file still used by a color variant.
        if ($productImage->variants()->exists()) {
            return redirect()
                ->route('admin.products.edit', $productImage->product_id)
                ->with('error', 'این تصویر به یک یا چند تنوع متصل است؛ ابتدا اتصال آن را از ویرایش محصول مدیریت کنید.');
        }

        $productId = $productImage->product_id;
        $path = $productImage->path;
        $isMediaAsset = $productImage->media_id !== null
            || Media::query()->where('path', $path)->exists();
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
            ! $isMediaAsset
            && $path
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
