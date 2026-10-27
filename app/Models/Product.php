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
        return $this->images()
                ->where('is_primary', true)
                ->first()
            ?? $this->images()->first();
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
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query
            ->where('status', 'active')
            ->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query
            ->where('status', 'active')
            ->where('is_new', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
