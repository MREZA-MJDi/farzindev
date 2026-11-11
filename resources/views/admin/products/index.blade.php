{{-- resources/views/admin/products/index.blade.php --}}

@extends('admin.layouts.app')

@section('title', 'محصولات')
@section('page_title', 'محصولات')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h2 class="admin-title mt-2">
                محصولات
            </h2>

            <p class="admin-subtitle mt-2">
                مدیریت محصولات، قیمت، موجودی و وضعیت انتشار
            </p>
        </div>

        <a
            href="{{ route('admin.products.create') }}"
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

            افزودن محصول
        </a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="admin-card mb-6 grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 xl:grid-cols-6" role="search" aria-label="جستجو و فیلتر محصولات">
        <label class="block xl:col-span-2">
            <span class="mb-1 block text-xs font-semibold text-[var(--admin-muted)]">جستجو در نام یا SKU</span>
            <input type="search" name="q" value="{{ request('q') }}" maxlength="255" placeholder="نام محصول یا کد کالا..." class="admin-input w-full">
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold text-[var(--admin-muted)]">دسته‌بندی</span>
            <select name="category_id" class="admin-select w-full">
                <option value="">همه دسته‌ها</option>
                @foreach($categories as $categoryOption)
                    <option value="{{ $categoryOption->id }}" @selected((string) request('category_id') === (string) $categoryOption->id)>{{ $categoryOption->name }}</option>
                @endforeach
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold text-[var(--admin-muted)]">وضعیت انتشار</span>
            <select name="status" class="admin-select w-full">
                <option value="">همه وضعیت‌ها</option>
                <option value="active" @selected(request('status') === 'active')>فعال</option>
                <option value="draft" @selected(request('status') === 'draft')>پیش‌نویس</option>
                <option value="archived" @selected(request('status') === 'archived')>آرشیو</option>
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold text-[var(--admin-muted)]">موجودی</span>
            <select name="stock" class="admin-select w-full">
                <option value="">همه</option>
                <option value="out" @selected(request('stock') === 'out')>ناموجود</option>
                <option value="low" @selected(request('stock') === 'low')>کم (۱ تا ۵)</option>
                <option value="available" @selected(request('stock') === 'available')>موجودی بالای ۵</option>
            </select>
        </label>
        <label class="block">
            <span class="mb-1 block text-xs font-semibold text-[var(--admin-muted)]">کامل‌بودن</span>
            <select name="completeness" class="admin-select w-full">
                <option value="">همه محصولات</option>
                <option value="incomplete" @selected(request('completeness') === 'incomplete')>نیازمند تکمیل</option>
                <option value="complete" @selected(request('completeness') === 'complete')>کامل</option>
            </select>
        </label>
        <div class="flex flex-wrap items-end gap-2 sm:col-span-2 xl:col-span-6">
            <button type="submit" class="admin-btn admin-btn-primary min-h-11">اعمال فیلترها</button>
            <a href="{{ route('admin.products.index') }}" class="admin-btn admin-btn-secondary min-h-11">پاک‌کردن فیلترها</a>
            <span class="self-center text-xs text-[var(--admin-muted)]">{{ \App\Helpers\PersianNumberHelper::format($products->total()) }} محصول</span>
        </div>
    </form>

    <div class="admin-table-wrapper admin-products-table-wrap">
        <table class="admin-table admin-products-table">
            <colgroup>
                <col style="width: 5%">
                <col style="width: 27%">
                <col style="width: 11%">
                <col style="width: 9%">
                <col style="width: 11%">
                <col style="width: 7%">
                <col style="width: 8%">
                <col style="width: 8%">
                <col style="width: 14%">
            </colgroup>

                <thead>
                <tr>
                    <th>#</th>
                    <th>محصول</th>
                    <th>دسته‌بندی</th>
                    <th>SKU</th>
                    <th>قیمت</th>
                    <th>موجودی</th>
                    <th>وضعیت</th>
                    <th>ویژگی‌ها</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>
                @forelse($products as $product)

                    @php
                        $primaryImage = $product->images->first(
                            fn ($image) => $image->is_primary && filled($image->url)
                        ) ?? $product->images->first(
                            fn ($image) => filled($image->url)
                        );
                        $displayStock = $product->variants_count > 0
                            ? (int) ($product->active_variants_stock ?? 0)
                            : (int) $product->stock;
                    @endphp

                    <tr>
                        <td>
                            {{ $products->firstItem() + $loop->index }}
                        </td>

                        <td>
                            <div class="flex min-w-[280px] items-center gap-3">

                                <div class="admin-image-thumb shrink-0">
                                    @if($primaryImage)
                                        <img
                                            src="{{ $primaryImage->url }}"
                                            alt="{{ $product->name }}"
                                            class="admin-image"
                                        >
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-[var(--admin-muted)]">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 fill="none"
                                                 viewBox="0 0 24 24"
                                                 stroke-width="1.5"
                                                 stroke="currentColor"
                                                 class="h-5 w-5">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659m0 0 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z"
                                                />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0">
                                    <a
                                        href="{{ route('admin.products.show', $product) }}"
                                        class="block truncate text-sm font-semibold text-[var(--admin-text)] transition hover:text-[var(--admin-accent)]"
                                    >
                                        {{ $product->name }}
                                    </a>

                                    <p class="mt-1 truncate text-xs text-[var(--admin-muted)]">
                                        {{ $product->short_description ?: 'بدون توضیح کوتاه' }}
                                    </p>
                                </div>

                            </div>
                        </td>

                        <td>
                            {{ $product->category?->name ?? '—' }}
                        </td>

                        <td>
                                <span class="font-mono text-xs text-[var(--admin-text-soft)]">
                                    {{ $product->sku }}
                                </span>
                        </td>

                        <td>
                            <div class="whitespace-nowrap">
                                    <span class="font-semibold text-[var(--admin-text)]">
                                        {{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}
                                    </span>

                                <span class="mr-1 text-xs text-[var(--admin-muted)]">
                                        تومان
                                    </span>

                                @if(
                                    $product->compare_at_price !== null &&
                                    (float) $product->compare_at_price > (float) $product->price
                                )
                                    <div class="mt-1 text-xs text-[var(--admin-muted)] line-through">
                                        {{ \App\Helpers\PersianNumberHelper::format((float) $product->compare_at_price) }}
                                    </div>
                                @endif
                            </div>
                        </td>

                        <td>
                            @if($displayStock > 0)
                                <span class="admin-badge admin-badge-success">
                                        {{ \App\Helpers\PersianNumberHelper::format($displayStock) }}
                                    </span>
                            @else
                                <span class="admin-badge admin-badge-danger">
                                        ناموجود
                                    </span>
                            @endif
                        </td>

                        <td>
                            @switch($product->status)
                                @case('active')
                                <span class="admin-badge admin-badge-success">
                                            فعال
                                        </span>
                                @break

                                @case('draft')
                                <span class="admin-badge admin-badge-warning">
                                            پیش‌نویس
                                        </span>
                                @break

                                @case('archived')
                                <span class="admin-badge admin-badge-neutral">
                                            آرشیو
                                        </span>
                                @break

                                @default
                                <span class="admin-badge admin-badge-neutral">
                                            {{ $product->status }}
                                        </span>
                            @endswitch
                        </td>

                        <td>
                            <div class="flex flex-wrap gap-1">

                                @if($product->is_featured)
                                    <span class="admin-badge admin-badge-info">
                                            ویژه
                                        </span>
                                @endif

                                @if($product->is_new)
                                    <span class="admin-badge admin-badge-success">
                                            جدید
                                        </span>
                                @endif

                                @if(!$product->is_featured && !$product->is_new)
                                    <span class="text-xs text-[var(--admin-muted)]">
                                            —
                                        </span>
                                @endif

                            </div>
                        </td>

                        <td>
                            <div class="admin-action-group admin-products-actions">

                                <a
                                    href="{{ route('admin.products.show', $product) }}"
                                    class="admin-btn admin-btn-ghost px-3"
                                    title="مشاهده"
                                >
                                    مشاهده
                                </a>

                                <a
                                    href="{{ route('admin.products.edit', $product) }}"
                                    class="admin-btn admin-btn-secondary px-3"
                                    title="ویرایش"
                                >
                                    ویرایش
                                </a>

                                <form
                                    action="{{ route('admin.products.destroy', $product) }}"
                                    method="POST"
                                    onsubmit="return confirm('آیا از حذف این محصول مطمئن هستید؟');"
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
                        <td colspan="9">
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
                                            d="m21 8.25-9-5.25-9 5.25m18 0-9 5.25m9-5.25V15l-9 5.25M3 8.25l9 5.25m-9-5.25V15l9 5.25m0-6.75V20.25"
                                        />
                                    </svg>
                                </div>

                                <h3 class="text-sm font-semibold text-[var(--admin-text)]">
                                    هنوز محصولی ثبت نشده است.
                                </h3>

                                <p class="mt-2 text-xs text-[var(--admin-muted)]">
                                    برای شروع، اولین محصول را ایجاد کنید.
                                </p>

                                <a
                                    href="{{ route('admin.products.create') }}"
                                    class="admin-btn admin-btn-primary mt-5"
                                >
                                    افزودن محصول
                                </a>
                            </div>
                        </td>
                    </tr>

                @endforelse
                </tbody>

        </table>
    </div>

    @if($products->hasPages())
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif

@endsection
