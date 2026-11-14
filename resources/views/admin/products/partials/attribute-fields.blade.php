@php
    $attributeDefinitions = $categories->mapWithKeys(function ($category) {
        return [$category->id => $category->attributes->map(function ($attribute) {
            return [
                'id' => $attribute->id,
                'key' => $attribute->key,
                'label' => $attribute->label,
                'type' => $attribute->type,
                'unit' => $attribute->unit,
                'group' => $attribute->group,
                'help_text' => $attribute->help_text,
                'required' => (bool) $attribute->pivot->is_required,
                'recommended' => (bool) $attribute->pivot->is_recommended,
                'filterable' => (bool) $attribute->pivot->is_filterable,
                'key_spec' => (bool) $attribute->pivot->is_key_spec,
                'options' => $attribute->options->map(fn ($option) => ['id' => $option->id, 'label' => $option->label])->values(),
            ];
        })->values()];
    });

    $initialAttributeValues = old('attribute_values');
    if (!is_array($initialAttributeValues)) {
        $initialAttributeValues = [];
        foreach (($product->attributeValues ?? collect())->groupBy('attribute_id') as $attributeId => $rows) {
            $first = $rows->first();
            if ($first->option_id !== null) {
                $initialAttributeValues[$attributeId] = $rows->count() > 1
                    ? ['option_ids' => $rows->pluck('option_id')->all()]
                    : ['option_id' => $first->option_id];
            } elseif ($first->value_number !== null) {
                $initialAttributeValues[$attributeId] = ['value' => $first->value_number];
            } elseif ($first->value_boolean !== null) {
                $initialAttributeValues[$attributeId] = ['value' => $first->value_boolean ? '1' : '0'];
            } else {
                $initialAttributeValues[$attributeId] = ['value' => $first->value_text];
            }
        }
    }
@endphp

<section
    id="typed-product-attributes"
    class="sm:col-span-2 rounded-2xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5"
    data-category-attributes='@json($attributeDefinitions)'
    data-initial-values='@json($initialAttributeValues)'
