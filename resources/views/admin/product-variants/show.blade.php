@extends('admin.layouts.app')

@section('title', 'جزئیات تنوع')
@section('page_title', 'جزئیات تنوع محصول')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>

            <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
                CATALOG / PRODUCT VARIANTS / VIEW
            </div>

            <h2 class="admin-title">
                {{ $productVariant->name }}
            </h2>

            <p class="admin-subtitle mt-2">
                جزئیات تنوع محصول
            </p>

        </div>


        <div class="flex flex-wrap gap-2">

            <a
                href="{{ route('admin.product-variants.edit', $productVariant) }}"
                class="admin-btn admin-btn-primary"
            >
                ویرایش
            </a>

            @if($productVariant->product)

                <a
                    href="{{ route('admin.products.show', $productVariant->product) }}"
                    class="admin-btn admin-btn-secondary"
                >
                    مشاهده محصول
                </a>

            @endif

            <a
                href="{{ route('admin.product-variants.index') }}"
                class="admin-btn admin-btn-secondary"
            >
                بازگشت
            </a>

        </div>

    </div>


    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Main --}}
        <div class="space-y-6 xl:col-span-2">

            <div class="admin-card p-6">

                <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">

                    <div>

                        <h3 class="text-base font-bold text-[var(--admin-text)]">
                            اطلاعات تنوع
                        </h3>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            مشخصات اصلی
                        </p>

                    </div>


                    @if($productVariant->is_active)

                        <span class="admin-badge admin-badge-success">
                            فعال
                        </span>

                    @else

                        <span class="admin-badge admin-badge-neutral">
                            غیرفعال
                        </span>

                    @endif

                </div>


                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    <div>
                        <dt class="admin-stat-label">
                            محصول
                        </dt>

                        <dd class="mt-2">

                            @if($productVariant->product)

                                <a
                                    href="{{ route('admin.products.show', $productVariant->product) }}"
                                    class="text-sm font-semibold text-[var(--admin-accent)] hover:text-[var(--admin-accent-hover)]"
                                >
                                    {{ $productVariant->product->name }}
                                </a>

                            @else

                                <span class="text-sm text-[var(--admin-muted)]">
                                    محصول حذف شده
                                </span>

                            @endif

                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            نوع ویژگی
                        </dt>

                        <dd class="mt-2">
                            <span class="admin-badge admin-badge-neutral">
                                {{ $productVariant->type }}
                            </span>
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            نام ویژگی
                        </dt>

                        <dd class="mt-2 text-sm font-semibold text-[var(--admin-text)]">
                            {{ $productVariant->name }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            مقدار
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $productVariant->value }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            SKU
                        </dt>

                        <dd class="mt-2 break-all font-mono text-sm text-[var(--admin-text-soft)]">
                            {{ $productVariant->sku ?: '—' }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            موجودی
                        </dt>

                        <dd class="mt-2 text-sm font-semibold text-[var(--admin-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($productVariant->stock) }}
                        </dd>
                    </div>

                </dl>

            </div>


            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    قیمت
                </h3>

                <div class="mt-5 rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5">

                    @php
                        $adjustment = (float) $productVariant->price_adjustment;
                    @endphp

                    <p class="text-xs text-[var(--admin-muted)]">
                        تعدیل قیمت نسبت به محصول اصلی
                    </p>

                    <p class="mt-2 text-2xl font-bold">

                        @if($adjustment > 0)

                            <span class="text-[var(--admin-success)]">
                                +{{ \App\Helpers\PersianNumberHelper::format($adjustment) }}
                            </span>

                        @elseif($adjustment < 0)

                            <span class="text-[var(--admin-danger)]">
                                {{ \App\Helpers\PersianNumberHelper::format($adjustment) }}
                            </span>

                        @else

                            <span class="text-[var(--admin-text-soft)]">
                                0
                            </span>

                        @endif

                        <span class="text-xs font-normal text-[var(--admin-muted)]">
                            تومان
                        </span>

                    </p>

                </div>

            </div>

        </div>


        {{-- Side --}}
        <div class="space-y-6">

            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    خلاصه
                </h3>

                <dl class="mt-6 space-y-5">

                    <div>
                        <dt class="admin-stat-label">
                            وضعیت
                        </dt>

                        <dd class="mt-2">

                            @if($productVariant->is_active)

                                <span class="admin-badge admin-badge-success">
                                    فعال
                                </span>

                            @else

                                <span class="admin-badge admin-badge-neutral">
                                    غیرفعال
                                </span>

                            @endif

                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            موجودی
                        </dt>

                        <dd class="mt-2 text-sm font-semibold text-[var(--admin-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($productVariant->stock) }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            تاریخ ایجاد
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $productVariant->created_at ? \App\Helpers\JalaliHelper::format($productVariant->created_at, 'Y/m/d H:i') : '—' }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            آخرین بروزرسانی
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $productVariant->updated_at ? \App\Helpers\JalaliHelper::format($productVariant->updated_at, 'Y/m/d H:i') : '—' }}
                        </dd>
                    </div>

                </dl>

            </div>


            <div class="admin-card p-6">

                <h3 class="text-sm font-bold text-[var(--admin-text)]">
                    حذف تنوع
                </h3>

                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                    این عملیات قابل بازگشت نیست.
                </p>

                <form
                    action="{{ route('admin.product-variants.destroy', $productVariant) }}"
                    method="POST"
                    class="mt-5"
                    onsubmit="return confirm('آیا از حذف این تنوع مطمئن هستید؟');"
                >

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="admin-btn admin-btn-danger w-full"
                    >
                        حذف تنوع
                    </button>

                </form>

            </div>

        </div>

    </div>

@endsection
