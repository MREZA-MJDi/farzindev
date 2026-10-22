<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductVariant\StoreProductVariantRequest;
use App\Http\Requests\Admin\ProductVariant\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of product variants.
     */
    public function index(Request $request): View
    {
        $query = ProductVariant::query()
            ->with([
                'product',
                'images',
            ])
            ->orderBy('type')
            ->orderBy('name')
            ->orderBy('value');

        if ($request->filled('product_id')) {
            $query->where(
                'product_id',
                $request->integer('product_id')
            );
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        if ($request->filled('is_active')) {
            $query->where(
                'is_active',
                $request->boolean('is_active')
            );
        }

        $variants = $query
            ->paginate(20)
            ->withQueryString();

        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $types = ProductVariant::query()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view(
            'admin.product-variants.index',
            compact(
                'variants',
                'products',
                'types'
            )
        );
    }

    /**
     * Show the form for creating a new product variant.
     */
    public function create(Request $request): View
    {
        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $selectedProductId = $request->filled('product_id')
            ? $request->integer('product_id')
            : null;

        $selectedProductImages = $selectedProductId
            ? ProductImage::query()
                ->where('product_id', $selectedProductId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
            : collect();

        return view(
            'admin.product-variants.create',
            compact(
                'products',
                'selectedProductId',
                'selectedProductImages'
            )
        );
    }

    /**
     * Store a newly created product variant.
     */
    public function store(
        StoreProductVariantRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        $imageIds = $data['image_ids'] ?? [];
        $uploads = $request->file('images', []);
        unset($data['image_ids'], $data['images']);

        $productId = (int) $data['product_id'];
        $storedPaths = [];
        $newImageIds = [];

        foreach ($uploads as $upload) {
            $path = $upload->store('products/images', 'public');
            if (! $path) {
                foreach ($storedPaths as $storedPath) {
                    Storage::disk('public')->delete($storedPath);
                }

                return back()->withInput()->withErrors([
                    'images' => 'ذخیره یکی از تصاویر تنوع انجام نشد.',
                ]);
            }
            $storedPaths[] = $path;
        }

        try {
            DB::transaction(function () use ($data, $imageIds, $productId, $storedPaths, &$newImageIds): void {
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

                $this->syncVariantImages($productVariant, $imageIds, $newImageIds);
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }
            throw $exception;
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
    public function show(
        ProductVariant $productVariant
    ): View {
        $productVariant->load([
            'product',
            'images',
        ]);

        return view(
            'admin.product-variants.show',
            compact('productVariant')
        );
    }

    /**
     * Show the form for editing the specified product variant.
     */
    public function edit(
        ProductVariant $productVariant
    ): View {
        $productVariant->load('images');

        $products = Product::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $selectedProductId = (int) $productVariant->product_id;

        $selectedProductImages = ProductImage::query()
            ->where('product_id', $selectedProductId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view(
            'admin.product-variants.edit',
            compact(
                'productVariant',
                'products',
                'selectedProductId',
                'selectedProductImages'
            )
        );
    }

    /**
     * Update the specified product variant.
     */
    public function update(
        UpdateProductVariantRequest $request,
        ProductVariant $productVariant
    ): RedirectResponse {
        $data = $request->validated();

        $imageIds = $data['image_ids'] ?? [];
        $uploads = $request->file('images', []);
        unset($data['image_ids'], $data['images']);

        $oldProductId = (int) $productVariant->product_id;
        $newProductId = (int) $data['product_id'];
        $storedPaths = [];
        $newImageIds = [];

        foreach ($uploads as $upload) {
            $path = $upload->store('products/images', 'public');
            if (! $path) {
                foreach ($storedPaths as $storedPath) {
                    Storage::disk('public')->delete($storedPath);
                }

                return back()->withInput()->withErrors([
                    'images' => 'ذخیره یکی از تصاویر تنوع انجام نشد؛ تصاویر قبلی حفظ شدند.',
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
                &$newImageIds
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

                $this->syncVariantImages($lockedVariant, $imageIds, $newImageIds);
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk('public')->delete($storedPath);
            }
            throw $exception;
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

        foreach (array_values(array_unique(array_map('intval', $uploadedImageIds))) as $index => $imageId) {
            $syncData[$imageId] = ['sort_order' => $index];
        }

        foreach (array_values(array_unique(array_map('intval', $selectedImageIds))) as $index => $imageId) {
            if (! array_key_exists($imageId, $syncData)) {
                $syncData[$imageId] = ['sort_order' => 100 + $index];
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
