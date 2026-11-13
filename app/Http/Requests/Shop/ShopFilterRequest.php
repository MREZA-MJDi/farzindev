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
                    'available_first',
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
            'attributes' => ['nullable', 'array', 'max:30'],

            'page' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];
    }

    private function digits(string $value): string
    {
        return strtr($value, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
    }

    protected function prepareForValidation(): void
    {
        $attributes = $this->input('attributes', []);
        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                if (is_array($value)) {
                    foreach (['min', 'max'] as $bound) {
                        if (isset($value[$bound])) $value[$bound] = $this->digits((string) $value[$bound]);
                    }
                    $attributes[$key] = $value;
                } elseif (is_scalar($value)) {
                    $attributes[$key] = $this->digits((string) $value);
                }
            }
        }

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
            'max_price' => $this->filled('max_price')
                ? $this->digits((string) $this->input('max_price'))
                : null,
            'min_price' => $this->filled('min_price')
                ? $this->digits((string) $this->input('min_price'))
                : null,

            /*
            |--------------------------------------------------------------------------
            | Boolean
            |--------------------------------------------------------------------------
            */
            'in_stock' => $this->has('in_stock') ? $this->boolean('in_stock') : true,
            'featured' => $this->boolean('featured'),
            'new' => $this->boolean('new'),
            'installment' => $this->boolean('installment'),
            'attributes' => $attributes,
        ]);
    }
}
