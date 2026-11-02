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
use App\Services\Media\ResponsiveImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request): View
    {
        $query = Product::query()
            ->withCount('variants')
            ->withSum('variants as active_variants_stock', 'stock')
            ->with([
                'category',
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->limit(1),
                'images.media:id,path',
            ]);

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        }

        $categoryId = $request->integer('category_id');
        if ($categoryId > 0) {
            $query->where('category_id', $categoryId);
        }

        $status = $request->query('status');
        if (in_array($status, ['active', 'draft', 'archived'], true)) {
            $query->where('status', $status);
        }

        $stock = $request->query('stock');
        if ($stock === 'out') {
            $query->where(function ($builder): void {
                $builder->where(fn ($base) => $base
                    ->whereDoesntHave('variants')
                    ->where('stock', '<=', 0))
                    ->orWhere(fn ($variants) => $variants
                        ->whereHas('variants')
                        ->whereDoesntHave('variants', fn ($variant) => $variant->where('stock', '>', 0)));
            });
        } elseif ($stock === 'low') {
            $query->where(function ($builder): void {
                $builder->where(fn ($base) => $base
                    ->whereDoesntHave('variants')
                    ->whereBetween('stock', [1, 5]))
                    ->orWhereHas('variants', fn ($variant) => $variant->whereBetween('stock', [1, 5]));
            });
        } elseif ($stock === 'available') {
            $query->where(function ($builder): void {
                $builder->where(fn ($base) => $base
                    ->whereDoesntHave('variants')
                    ->where('stock', '>', 5))
                    ->orWhereHas('variants', fn ($variant) => $variant->where('stock', '>', 5));
            });
        }

        $completeness = $request->query('completeness');
        $incomplete = function ($builder): void {
            $builder->whereNull('description')
                ->orWhere('description', '')
                ->orWhereNull('short_description')
                ->orWhere('short_description', '')
                ->orWhere('sku', 'like', 'FZ-SEED-%')
                ->orWhere('sku', 'like', 'LIV-%')
                ->orWhereDoesntHave('images', fn ($imageQuery) => $imageQuery
                    ->whereNotNull('alt')
                    ->where('alt', '<>', ''));
        };

        if ($completeness === 'incomplete') {
            $query->where($incomplete);
        } elseif ($completeness === 'complete') {
            $query->whereNot(function ($builder) use ($incomplete): void {
                $builder->where($incomplete);
            });
        }

        $products = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(100)
            ->get();
        $mediaImages = Media::query()
            ->whereIn('mime_type', ['image/jpeg', 'image/png', 'image/webp'])
            ->latest('id')
            ->limit(60)
            ->get();

        $product = new Product();

        return view('admin.products.create', compact('categories', 'mediaImages', 'product'));
    }

    /**
     * Store a newly created product.
     */
    public function store(
        StoreProductRequest $request,
        ProductMediaLibrary $mediaLibrary,
        ResponsiveImageService $images
    ): RedirectResponse {
        $data = $request->validated();
        $uploadedImage = $request->file('image');
        $primaryMediaId = $data['primary_media_id'] ?? null;
        unset($data['image'], $data['primary_media_id']);

        $newPath = null;

        if ($uploadedImage) {
            try {
                $newPath = $images->store($uploadedImage, 'products/images');
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
                $images->delete($newPath);
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
            ->where(function ($query) use ($product): void {
                $query->where('is_active', true)
                    ->orWhere('id', $product->category_id);
            })
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$product->category_id])
            ->orderBy('name')
            ->limit(101)
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
        ProductMediaLibrary $mediaLibrary,
        ResponsiveImageService $images
    ): RedirectResponse {
        $data = $request->validated();
        $uploadedImage = $request->file('image');
        $primaryMediaId = $data['primary_media_id'] ?? null;
        unset($data['image'], $data['primary_media_id']);

        $newPath = null;

        if ($uploadedImage) {
            try {
                $newPath = $images->store($uploadedImage, 'products/images');
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
                $images->delete($newPath);
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
            $images->delete($oldPathToDelete);
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
