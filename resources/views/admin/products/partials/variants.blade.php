<div class="admin-card p-6">

    <div class="mb-6 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

        <div>
            <h3 class="text-base font-bold text-[var(--admin-text)]">
                تنوع‌های محصول
            </h3>

            <p class="mt-1 text-xs text-[var(--admin-muted)]">
                مدیریت رنگ، اندازه، قیمت و موجودی تنوع‌های محصول.
            </p>
        </div>

        <a
            href="{{ route('admin.product-variants.create', ['product_id' => $product->id]) }}"
            class="admin-btn admin-btn-secondary"
        >
            افزودن تنوع
        </a>

    </div>


    @if($product->allVariants->count())

        <div class="admin-table-wrap">

            <div class="overflow-x-auto">

                <table class="admin-table">

                    <thead>
                    <tr>
                        <th>عنوان</th>
                        <th>SKU</th>
                        <th>قیمت</th>
                        <th>موجودی</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($product->allVariants as $variant)

                        <tr>

                            <td>
                                <div class="font-semibold text-[var(--admin-text)]">
                                    {{ $variant->name ?? 'تنوع محصول' }}
                                </div>

                                <div class="mt-1 text-xs text-[var(--admin-muted)]">
                                    {{ $variant->value }}
                                </div>

                                @if($variant->color_hex)
                                    <span
                                        class="mt-2 inline-block h-4 w-4 rounded-full border border-black/10 align-middle"
                                        style="background-color: {{ $variant->color_hex }};"
                                        title="{{ $variant->color_hex }}"
                                    ></span>
                                @endif
                            </td>

                            <td>
                                    <span class="font-mono text-xs text-[var(--admin-text-soft)]">
                                        {{ $variant->sku ?? '—' }}
                                    </span>
                            </td>

                            <td>
                                @php
                                    $priceAdjustment = (float) $variant->price_adjustment;
                                @endphp

                                @if($priceAdjustment > 0)
                                    +{{ \App\Helpers\PersianNumberHelper::format($priceAdjustment) }}
                                    <span class="text-xs text-[var(--admin-muted)]">
                                        تومان
                                    </span>
                                @elseif($priceAdjustment < 0)
                                    {{ \App\Helpers\PersianNumberHelper::format($priceAdjustment) }}
                                    <span class="text-xs text-[var(--admin-muted)]">
                                        تومان
                                    </span>
                                @else
                                    بدون تغییر
                                @endif
                            </td>

                            <td>
                                {{ \App\Helpers\PersianNumberHelper::format((int) ($variant->stock ?? 0)) }}
                            </td>

                            <td>

                                @if($variant->is_active ?? true)

                                    <span class="admin-badge admin-badge-success">
                                            فعال
                                        </span>

                                @else

                                    <span class="admin-badge admin-badge-neutral">
                                            غیرفعال
                                        </span>

                                @endif

                            </td>

                            <td>

                                <div class="flex items-center justify-end gap-2">

                                    <a
                                        href="{{ route('admin.product-variants.edit', $variant) }}"
                                        class="admin-btn admin-btn-secondary px-3"
                                    >
                                        ویرایش
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    @else

        <div class="admin-empty">

            <div class="admin-empty-icon">

                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                    class="h-6 w-6"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15"
                    />
                </svg>

            </div>

            <h4 class="text-sm font-semibold text-[var(--admin-text)]">
                هنوز تنوعی برای این محصول ثبت نشده است.
            </h4>

            <p class="mt-2 text-xs text-[var(--admin-muted)]">
                برای این محصول اولین تنوع را ایجاد کنید.
            </p>

            <a
                href="{{ route('admin.product-variants.create', ['product_id' => $product->id]) }}"
                class="admin-btn admin-btn-primary mt-5"
            >
                افزودن تنوع
            </a>

        </div>

    @endif

</div>
