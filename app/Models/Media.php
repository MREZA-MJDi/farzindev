<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\Media\ResponsiveImageService;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'disk',
        'path',
        'filename',
        'original_name',
        'mime_type',
        'extension',
        'size',
        'alt',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    /**
     * User who uploaded this media.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Full public URL for the media file.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->path, '/'));
    }

    public function getResponsiveSrcsetAttribute(): ?string
    {
        if (! $this->is_image || $this->mime_type === 'image/gif') {
            return null;
        }

        return app(ResponsiveImageService::class)->srcset($this->path, $this->disk ?: 'public');
    }

    /**
     * Human-readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format($bytes / (1024 * 1024), 1) . ' MB';
        }

        return number_format(
                $bytes / (1024 * 1024 * 1024),
                1
            ) . ' GB';
    }

    /**
     * Determine whether the media is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function getIsVideoAttribute(): bool
    {
        return in_array($this->mime_type, ['video/mp4', 'video/webm'], true);
    }

    public function getIsAnimatedPreviewAttribute(): bool
    {
        return $this->mime_type === 'image/gif' || $this->is_video;
    }

    /**
     * @return HasMany
     */
    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}
