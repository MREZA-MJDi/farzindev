<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'media_id',
        'path',
        'alt',
        'sort_order',
        'is_primary',
    ];

    protected $appends = [
        'url',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_primary' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_images',
            'product_image_id',
            'product_variant_id'
        )
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    public function getResponsiveSrcsetAttribute(): ?string
    {
        if (Str::startsWith($this->path, ['http://', 'https://', '//'])) {
            return null;
        }

        return app(ResponsiveImageService::class)->srcset($this->path);
    }

    public function getUrlAttribute(): string
    {
        if (
            Str::startsWith($this->path, [
                'http://',
                'https://',
                '//',
            ])
        ) {
            return $this->path;
        }

        $path = ltrim($this->path, '/');

        if (! Storage::disk('public')->exists($path)) {
            return $this->media?->url ?? '';
        }

        return Storage::disk('public')->url($path);
    }
}