>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-bold text-[var(--admin-text)]">مشخصات فنی ساختاریافته</h3>
            <p class="mt-1 text-xs leading-6 text-[var(--admin-muted)]">فیلدها بر اساس دسته‌بندی انتخاب‌شده ساخته می‌شوند. ویژگی‌های اجباری فقط هنگام انتشار محصول باید تکمیل شوند؛ پیش‌نویس را می‌توانید ناقص ذخیره کنید.</p>
        </div>
        <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-card)] px-3 py-2 text-xs font-semibold text-[var(--admin-text)]" aria-live="polite">
            تکمیل ویژگی‌های الزامی: <span id="attribute-completeness">۰ از ۰</span>
        </div>
    </div>
    <div id="typed-attribute-groups" class="mt-4 space-y-5" aria-live="polite"></div>
    <p class="mt-3 text-[11px] leading-6 text-[var(--admin-muted)]">با تغییر دسته‌بندی، مقادیر ویژگی‌های مشترک حفظ می‌شوند. پیش از حذف مقادیر اختصاصی دسته قبلی، پیام تأیید نمایش داده می‌شود.</p>
    @foreach($errors->getMessages() as $errorKey => $messages)
        @if(str_starts_with($errorKey, 'attribute_values.'))
            @foreach($messages as $message)
                <p class="mt-2 text-xs text-[var(--admin-danger)]" role="alert">{{ $message }}</p>
            @endforeach
        @endif
    @endforeach
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('typed-product-attributes');
    const category = document.getElementById('category_id');
    const groups = document.getElementById('typed-attribute-groups');
    const completeness = document.getElementById('attribute-completeness');
    if (!root || !category || !groups || !completeness) return;

    const definitions = JSON.parse(root.dataset.categoryAttributes || '{}');
    let values = JSON.parse(root.dataset.initialValues || '{}');
    let previousCategory = String(category.value || '');

    const englishDigits = value => String(value ?? '').replace(/[۰-۹٠-٩]/g, digit => {
        const fa = '۰۱۲۳۴۵۶۷۸۹';
        const ar = '٠١٢٣٤٥٦٧٨٩';
        const index = fa.indexOf(digit);
        return index >= 0 ? String(index) : String(ar.indexOf(digit));
    });

    const currentValues = () => {
        const result = {};
        groups.querySelectorAll('[data-attribute-id]').forEach(field => {
            const id = field.dataset.attributeId;
            const type = field.dataset.attributeType;
            if (type === 'multiselect') {
                result[id] = {option_ids: [...field.querySelectorAll('input:checked')].map(input => input.value)};
            } else if (type === 'select') {
                result[id] = {option_id: field.querySelector('select')?.value || ''};
            } else {
                result[id] = {value: field.querySelector('input,textarea,select')?.value ?? ''};
            }
        });
        return result;
    };

    const hasValue = input => {
        if (!input) return false;
        if (Array.isArray(input.option_ids)) return input.option_ids.length > 0;
        return Boolean(String(input.option_id ?? input.value ?? '').trim());
    };

    const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    };

    const render = () => {
        groups.replaceChildren();
        const attributes = definitions[String(category.value)] || [];
        if (!attributes.length) {
            groups.append(element('p', 'rounded-xl border border-dashed border-[var(--admin-border)] bg-[var(--admin-card)] p-4 text-xs text-[var(--admin-muted)]', 'برای این دسته‌بندی هنوز ویژگی فنی تعریف نشده است.'));
            completeness.textContent = '۰ از ۰';
            return;
        }

        const grouped = new Map();
        attributes.forEach(attribute => {
            const group = attribute.group || 'مشخصات عمومی';
            if (!grouped.has(group)) grouped.set(group, []);
            grouped.get(group).push(attribute);
        });

        let requiredCount = 0;
        let completedCount = 0;
        grouped.forEach((items, groupName) => {
            const section = element('section', 'rounded-xl border border-[var(--admin-border)] bg-[var(--admin-card)] p-4');
            section.append(element('h4', 'mb-3 text-xs font-bold text-[var(--admin-text)]', groupName));
            const grid = element('div', 'grid grid-cols-1 gap-4 sm:grid-cols-2');
            items.forEach(attribute => {
                const id = String(attribute.id);
                const saved = values[id] || {};
                const wrapper = element('div', 'min-w-0');
                wrapper.dataset.attributeId = id;
                wrapper.dataset.attributeType = attribute.type;
                const label = element('label', 'mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-[var(--admin-text)]', attribute.label + (attribute.unit ? ' (' + attribute.unit + ')' : ''));
                if (attribute.required) {
                    requiredCount++;
                    if (hasValue(saved)) completedCount++;
                    label.append(element('span', 'rounded-full bg-rose-50 px-2 py-1 text-[10px] text-rose-700', 'اجباری برای انتشار'));
                } else if (attribute.recommended) {
                    label.append(element('span', 'rounded-full bg-amber-50 px-2 py-1 text-[10px] text-amber-800', 'پیشنهادی'));
                }
                if (attribute.key_spec) label.append(element('span', 'rounded-full bg-sky-50 px-2 py-1 text-[10px] text-sky-700', 'مشخصه کلیدی'));
                wrapper.append(label);

                if (attribute.type === 'select') {
                    const select = element('select', 'admin-select w-full');
                    select.name = 'attribute_values[' + id + '][option_id]';
                    select.append(new Option('انتخاب کنید', ''));
                    (attribute.options || []).forEach(option => select.append(new Option(option.label, option.id)));
                    select.value = saved.option_id || '';
                    wrapper.append(select);
                } else if (attribute.type === 'multiselect') {
                    const list = element('div', 'grid grid-cols-1 gap-2 rounded-lg border border-[var(--admin-border)] p-3 sm:grid-cols-2');
                    const selected = (saved.option_ids || []).map(String);
                    (attribute.options || []).forEach(option => {
                        const optionLabel = element('label', 'flex items-center gap-2 text-xs text-[var(--admin-text)]');
                        const input = document.createElement('input');
                        input.type = 'checkbox';
                        input.name = 'attribute_values[' + id + '][option_ids][]';
                        input.value = option.id;
                        input.checked = selected.includes(String(option.id));
                        input.className = 'admin-checkbox';
                        optionLabel.append(input, document.createTextNode(option.label));
                        list.append(optionLabel);
                    });
                    wrapper.append(list);
                } else if (attribute.type === 'boolean') {
                    const select = element('select', 'admin-select w-full');
                    select.name = 'attribute_values[' + id + '][value]';
                    select.append(new Option('انتخاب کنید', ''), new Option('بله', '1'), new Option('خیر', '0'));
                    select.value = saved.value ?? '';
                    wrapper.append(select);
                } else if (attribute.type === 'textarea') {
                    const textarea = element('textarea', 'admin-textarea w-full');
                    textarea.rows = 3;
                    textarea.name = 'attribute_values[' + id + '][value]';
                    textarea.value = saved.value ?? '';
                    wrapper.append(textarea);
                } else {
                    const input = element('input', 'admin-input w-full');
                    input.type = attribute.type === 'number' ? 'text' : 'text';
                    input.inputMode = attribute.type === 'number' ? 'decimal' : 'text';
                    input.name = 'attribute_values[' + id + '][value]';
                    input.value = saved.value ?? '';
                    input.maxLength = 2000;
                    wrapper.append(input);
                }
                if (attribute.help_text) wrapper.append(element('p', 'mt-1 text-[11px] leading-5 text-[var(--admin-muted)]', attribute.help_text));
                grid.append(wrapper);
            });
            section.append(grid);
            groups.append(section);
        });
        completeness.textContent = String(completedCount).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]) + ' از ' + String(requiredCount).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    };

    category.addEventListener('change', function () {
        const nextCategory = String(category.value || '');
        const current = currentValues();
        const nextIds = new Set((definitions[nextCategory] || []).map(item => String(item.id)));
        const removedValues = Object.entries(current).filter(([id, input]) => !nextIds.has(id) && hasValue(input));
        if (removedValues.length && !window.confirm('با تغییر دسته‌بندی، ' + removedValues.length + ' مقدار اختصاصی دسته قبلی حذف می‌شود. ویژگی‌های مشترک حفظ خواهند شد. ادامه می‌دهید؟')) {
            category.value = previousCategory;
            return;
        }
        values = {...values, ...current};
        Object.keys(values).forEach(id => { if (!nextIds.has(id)) delete values[id]; });
        previousCategory = nextCategory;
        render();
    });

    groups.addEventListener('input', () => {
        values = {...values, ...currentValues()};
        renderCompletenessOnly();
    });
    groups.addEventListener('change', () => {
        values = {...values, ...currentValues()};
        renderCompletenessOnly();
    });

    function renderCompletenessOnly() {
        const attributes = definitions[String(category.value)] || [];
        const required = attributes.filter(item => item.required);
        const complete = required.filter(item => hasValue(currentValues()[String(item.id)])).length;
        completeness.textContent = String(complete).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]) + ' از ' + String(required.length).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
    }

    root.closest('form')?.addEventListener('submit', () => {
        values = {...values, ...currentValues()};
        groups.querySelectorAll('[data-attribute-type="number"] input').forEach(input => { input.value = englishDigits(input.value); });
    });

    render();
});
</script>
@endpush
