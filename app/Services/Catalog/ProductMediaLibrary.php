<?php

namespace App\Services\Catalog;

use App\Models\Media;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductMediaLibrary
{
    private const IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Link existing Media Library images to a product without copying files.
     *
     * Existing product-image records are reused so selecting the same asset
     * repeatedly does not create duplicate gallery rows.
     *
     * @param array<int, int|string> $mediaIds
     * @return array<int, int> ProductImage IDs, in requested order.
     */
    public function attachToProduct(int $productId, array $mediaIds, ?string $alt = null): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $mediaIds),
            static fn (int $id): bool => $id > 0,
        )));

        if ($ids === []) {
            return [];
        }

        $mediaById = Media::query()
            ->whereIn('id', $ids)
            ->whereIn('mime_type', self::IMAGE_MIME_TYPES)
            ->get()
            ->keyBy('id');

        if ($mediaById->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'media_ids' => 'یکی از فایل‌های انتخاب‌شده وجود ندارد یا تصویر قابل استفاده در محصول نیست. صفحه را تازه‌سازی کنید و دوباره تلاش کنید.',
            ]);
        }

        $existingByMediaId = ProductImage::query()
            ->where('product_id', $productId)
            ->whereIn('media_id', $ids)
            ->get()
            ->keyBy('media_id');

        $hasPrimary = ProductImage::query()
            ->where('product_id', $productId)
            ->where('is_primary', true)
            ->exists();

        $nextSort = ((int) ProductImage::query()
            ->where('product_id', $productId)
            ->max('sort_order')) + 1;

        $imageIds = [];

        foreach ($ids as $mediaId) {
            if ($existingByMediaId->has($mediaId)) {
                $imageIds[] = (int) $existingByMediaId->get($mediaId)->id;
                continue;
            }

            /** @var Media $media */
            $media = $mediaById->get($mediaId);
            $image = ProductImage::create([
                'product_id' => $productId,
                'media_id' => $media->id,
                'path' => $media->path,
                'alt' => filled($alt)
                    ? trim((string) $alt)
                    : (filled($media->alt) ? $media->alt : $media->original_name),
                'sort_order' => $nextSort++,
                'is_primary' => ! $hasPrimary,
            ]);

            $hasPrimary = true;
            $imageIds[] = (int) $image->id;
        }

        return $imageIds;
    }
}
