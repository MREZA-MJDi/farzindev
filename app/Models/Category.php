<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
        'canonical_url',
        'og_title',
        'og_description',
        'seo_robots',
        'image',
        'hover_media_id',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (Str::startsWith($this->image, [
            'http://',
            'https://',
            '//',
        ])) {
            return $this->image;
        }

        $path = ltrim($this->image, '/');

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    public function getImageSrcsetAttribute(): ?string
    {
        if (! $this->image || Str::startsWith($this->image, ['http://', 'https://', '//'])) {
            return null;
        }

        return app(ResponsiveImageService::class)->srcset($this->image);
    }

    public function getHomepageImageSrcsetAttribute(): ?string
    {
        $categoryImageIsUploaded = $this->image
            && ! Str::startsWith($this->image, ['http://', 'https://', '//']);

        if ($categoryImageIsUploaded && $this->image_srcset) {
            return $this->image_srcset;
        }

        return $this->latestActiveProduct?->images?->first()?->responsive_srcset;
    }

    /** Animated category preview selected from the existing media library. */
    public function hoverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'hover_media_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Latest active product used as the homepage image fallback.
     *
     * Admin-uploaded category images keep priority. Seeded/external
     * category images fall back to the latest active product image.
     */
    public function latestActiveProduct(): HasOne
    {
        return $this->hasOne(Product::class)
            ->where('status', 'active')
            ->latestOfMany('updated_at');
    }

    public function getHomepageImageUrlAttribute(): ?string
    {
        $categoryImageIsUploaded =
            $this->image
            && ! Str::startsWith($this->image, [
                'http://',
                'https://',
                '//',
            ]);

        if ($categoryImageIsUploaded && $this->image_url) {
            return $this->image_url;
        }

        $productImage = $this->latestActiveProduct?->images?->first()?->url;

        return $productImage ?: $this->image_url;
    }

    public function scopeActive($query)
    {
        return $query
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
