<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AttributeOption;
use App\Models\SeoRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private function englishDigits(string $value): string
    {
        return strtr($value, ['۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9','٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
    }

    public function index(): View
    {
        $categories = Category::query()
            ->visibleInStorefront()
            ->with('hoverMedia')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
        ]);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $category = Category::query()->where('slug', $slug)->first();

        if (! $category) {
            $redirect = SeoRedirect::query()
                ->where('source_path', route('categories.show', $slug, false))
                ->where('is_active', true)
                ->first();

            if ($redirect) {
                return redirect()->to($redirect->target_url, $redirect->status_code);
            }

            abort(404);
        }

        // Empty categories are hidden from every public entry point, including direct URLs.
        abort_unless(
            Category::query()->visibleInStorefront()->whereKey($category->getKey())->exists(),
            404
        );

        $filterableAttributes = $category->attributes()
            ->wherePivot('is_filterable', true)
            ->with('options')
            ->get();
        $productQuery = $category->products()
            ->active()
            ->whereHas('images', fn ($imageQuery) => $imageQuery
                ->where(function ($imageSource): void {
                    $imageSource
                        ->where(fn ($path) => $path->whereNotNull('path')->where('path', '<>', ''))
                        ->orWhereHas('media');
                }));

        $attributeFilters = $request->input('attributes', []);
        if (is_array($attributeFilters)) {
            foreach ($attributeFilters as $attributeId => $selectedValue) {
                $attribute = $filterableAttributes->firstWhere('id', (int) $attributeId);
                if (! $attribute || $selectedValue === '' || $selectedValue === null) continue;

                if ($attribute->type === 'number' && is_array($selectedValue)) {
                    $minimumRaw = $selectedValue['min'] ?? '';
                    $maximumRaw = $selectedValue['max'] ?? '';
                    $minimum = is_scalar($minimumRaw) ? $this->englishDigits((string) $minimumRaw) : '';
                    $maximum = is_scalar($maximumRaw) ? $this->englishDigits((string) $maximumRaw) : '';
                    if ((is_numeric($minimum) || is_numeric($maximum)) && (! is_numeric($minimum) || ! is_numeric($maximum) || (float) $minimum <= (float) $maximum)) {
                        $productQuery->whereHas('attributeValues', function ($values) use ($attribute, $minimum, $maximum): void {
                            $values->where('attribute_id', $attribute->id);
                            if (is_numeric($minimum)) $values->where('value_number', '>=', $minimum);
                            if (is_numeric($maximum)) $values->where('value_number', '<=', $maximum);
                        });
                    }
                    continue;
                }

                if (in_array($attribute->type, ['select', 'multiselect'], true)) {
                    $ids = collect((array) $selectedValue)->filter(fn ($id) => is_scalar($id) && is_numeric($id))->map(fn ($id) => (int) $id)->all();
                    $validIds = AttributeOption::query()->where('attribute_id', $attribute->id)->whereIn('id', $ids)->pluck('id')->all();
                    if ($validIds !== []) $productQuery->whereHas('attributeValues', fn ($values) => $values->where('attribute_id', $attribute->id)->whereIn('option_id', $validIds));
                    continue;
                }

                if ($attribute->type === 'boolean' && is_scalar($selectedValue) && in_array((string) $selectedValue, ['0', '1'], true)) {
                    $productQuery->whereHas('attributeValues', fn ($values) => $values->where('attribute_id', $attribute->id)->where('value_boolean', (int) $selectedValue));
                    continue;
                }

                if (in_array($attribute->type, ['text', 'textarea'], true) && is_scalar($selectedValue)) {
                    $productQuery->whereHas('attributeValues', fn ($values) => $values->where('attribute_id', $attribute->id)->where('value_text', 'like', '%' . addcslashes((string) $selectedValue, '%_') . '%'));
                }
            }
        }

        $comparisonQuery = clone $productQuery;
        $products = $productQuery
            ->with([
                'category.attributes.options',
                'attributeValues.option',
                'images' => fn ($query) => $query
                    ->select(['id', 'product_id', 'media_id', 'path', 'alt', 'sort_order', 'is_primary'])
                    ->orderByDesc('is_primary')
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->limit(5),
                'images.media:id,path,disk',
                'variants:id,product_id,type,name,value,stock,price_adjustment,is_active',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $keyAttributes = $category->attributes()->wherePivot('is_key_spec', true)->with('options')->get();
        $comparisonProducts = $comparisonQuery
            ->whereHas('attributeValues')
            ->with(['category.attributes.options', 'attributeValues.attribute', 'attributeValues.option', 'images.media'])
            ->latest()
            ->limit(4)
            ->get();
        if (! empty($attributeFilters)) {
            $comparisonProducts = collect();
        }


        return view('categories.show', [
            'category' => $category,
            'products' => $products,
            'filterableAttributes' => $filterableAttributes,
            'keyAttributes' => $keyAttributes,
            'comparisonProducts' => $comparisonProducts,
        ]);
    }
}
