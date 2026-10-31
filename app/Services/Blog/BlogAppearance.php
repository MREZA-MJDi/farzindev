<?php

namespace App\Services\Blog;

use App\Models\StorefrontSetting;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BlogAppearance
{
    private const CACHE_KEY = 'blog.appearance.v1';

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function (): array {
            $values = StorefrontSetting::query()
                ->whereIn('key', [
                    'blog_hero_art_image',
                    'blog_hero_art_alt',
                    'blog_hero_art_fit',
                    'blog_hero_art_scale',
                    'blog_hero_art_position',
                    'blog_closing_art_image',
                    'blog_closing_art_alt',
                    'blog_closing_art_fit',
                    'blog_closing_art_scale',
                    'blog_closing_art_position',
                    'blog_container_width',
                ])
                ->pluck('value', 'key');

            return [
                'hero' => $this->imageSettings($values, 'hero'),
                'closing' => $this->imageSettings($values, 'closing'),
                'container_width' => max(1080, min(1440, (int) ($values['blog_container_width'] ?? 1280))),
            ];
        });
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function imageSettings($values, string $slot): array
    {
        $prefix = 'blog_' . $slot . '_art';
        $path = $values[$prefix . '_image'] ?? null;
        $fit = $values[$prefix . '_fit'] ?? 'cover';
        $position = $values[$prefix . '_position'] ?? 'center';

        if (! in_array($fit, ['cover', 'contain'], true)) {
            $fit = 'cover';
        }

        if (! in_array($position, ['center', 'top', 'bottom', 'left', 'right'], true)) {
            $position = 'center';
        }

        return [
            'image_path' => $path,
            'image_url' => $this->resolveUrl($path),
            'image_srcset' => $this->resolveSrcset($path),
            'alt' => (string) ($values[$prefix . '_alt'] ?? ''),
            'fit' => $fit,
            'scale' => max(70, min(135, (int) ($values[$prefix . '_scale'] ?? 100))),
            'position' => $position,
            'object_position' => match ($position) {
                'top' => 'center top',
                'bottom' => 'center bottom',
                'left' => 'left center',
                'right' => 'right center',
                default => 'center center',
            },
        ];
    }

    private function resolveUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        return Storage::disk('public')->url($path);
    }

    private function resolveSrcset(?string $path): ?string
    {
        if (blank($path) || filter_var($path, FILTER_VALIDATE_URL) || str_starts_with($path, '/')) {
            return null;
        }

        return app(ResponsiveImageService::class)->srcset($path);
    }
}
