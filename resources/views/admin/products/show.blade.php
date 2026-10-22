@extends('admin.layouts.app')

@section('title', $product->name)
@section('page_title', 'جزئیات محصول')

@section('content')

    {{-- Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>

            <span class="text-xs font-medium text-[var(--admin-accent)]">
                CATALOG / PRODUCTS / VIEW
            </span>

            <h2 class="admin-title mt-2">
                {{ $product->name }}
            </h2>

            <p class="admin-subtitle mt-2">
                مشاهده جزئیات، تصاویر و تنوع‌های محصول
            </p>

        </div>


        <div class="flex flex-wrap gap-2">

            <a
                href="{{ route('admin.products.edit', $product) }}"
                class="admin-btn admin-btn-primary"
            >
                ویرایش محصول
            </a>

            <a
                href="{{ route('admin.products.index') }}"
                class="admin-btn admin-btn-secondary"
            >
                بازگشت
            </a>

        </div>

    </div>


    {{-- Product Overview --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Primary Image --}}
        <div class="xl:col-span-1">

            <div class="admin-card p-5">

                <div class="admin-image-preview aspect-square">

                    @php
                        $primaryImage = $product->primaryImage();
                    @endphp

                    @if($primaryImage)

                        <img
                            src="{{ $primaryImage->url }}"
                            alt="{{ $primaryImage->alt ?: $product->name }}"
                            class="admin-image"
                        >

                    @else

                        <div class="flex h-full items-center justify-center text-[var(--admin-muted)]">

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                class="h-14 w-14"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l2.659 2.659 1.5-1.5a2.25 2.25 0 0 1 3.182 0l3.068 3.068M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Z"
                                />
                            </svg>

                        </div>

                    @endif

                </div>


                @if($product->images->count() > 1)

                    <div class="mt-4 grid grid-cols-4 gap-2">

                        @foreach($product->images->take(8) as $image)

                            <div class="admin-image-thumb aspect-square !h-auto">

                                <img
                                    src="{{ $image->url }}"
                                    alt="{{ $image->alt ?: $product->name }}"
                                    class="admin-image"
                                >

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>


        {{-- Product Information --}}
        <div class="space-y-6 xl:col-span-2">

            {{-- Main Info --}}
            <div class="admin-card p-6">

                <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-start">

                    <div>

                        <h3 class="text-base font-bold text-[var(--admin-text)]">
                            اطلاعات محصول
                        </h3>

                        <p class="mt-1 text-xs text-[var(--admin-muted)]">
                            مشخصات اصلی محصول
                        </p>

                    </div>


                    <div class="flex flex-wrap gap-2">

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

                        @endswitch


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

                    </div>

                </div>


                <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                    <div>
                        <dt class="admin-stat-label">
                            نام محصول
                        </dt>

                        <dd class="mt-2 text-sm font-semibold text-[var(--admin-text)]">
                            {{ $product->name }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            دسته‌بندی
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $product->category?->name ?? '—' }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            SKU
                        </dt>

                        <dd class="mt-2 font-mono text-sm text-[var(--admin-text-soft)]">
                            {{ $product->sku }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            Slug
                        </dt>

                        <dd class="mt-2 break-all font-mono text-sm text-[var(--admin-text-soft)]">
                            {{ $product->slug }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            موجودی
                        </dt>

                        <dd class="mt-2 text-sm font-semibold text-[var(--admin-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format($product->stock) }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            درصد تخفیف
                        </dt>

                        <dd class="mt-2 text-sm font-semibold text-[var(--admin-accent)]">
                            {{ $product->discount_percentage !== null
                                ? $product->discount_percentage . '٪'
                                : 'بدون تخفیف'
                            }}
                        </dd>
                    </div>

                </dl>

            </div>


            {{-- Pricing --}}
            <div class="admin-card p-6">

                <div class="mb-6">
                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        قیمت
                    </h3>
                </div>


                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5">

                        <p class="text-xs text-[var(--admin-muted)]">
                            قیمت فروش
                        </p>

                        <p class="mt-2 text-xl font-bold text-[var(--admin-text)]">
                            {{ \App\Helpers\PersianNumberHelper::format((float) $product->price) }}

                            <span class="text-xs font-normal text-[var(--admin-muted)]">
                                تومان
                            </span>
                        </p>

                    </div>


                    <div class="rounded-xl border border-[var(--admin-border)] bg-[var(--admin-surface-soft)] p-5">

                        <p class="text-xs text-[var(--admin-muted)]">
                            قیمت قبل
                        </p>

                        <p class="mt-2 text-xl font-bold text-[var(--admin-text-soft)]">

                            @if($product->compare_at_price !== null)

                                {{ \App\Helpers\PersianNumberHelper::format((float) $product->compare_at_price) }}

                                <span class="text-xs font-normal text-[var(--admin-muted)]">
                                    تومان
                                </span>

                            @else

                                —

                            @endif

                        </p>

                    </div>

                </div>

            </div>


            {{-- Short Description --}}
            @if(filled($product->short_description))

                <div class="admin-card p-6">

                    <h3 class="text-base font-bold text-[var(--admin-text)]">
                        توضیح کوتاه
                    </h3>

                    <p class="mt-3 text-sm leading-8 text-[var(--admin-text-soft)]">
                        {{ $product->short_description }}
                    </p>

                </div>

            @endif


            {{-- Full Description --}}
            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    توضیحات محصول
                </h3>

                <div class="mt-4 text-sm leading-8 text-[var(--admin-text-soft)]">

                    @if(filled($product->description))

                        {!! nl2br(e($product->description)) !!}

                    @else

                        <span class="text-[var(--admin-muted)]">
                            توضیحی برای این محصول ثبت نشده است.
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </div>


    <section class="admin-card mt-6 flex flex-col justify-between gap-4 p-5 sm:flex-row sm:items-center sm:p-6">
        <div>
            <h3 class="text-sm font-bold text-[var(--admin-text)]">رنگ‌ها و تصاویر وابسته به هر رنگ</h3>
            <p class="mt-2 text-xs leading-7 text-[var(--admin-muted)]">برای جلوگیری از مدیریت پراکنده، همهٔ تنوع‌ها و تصاویرشان در فرم ویرایش همین محصول به‌صورت یکجا قابل مدیریت‌اند.</p>
        </div>
        <a href="{{ route('admin.products.edit', $product) }}" class="admin-btn admin-btn-primary shrink-0">مدیریت محصول و تنوع‌ها</a>
    </section>


    {{-- SEO --}}
    <div class="mt-6">

        <div class="admin-card p-6">

            <div class="mb-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات SEO
                </h3>

            </div>


            <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">

                <div>

                    <dt class="admin-stat-label">
                        Meta Title
                    </dt>

                    <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                        {{ $product->meta_title ?: '—' }}
                    </dd>

                </div>


                <div>

                    <dt class="admin-stat-label">
                        Meta Description
                    </dt>

                    <dd class="mt-2 text-sm leading-7 text-[var(--admin-text-soft)]">
                        {{ $product->meta_description ?: '—' }}
                    </dd>

                </div>

            </dl>

        </div>

    </div>


    {{-- Danger Zone --}}
    <div class="mt-6">

        <div class="admin-card p-6">

            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">

                <div>

                    <h3 class="text-sm font-bold text-[var(--admin-text)]">
                        حذف محصول
                    </h3>

                    <p class="mt-1 text-xs text-[var(--admin-muted)]">
                        محصول با Soft Delete حذف خواهد شد.
                    </p>

                </div>


                <form
                    action="{{ route('admin.products.destroy', $product) }}"
                    method="POST"
                    onsubmit="return confirm('آیا از حذف این محصول مطمئن هستید؟');"
                >

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="admin-btn admin-btn-danger"
                    >
                        حذف محصول
                    </button>

                </form>

            </div>

        </div>

    </div>

@endsection
