<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\ProductImage;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResponsiveImageServiceTest extends TestCase
{
    public function test_media_and_product_image_urls_do_not_point_to_missing_files(): void
    {
        Storage::fake('public');

        $media = new Media([
            'disk' => 'public',
            'path' => 'media/missing-cover.webp',
        ]);

        $this->assertNull($media->url);

        Storage::disk('public')->put('media/available-cover.webp', 'image-content');
        $media->path = 'media/available-cover.webp';

        $this->assertSame(
            Storage::disk('public')->url('media/available-cover.webp'),
            $media->url
        );

        $missingMedia = new Media([
            'disk' => 'public',
            'path' => 'media/missing-product.webp',
        ]);
        $productImage = new ProductImage([
            'path' => 'media/missing-product.webp',
        ]);
        $productImage->setRelation('media', $missingMedia);

        $this->assertSame('', $productImage->url);
    }

    public function test_new_image_upload_keeps_original_and_creates_responsive_webp_sources(): void
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('GD WebP support is required for responsive image conversion tests.');
        }

        Storage::fake('public');

        $service = app(ResponsiveImageService::class);
        $path = $service->store(
            UploadedFile::fake()->image('catalog-product.jpg', 1600, 1200)->size(4096),
            'products/images'
        );

        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists($service->variantPath($path, 480));
        Storage::disk('public')->assertExists($service->variantPath($path, 800));
        Storage::disk('public')->assertExists($service->variantPath($path, 1200));

        $srcset = $service->srcset($path);

        $this->assertIsString($srcset);
        $this->assertStringContainsString('480w', $srcset);
        $this->assertStringContainsString('800w', $srcset);
        $this->assertStringContainsString('1200w', $srcset);
        $this->assertStringContainsString('1600w', $srcset);
    }

    public function test_external_and_missing_images_do_not_generate_srcsets(): void
    {
        $service = app(ResponsiveImageService::class);

        $this->assertNull($service->srcset('https://images.example.test/product.jpg'));
        $this->assertNull($service->srcset('missing/product.jpg'));
    }

    public function test_delete_removes_original_and_its_generated_variants(): void
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatefromstring')) {
            $this->markTestSkipped('GD WebP support is required for responsive image conversion tests.');
        }

        Storage::fake('public');

        $service = app(ResponsiveImageService::class);
        $path = $service->store(
            UploadedFile::fake()->image('category.png', 1200, 900)->size(2048),
            'categories'
        );

        $this->assertNotEmpty($path);
        $service->delete($path);

        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing($service->variantPath($path, 480));
        Storage::disk('public')->assertMissing($service->variantPath($path, 800));
    }
}
