<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'specifications',
        'description',
        'price',
        'compare_at_price',
        'stock',
        'status',
        'is_featured',
        'is_new',
        'show_in_home_gallery',
        'meta_title',
        'meta_description',
        'canonical_url',
        'og_title',
        'og_description',
        'seo_robots',

        /*
         * Internal installment settings
         */
        'installment_enabled',
        'installment_cash_percent',
        'installment_remainder_method',
        'installment_cheque_count',
        'installment_interval_months',
    ];

    /**
     * @return HasMany
     */
    public function wishlists()
    {
        return $this->hasMany(
            Wishlist::class
        );
    }
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'specifications' => 'array',
            'stock' => 'integer',

            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'show_in_home_gallery' => 'boolean',

            /*
             * Internal installment settings
             */
            'installment_enabled' => 'boolean',
            'installment_cash_percent' => 'integer',
            'installment_cheque_count' => 'integer',
            'installment_interval_months' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)
            ->where('is_active', true);
    }

    /**
     * All product variants, including inactive admin-managed options.
     */
    public function allVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function primaryImage(): ?ProductImage
    {
        $images = $this->relationLoaded('images')
            ? $this->images
            : $this->images()
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

        $availablePrimary = $images->first(
            fn (ProductImage $image): bool => $image->is_primary && filled($image->url)
        );

        if ($availablePrimary) {
            return $availablePrimary;
        }

        $availableFallback = $images->first(
            fn (ProductImage $image): bool => filled($image->url)
        );

        if ($availableFallback) {
            return $availableFallback;
        }

        // Some listing queries intentionally eager-load only one image. If that
        // file was deleted, make one fallback query for the remaining gallery.
        if ($this->relationLoaded('images') && $images->isNotEmpty()) {
            return $this->images()
                ->whereNotIn('id', $images->modelKeys())
                ->orderByDesc('is_primary')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->first(fn (ProductImage $image): bool => filled($image->url));
        }

        return null;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (
            $this->compare_at_price === null ||
            (float) $this->compare_at_price <= (float) $this->price ||
            (float) $this->compare_at_price <= 0
        ) {
            return null;
        }

        return (int) round(
            (
                (float) $this->compare_at_price
                - (float) $this->price
            )
            / (float) $this->compare_at_price
            * 100
        );
    }

    public function scopeActive($query)
    {
        $query->where('status', 'active');

        if (! app()->environment(['local', 'testing'])) {
            $query->where(function ($skuQuery): void {
                $skuQuery->whereNull('sku')
                    ->orWhere(function ($realProductQuery): void {
                        $realProductQuery
                            ->where('sku', 'not like', 'FZ-SEED-%')
                            ->where('sku', 'not like', 'LIV-%');
                    });
            });
        }

        return $query;
    }

    public function scopeFeatured($query)
    {
        return $query
            ->active()
            ->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query
            ->active()
            ->where('is_new', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
