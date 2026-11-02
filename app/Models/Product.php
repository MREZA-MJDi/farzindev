<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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
        'width_cm',
        'installation_type',
        'suction_power_m3h',
        'noise_db',
        'power_watts',
        'material',
        'warranty_months',
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
            'stock' => 'integer',
            'width_cm' => 'integer',
            'installation_type' => 'string',
            'suction_power_m3h' => 'integer',
            'noise_db' => 'decimal:1',
            'power_watts' => 'integer',
            'material' => 'string',
            'warranty_months' => 'integer',

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



    /**
     * Exclude known preview, demo and test catalogue entries from public pages.
     * Rows stay in the database for audit/history; this is a visibility scope,
     * not destructive cleanup.
     */
    public function scopePublicCatalogue(Builder $query): Builder
    {
        return $query
            ->where('sku', 'not like', '%SEED%')
            ->where('sku', 'not like', '%DEMO%')
            ->where('slug', 'not like', '%preview%')
            ->where(function (Builder $name): void {
                $name
                    ->where('name', 'not like', '%تی‌شرت%')
                    ->where('name', 'not like', '%تی شرت%')
                    ->where('name', 'not like', '%تیشرت%')
                    ->where('name', 'not like', '%t-shirt%')
                    ->where('name', 'not like', '%میز%');
            })
            ->whereDoesntHave('category', function ($category): void {
                $category
                    ->whereIn('slug', ['furniture', 'chairs', 'tables', 'decor', 'accessories'])
                    ->orWhere('name', 'like', '%میز%');
            });
    }

    /**
     * Products that can be purchased with the current base or option stock.
     * For variant-backed products, every active option type must have at least
     * one in-stock option; a single available colour must not hide an empty size group.
     */
    public function scopeAvailableForPurchase(Builder $query): Builder
    {
        $table = $this->getTable();

        return $query->where(function (Builder $available) use ($table): void {
            $available
                ->where(function (Builder $baseStock) use ($table): void {
                    $baseStock
                        ->whereDoesntHave('variants')
                        ->where($table . '.stock', '>', 0);
                })
                ->orWhere(function (Builder $variantStock) use ($table): void {
                    $variantStock
                        ->whereHas('variants')
                        ->whereNotExists(function ($emptyOptionType) use ($table): void {
                            $emptyOptionType
                                ->selectRaw('1')
                                ->from('product_variants as availability_options')
                                ->whereColumn('availability_options.product_id', $table . '.id')
                                ->where('availability_options.is_active', true)
                                ->groupBy('availability_options.type')
                                ->havingRaw('SUM(CASE WHEN availability_options.stock > 0 THEN 1 ELSE 0 END) = 0');
                        });
                });
        });
    }

    /**
     * Put purchasable products before unavailable ones without changing the
     * caller's secondary ordering or filtering out unavailable catalogue items.
     */
    public function scopeOrderByAvailability(Builder $query): Builder
    {
        $availableIds = static::query()
            ->select($this->getTable() . '.id')
            ->availableForPurchase();

        return $query->orderByRaw(
            $this->getTable() . '.id IN (' . $availableIds->toSql() . ') DESC',
            $availableIds->getBindings()
        );
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
