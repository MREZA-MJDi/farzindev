<div class="admin-card p-6">

    <div class="mb-6">

        <h3 class="text-base font-bold text-[var(--admin-text)]">
            اقلام سفارش
        </h3>

        <p class="mt-1 text-xs text-[var(--admin-muted)]">
            محصولاتی که در این سفارش ثبت شده‌اند.
        </p>

    </div>


    <div class="admin-table-wrap">

        <div class="overflow-x-auto">

            <table class="admin-table">

                <thead>
                <tr>
                    <th>محصول</th>
                    <th>تنوع</th>
                    <th>تعداد</th>
                    <th>قیمت واحد</th>
                    <th>مجموع</th>
                </tr>
                </thead>

                <tbody>

                @forelse($order->items as $item)

                    @php
                        $lineTotal = (float) $item->unit_price * (int) $item->quantity;
                    @endphp

                    <tr>

                        <td>

                            @if($item->product)

                                <a
                                    href="{{ route('admin.products.show', $item->product) }}"
                                    class="font-semibold text-[var(--admin-text)] hover:text-[var(--admin-accent)]"
                                >
                                    {{ $item->product->name }}
                                </a>

                            @else

                                <span class="text-[var(--admin-muted)]">
                                        محصول حذف شده
                                    </span>

                            @endif

                        </td>


                        <td>

                            @if($item->productVariant)

                                <div class="text-sm text-[var(--admin-text-soft)]">
                                    {{ $item->productVariant->name }}
                                </div>

                                <div class="mt-1 text-xs text-[var(--admin-muted)]">
                                    {{ $item->productVariant->value }}
                                </div>

                            @else

                                <span class="text-[var(--admin-muted)]">
                                        —

                                    </span>

                            @endif

                        </td>


                        <td>
                            {{ \App\Helpers\PersianNumberHelper::format((int) $item->quantity) }}
                        </td>


                        <td>

                                <span class="font-semibold text-[var(--admin-text)]">
                                    {{ \App\Helpers\PersianNumberHelper::format((float) $item->unit_price) }}
                                </span>

                            <span class="text-xs text-[var(--admin-muted)]">
                                    تومان
                                </span>

                        </td>


                        <td>

                                <span class="font-semibold text-[var(--admin-text)]">
                                    {{ \App\Helpers\PersianNumberHelper::format($lineTotal) }}
                                </span>

                            <span class="text-xs text-[var(--admin-muted)]">
                                    تومان
                                </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5">

                            <div class="admin-empty py-8">

                                <p class="text-xs text-[var(--admin-muted)]">
                                    این سفارش فاقد آیتم است.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
