<?php

namespace App\Models;

use App\Services\Media\ResponsiveImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'body',
        'body_html',
        'cover_image',
        'cover_image_alt',
        'meta_title',
        'meta_description',
        'focus_keyword',
        'canonical_url',
        'og_title',
        'og_description',
        'seo_robots',
        'schema_type',
        'is_featured',
        'status',
        'published_at',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(BlogTerm::class, 'blog_post_terms')
            ->withTimestamps();
    }

    public function categories(): BelongsToMany
    {
        return $this->terms()->where('type', BlogTerm::TYPE_CATEGORY);
    }

    public function tags(): BelongsToMany
    {
        return $this->terms()->where('type', BlogTerm::TYPE_TAG);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeIndexable(Builder $query): Builder
    {
        return $query->whereIn('seo_robots', ['inherit', 'index']);
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

    public function getReadingTimeAttribute(): int
    {
        $text = strip_tags((string) ($this->body_html ?: $this->body));
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return max(1, (int) ceil(count($words) / 200));
    }

    public function getPlainTextAttribute(): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($this->body_html ?: $this->body))) ?? '');
    }
}
