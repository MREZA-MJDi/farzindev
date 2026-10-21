@extends('admin.layouts.app')

@section('title', 'تنوع محصولات')
@section('page_title', 'تنوع محصولات')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>
            <span class="text-xs font-medium text-[var(--admin-accent)]">
                CATALOG / PRODUCT VARIANTS
            </span>

            <h2 class="admin-title mt-2">
                تنوع محصولات
            </h2>

            <p class="admin-subtitle mt-2">
                مدیریت ویژگی‌ها، قیمت و موجودی تنوع‌های محصولات
            </p>
        </div>

        <a
            href="{{ route('admin.product-variants.create') }}"
            class="admin-btn admin-btn-primary"
        >
            <svg xmlns="http://www.w3.org/2000/svg"
                 fill="none"
                 viewBox="0 0 24 24"
                 stroke-width="1.5"
                 stroke="currentColor"
                 class="h-5 w-5">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 5.25v13.5M5.25 12h13.5"
                />
            </svg>

            افزودن تنوع
        </a>

    </div>


    {{-- Filters --}}
    <div class="admin-card mb-6 p-5">

        <form
            action="{{ route('admin.product-variants.index') }}"
            method="GET"
            class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
        >

            <div>
                <label for="product_id" class="admin-label">
                    محصول
                </label>

                <select
                    id="product_id"
                    name="product_id"
                    class="admin-select"
                >
                    <option value="">
                        همه محصولات
                    </option>

                    @foreach($products as $product)
                        <option
                            value="{{ $product->id }}"
                            @selected((string) request('product_id') === (string) $product->id)
                        >
                        {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div>
                <label for="type" class="admin-label">
                    نوع ویژگی
                </label>

                <select
                    id="type"
                    name="type"
                    class="admin-select"
                >
                    <option value="">
                        همه انواع
                    </option>

                    @foreach($types as $type)
                        <option
                            value="{{ $type }}"
                            @selected(request('type') === $type)
                        >
                        {{ $type }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div>
                <label for="is_active" class="admin-label">
                    وضعیت
                </label>

                <select
                    id="is_active"
                    name="is_active"
                    class="admin-select"
                >
                    <option value="">
                        همه
                    </option>

                    <option
                        value="1"
                        @selected(request('is_active') === '1')
                    >
                    فعال
                    </option>

                    <option
                        value="0"
                        @selected(request('is_active') === '0')
                    >
                    غیرفعال
                    </option>
                </select>
            </div>


            <div class="flex items-end gap-2">

                <button
                    type="submit"
                    class="admin-btn admin-btn-secondary flex-1"
                >
                    اعمال فیلتر
                </button>

                @if(request()->hasAny(['product_id', 'type', 'is_active']))
                    <a
                        href="{{ route('admin.product-variants.index') }}"
                        class="admin-btn admin-btn-ghost"
                    >
                        پاک
                    </a>
                @endif

            </div>

        </form>

    </div>


    {{-- Table --}}
    <div class="admin-table-wrap">

        <div class="overflow-x-auto">

            <table class="admin-table">

                <thead>
                <tr>
                    <th>#</th>
                    <th>محصول</th>
                    <th>نوع</th>
                    <th>عنوان</th>
                    <th>مقدار</th>
                    <th>SKU</th>
                    <th>تعدیل قیمت</th>
                    <th>موجودی</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>

                @forelse($variants as $variant)

                    <tr>

                        <td>
                            {{ $variants->firstItem() + $loop->index }}
                        </td>


                        <td>

                            @if($variant->product)

                                <a
                                    href="{{ route('admin.products.show', $variant->product) }}"
                                    class="font-semibold text-[var(--admin-text)] transition hover:text-[var(--admin-accent)]"
                                >
                                    {{ $variant->product->name }}
                                </a>

                            @else

                                <span class="text-[var(--admin-muted)]">
                                        محصول حذف شده
                                    </span>

                            @endif

                        </td>


                        <td>
                                <span class="admin-badge admin-badge-neutral">
                                    {{ $variant->type }}
                                </span>
                        </td>


                        <td>
                                <span class="text-sm font-semibold text-[var(--admin-text)]">
                                    {{ $variant->name }}
                                </span>
                        </td>


                        <td>
                                <span class="text-sm text-[var(--admin-text-soft)]">
                                    {{ $variant->value }}
                                </span>
                        </td>


                        <td>
                                <span class="font-mono text-xs text-[var(--admin-text-soft)]">
                                    {{ $variant->sku ?: '—' }}
                                </span>
                        </td>


                        <td>

                            @php
                                $adjustment = (float) $variant->price_adjustment;
                            @endphp

                            @if($adjustment > 0)

                                <span class="text-sm font-semibold text-[var(--admin-success)]">
                                        +{{ \App\Helpers\PersianNumberHelper::format($adjustment) }}
                                    </span>

                                <span class="text-xs text-[var(--admin-muted)]">
                                        تومان
                                    </span>

                            @elseif($adjustment < 0)

                                <span class="text-sm font-semibold text-[var(--admin-danger)]">
                                        {{ \App\Helpers\PersianNumberHelper::format($adjustment) }}
                                    </span>

                                <span class="text-xs text-[var(--admin-muted)]">
                                        تومان
                                    </span>

                            @else

                                <span class="text-sm text-[var(--admin-muted)]">
                                        بدون تغییر
                                    </span>

                            @endif

                        </td>


                        <td>

                            @if($variant->stock > 0)

                                <span class="admin-badge admin-badge-success">
                                        {{ \App\Helpers\PersianNumberHelper::format($variant->stock) }}
                                    </span>

                            @else

                                <span class="admin-badge admin-badge-danger">
                                        ناموجود
                                    </span>

                            @endif

                        </td>


                        <td>

                            @if($variant->is_active)

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
                                    href="{{ route('admin.product-variants.show', $variant) }}"
                                    class="admin-btn admin-btn-ghost px-3"
                                >
                                    مشاهده
                                </a>

                                <a
                                    href="{{ route('admin.product-variants.edit', $variant) }}"
                                    class="admin-btn admin-btn-secondary px-3"
                                >
                                    ویرایش
                                </a>

                                <form
                                    action="{{ route('admin.product-variants.destroy', $variant) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از حذف این تنوع مطمئن هستید؟');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="admin-btn admin-btn-danger px-3"
                                    >
                                        حذف
                                    </button>
                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="10">

                            <div class="admin-empty">

                                <div class="admin-empty-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         fill="none"
                                         viewBox="0 0 24 24"
                                         stroke-width="1.5"
                                         stroke="currentColor"
                                         class="h-6 w-6">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4.5 6.75h15M4.5 12h15M4.5 17.25h15"
                                        />
                                    </svg>
                                </div>

                                <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                    هنوز تنوعی ثبت نشده است.
                                </h3>

                                <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                    برای شروع، اولین تنوع محصول را ایجاد کنید.
                                </p>

                                <a
                                    href="{{ route('admin.product-variants.create') }}"
                                    class="admin-btn admin-btn-primary mt-5"
                                >
                                    افزودن تنوع
                                </a>

                            </div>

                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    @if($variants->hasPages())

        <div class="mt-6">
            {{ $variants->links() }}
        </div>

    @endif

@endsection
