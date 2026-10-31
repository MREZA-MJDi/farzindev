<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ResponsiveImageService
{
    /**
     * Store the uploaded original and create smaller WebP variants.
     *
     * The original path remains the canonical stored path so existing database
     * rows, downloads and admin editing workflows remain backwards-compatible.
     */
    public function store(UploadedFile $file, string $directory, string $disk = 'public'): ?string
    {
        $path = $file->store($directory, $disk);

        if (! $path) {
            return null;
        }

        try {
            $this->generateForPath($path, $disk);
        } catch (Throwable $exception) {
            // Optimization must never turn a successful original upload into a
            // failed product/category update. The original remains usable.
            Log::warning('Responsive image generation failed; original retained.', [
                'disk' => $disk,
                'path' => $path,
                'exception' => $exception,
            ]);
        }

        return $path;
    }

    /**
     * @return array<int, string> Paths of newly-created WebP derivatives.
     */
    public function generateForPath(string $path, string $disk = 'public'): array
    {
        if (
            $path === ''
            || Str::startsWith($path, ['http://', 'https://', '//'])
            || ! function_exists('imagecreatefromstring')
            || ! function_exists('imagewebp')
        ) {
            return [];
        }

        $storage = Storage::disk($disk);

        if (! $storage->exists($path) || ! method_exists($storage, 'path')) {
            return [];
        }

        $sourcePath = $storage->path($path);
        $imageInfo = @getimagesize($sourcePath);

        if (! is_array($imageInfo) || ! in_array($imageInfo['mime'] ?? '', [
            'image/jpeg',
            'image/png',
            'image/webp',
        ], true)) {
            return [];
        }

        $width = (int) ($imageInfo[0] ?? 0);
        $height = (int) ($imageInfo[1] ?? 0);

        if ($width < 1 || $height < 1) {
            return [];
        }

        $maxPixels = max(1, (int) config('images.max_source_pixels', 24000000));

        if (($width * $height) > $maxPixels || ! $this->hasMemoryFor($width, $height)) {
            Log::notice('Responsive image generation skipped because the source is too large for the current PHP memory limit.', [
                'disk' => $disk,
                'path' => $path,
                'width' => $width,
                'height' => $height,
            ]);

            return [];
        }

        $contents = @file_get_contents($sourcePath);

        if (! is_string($contents) || $contents === '') {
            return [];
        }

        $source = @imagecreatefromstring($contents);
        unset($contents);

        if (! $source) {
            return [];
        }

        $source = $this->applyExifOrientation($source, $sourcePath, $imageInfo['mime'] ?? '');

        if (! $source) {
            return [];
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $quality = max(40, min(95, (int) config('images.webp_quality', 80)));
        $widths = array_values(array_unique(array_map(
            static fn ($value): int => (int) $value,
            (array) config('images.responsive_widths', [480, 800, 1200, 1600])
        )));
        sort($widths);

        $created = [];
        $sourceSize = (int) $storage->size($path);

        foreach ($widths as $targetWidth) {
            if ($targetWidth < 1 || $targetWidth >= $sourceWidth) {
                continue;
            }

            $variantPath = $this->variantPath($path, $targetWidth);

            if ($storage->exists($variantPath)) {
                continue;
            }

            $targetHeight = max(1, (int) round($sourceHeight * ($targetWidth / $sourceWidth)));
            $target = imagecreatetruecolor($targetWidth, $targetHeight);

            if (! $target) {
                continue;
            }

            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefilledrectangle($target, 0, 0, $targetWidth, $targetHeight, $transparent);

            $resampled = imagecopyresampled(
                $target,
                $source,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $sourceWidth,
                $sourceHeight
            );

            if (! $resampled) {
                imagedestroy($target);
                continue;
            }

            ob_start();
            $encoded = imagewebp($target, null, $quality);
            $webp = ob_get_clean();
            imagedestroy($target);

            if (! $encoded || ! is_string($webp) || $webp === '') {
                continue;
            }

            // A derivative should not be larger than the original upload.
            if ($sourceSize > 0 && strlen($webp) >= $sourceSize) {
                continue;
            }

            if ($storage->put($variantPath, $webp)) {
                $created[] = $variantPath;
            }
        }

        imagedestroy($source);

        return $created;
    }

    /**
     * Return a browser-native srcset. Missing derivatives are simply omitted.
     */
    public function srcset(string|null $path, string $disk = 'public'): ?string
    {
        if (
            blank($path)
            || Str::startsWith((string) $path, ['http://', 'https://', '//'])
        ) {
            return null;
        }

        $storage = Storage::disk($disk);
        $path = ltrim((string) $path, '/');

        if (! $storage->exists($path) || ! method_exists($storage, 'path')) {
            return null;
        }

        $candidates = [];

        foreach ((array) config('images.responsive_widths', [480, 800, 1200, 1600]) as $width) {
            $width = (int) $width;
            $variantPath = $this->variantPath($path, $width);

            if ($width > 0 && $storage->exists($variantPath)) {
                $candidates[$width] = $storage->url($variantPath) . ' ' . $width . 'w';
            }
        }

        $info = @getimagesize($storage->path($path));
        $originalWidth = is_array($info) ? (int) ($info[0] ?? 0) : 0;

        if ($originalWidth > 0) {
            $candidates[$originalWidth] = $storage->url($path) . ' ' . $originalWidth . 'w';
        }

        if ($candidates === []) {
            return null;
        }

        ksort($candidates, SORT_NUMERIC);

        return implode(', ', array_values($candidates));
    }

    /**
     * Remove derivatives and optionally the original after a successful DB swap.
     */
    public function delete(string|null $path, string $disk = 'public', bool $deleteOriginal = true): void
    {
        if (
            blank($path)
            || Str::startsWith((string) $path, ['http://', 'https://', '//'])
        ) {
            return;
        }

        $storage = Storage::disk($disk);
        $path = ltrim((string) $path, '/');

        foreach ((array) config('images.responsive_widths', [480, 800, 1200, 1600]) as $width) {
            $storage->delete($this->variantPath($path, (int) $width));
        }

        if ($deleteOriginal) {
            $storage->delete($path);
        }
    }

    public function variantPath(string $path, int $width): string
    {
        $directory = dirname($path);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $name = $filename . '.w' . $width . '.webp';

        return $directory === '.' ? $name : $directory . '/' . $name;
    }

    private function hasMemoryFor(int $width, int $height): bool
    {
        $limit = $this->memoryLimitBytes();

        if ($limit <= 0) {
            return true;
        }

        // GD keeps the decoded source plus at least one resampling buffer.
        $estimated = memory_get_usage(true) + ($width * $height * 8);

        return $estimated < (int) ($limit * 0.82);
    }

    private function memoryLimitBytes(): int
    {
        $value = trim((string) ini_get('memory_limit'));

        if ($value === '' || $value === '-1') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private function applyExifOrientation($image, string $path, string $mime)
    {
        if (
            $mime !== 'image/jpeg'
            || ! function_exists('exif_read_data')
            || ! function_exists('imagerotate')
        ) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = (int) ($exif['Orientation'] ?? 1);
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
