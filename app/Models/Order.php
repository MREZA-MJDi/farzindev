<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',

        'status',
        'payment_status',

        'payment_method',
        'payment_provider',

        'subtotal',
        'shipping_cost',
        'discount',
        'total',

        'first_name',
        'last_name',
        'phone',
        'email',

        'province',
        'city',
        'address',
        'postal_code',
        'unit',

        'notes',
        'inventory_reserved_at',
        'inventory_released_at',
        'inventory_committed_at',
        'inventory_issue_at',
        'inventory_issue_reason',

        /*
         * Internal installment snapshot
         */
        'installment_enabled',
        'installment_cash_percent',
        'installment_cash_amount',
        'installment_deferred_amount',
        'installment_remainder_method',
        'installment_cheque_count',
        'installment_interval_months',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'inventory_reserved_at' => 'datetime',
            'inventory_released_at' => 'datetime',
            'inventory_committed_at' => 'datetime',
            'inventory_issue_at' => 'datetime',

            'installment_enabled' => 'boolean',

            'installment_cash_percent' => 'integer',

            'installment_cash_amount' => 'decimal:2',
            'installment_deferred_amount' => 'decimal:2',

            'installment_cheque_count' => 'integer',
            'installment_interval_months' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)
            ->latestOfMany();
    }

    public function installments(): HasMany
    {
        return $this->hasMany(OrderInstallment::class)
            ->orderBy('sequence');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' . $this->last_name
        );
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->province,
            $this->city,
            $this->address,
        ];

        if (
            $this->unit !== null
            && $this->unit !== ''
        ) {
            $parts[] = 'واحد ' . $this->unit;
        }

        return implode(
            '، ',
            array_filter($parts)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payment Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isInstallment(): bool
    {
        return $this->payment_method === 'installment';
    }

    public function isInternalInstallment(): bool
    {
        return $this->isInstallment()
            && $this->payment_provider === 'livora';
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeProcessing($query)
    {
        return $query->where(
            'status',
            'processing'
        );
    }

    public function scopePaid($query)
    {
        return $query->where(
            'payment_status',
            'paid'
        );
    }

    public function scopeInstallment($query)
    {
        return $query->where(
            'payment_method',
            'installment'
        );
    }

    public function scopeInternalInstallment($query)
    {
        return $query
            ->where(
                'payment_method',
                'installment'
            )
            ->where(
                'payment_provider',
                'livora'
            );
    }
}
