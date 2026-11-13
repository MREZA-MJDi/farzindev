@if($comparisonProducts->count() >= 2 && $keyAttributes->isNotEmpty())
<section class="border-t border-[var(--livora-border)] bg-[var(--livora-surface)]">
    <x-layout.container>
        <div class="py-12 sm:py-16">
            <p class="text-[10px] font-semibold tracking-[0.16em] text-[var(--livora-accent)]">مقایسه مشخصات</p>
            <h2 class="mt-2 text-2xl font-black text-[var(--livora-ink)]">مقایسه چند مدل {{ $category->name }}</h2>
            <p class="mt-2 text-sm leading-7 text-[var(--livora-stone)]">فقط ویژگی‌های کلیدی ثبت‌شده برای محصولات این دسته نمایش داده می‌شوند.</p>
            <div class="mt-6 overflow-x-auto rounded-2xl border border-[var(--livora-border)] bg-white">
                <table class="min-w-full text-right text-xs">
                    <thead><tr class="border-b border-[var(--livora-border)] bg-[var(--livora-surface)]"><th class="min-w-36 px-4 py-4">مشخصه</th>@foreach($comparisonProducts as $comparisonProduct)<th class="min-w-44 px-4 py-4 font-bold">{{ $comparisonProduct->name }}<a class="mt-2 block font-medium text-[var(--livora-accent)]" href="{{ route('product.show',$comparisonProduct->slug) }}">مشاهده محصول</a></th>@endforeach</tr></thead>
                    <tbody>
                    @foreach($keyAttributes as $attribute)
                        <tr class="border-b border-[var(--livora-border)] last:border-0">
                            <th class="bg-[var(--livora-surface)] px-4 py-4 font-semibold">{{ $attribute->label }} @if($attribute->unit) ({{ $attribute->unit }}) @endif</th>
                            @foreach($comparisonProducts as $comparisonProduct)
                                @php
                                    $attributeRows = $comparisonProduct->attributeValues->where('attribute_id',$attribute->id);
                                    $attributeText = $attributeRows->map(function($row) use ($attribute) {
                                        if($row->option) return $row->option->label;
                                        if($row->value_boolean !== null) return $row->value_boolean ? 'بله' : 'خیر';
                                        if($row->value_number !== null) return \App\Helpers\PersianNumberHelper::format($row->value_number) . ($attribute->unit ? ' ' . $attribute->unit : '');
                                        return trim((string)$row->value_text);
                                    })->filter(fn($value)=>$value!=='')->unique()->join('، ');
                                @endphp
                                <td class="px-4 py-4 text-[var(--livora-text)]">{{ $attributeText !== '' ? $attributeText : '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-layout.container>
</section>
@endif
