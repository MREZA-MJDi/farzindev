<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'name',
        'value',
        'description',
        'color_hex',
        'sku',
        'price_adjustment',
        'stock',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_adjustment' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductImage::class,
            'product_variant_images',
            'product_variant_id',
            'product_image_id'
        )
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->orderBy('product_images.sort_order');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
