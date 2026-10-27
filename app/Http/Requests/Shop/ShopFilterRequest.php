<?php

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            | Category is filtered by SLUG, never by ID.
            |
            | Example:
            | /shop?category=sofa
            |--------------------------------------------------------------------------
            */
            'category' => [
                'nullable',
                'string',
                'max:100',
                Rule::exists('categories', 'slug')
                    ->where('is_active', true),
            ],

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */
            'search' => [
                'nullable',
                'string',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | Price
            |--------------------------------------------------------------------------
            */
            'min_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_price' => [
                'nullable',
                'numeric',
                'min:0',
                ...($this->filled('min_price') ? ['gte:min_price'] : []),
            ],

            /*
            |--------------------------------------------------------------------------
            | Stock
            |--------------------------------------------------------------------------
            */
            'in_stock' => [
                'nullable',
                'boolean',
            ],
            'featured' => [
                'nullable',
                'boolean',
            ],
            'new' => [
                'nullable',
                'boolean',
            ],
            'installment' => [
                'nullable',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Sorting
            |--------------------------------------------------------------------------
            */
            'sort' => [
                'nullable',
                Rule::in([
                    'newest',
                    'popular',
                    'price_asc',
                    'price_desc',
                    'name_asc',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | Pagination
            |--------------------------------------------------------------------------
            */
            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            | Keep category as slug.
            | Empty values become null.
            |--------------------------------------------------------------------------
            */
            'category' => $this->filled('category')
                ? trim((string) $this->input('category'))
                : null,

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */
            'search' => $this->filled('search')
                ? trim((string) $this->input('search'))
                : null,

            /*
            |--------------------------------------------------------------------------
            | Price
            |--------------------------------------------------------------------------
            */
            'min_price' => $this->filled('min_price')
                ? $this->input('min_price')
                : null,

            'max_price' => $this->filled('max_price')
                ? $this->input('max_price')
                : null,

            /*
            |--------------------------------------------------------------------------
            | Boolean
            |--------------------------------------------------------------------------
            */
            'in_stock' => $this->boolean('in_stock'),
            'featured' => $this->boolean('featured'),
            'new' => $this->boolean('new'),
            'installment' => $this->boolean('installment'),
        ]);
    }
}
