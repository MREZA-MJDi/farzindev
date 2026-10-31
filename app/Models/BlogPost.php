<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use App\Services\Media\ResponsiveImageService;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'cover_image',
        'meta_title',
        'meta_description',
        'status',
        'published_at',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getCoverImageSrcsetAttribute(): ?string
    {
        return $this->cover_image
            ? app(ResponsiveImageService::class)->srcset($this->cover_image)
            : null;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        if (blank($this->cover_image)) {
            return null;
        }

        return Storage::disk('public')->url($this->cover_image);
    }
}
