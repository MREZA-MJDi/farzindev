@extends('admin.layouts.app')

@section('title', 'جزئیات تصویر')
@section('page_title', 'جزئیات تصویر محصول')

@section('content')

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">

        <div>

            <div class="mb-2 text-xs font-medium text-[var(--admin-accent)]">
                CATALOG / PRODUCT IMAGES / VIEW
            </div>

            <h2 class="admin-title">
                جزئیات تصویر
            </h2>

            <p class="admin-subtitle mt-2">
                اطلاعات کامل تصویر محصول
            </p>

        </div>

        <div class="flex flex-wrap gap-2">

            <a
                href="{{ route('admin.product-images.edit', $productImage) }}"
                class="admin-btn admin-btn-primary"
            >
                ویرایش
            </a>

            @if($productImage->product)

                <a
                    href="{{ route('admin.products.show', $productImage->product) }}"
                    class="admin-btn admin-btn-secondary"
                >
                    مشاهده محصول
                </a>

            @endif

            <a
                href="{{ route('admin.product-images.index') }}"
                class="admin-btn admin-btn-secondary"
            >
                بازگشت
            </a>

        </div>

    </div>


    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Image --}}
        <div class="xl:col-span-2">

            <div class="admin-card p-5">

                <div class="admin-image-preview overflow-hidden">

                    <img
                        src="{{ $productImage->url }}"
                        alt="{{ $productImage->alt ?: $productImage->product?->name }}"
                        class="mx-auto block max-h-[700px] w-full object-contain"
                    >

                </div>

            </div>

        </div>


        {{-- Details --}}
        <div class="space-y-6">

            <div class="admin-card p-6">

                <h3 class="text-base font-bold text-[var(--admin-text)]">
                    اطلاعات
                </h3>

                <dl class="mt-6 space-y-5">

                    <div>
                        <dt class="admin-stat-label">
                            محصول
                        </dt>

                        <dd class="mt-2">

                            @if($productImage->product)

                                <a
                                    href="{{ route('admin.products.show', $productImage->product) }}"
                                    class="text-sm font-semibold text-[var(--admin-accent)] hover:text-[var(--admin-accent-hover)]"
                                >
                                    {{ $productImage->product->name }}
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
                            Alt
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $productImage->alt ?: '—' }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            ترتیب
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $productImage->sort_order }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            وضعیت
                        </dt>

                        <dd class="mt-2">

                            @if($productImage->is_primary)

                                <span class="admin-badge admin-badge-success">
                                    تصویر اصلی
                                </span>

                            @else

                                <span class="admin-badge admin-badge-neutral">
                                    تصویر معمولی
                                </span>

                            @endif

                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            مسیر فایل
                        </dt>

                        <dd class="mt-2 break-all font-mono text-xs leading-6 text-[var(--admin-muted)]">
                            {{ $productImage->path }}
                        </dd>
                    </div>


                    <div>
                        <dt class="admin-stat-label">
                            تاریخ ایجاد
                        </dt>

                        <dd class="mt-2 text-sm text-[var(--admin-text-soft)]">
                            {{ $productImage->created_at ? \App\Helpers\JalaliHelper::format($productImage->created_at, 'Y/m/d H:i') : '—' }}
                        </dd>
                    </div>

                </dl>

            </div>


            {{-- Delete --}}
            <div class="admin-card p-6">

                <h3 class="text-sm font-bold text-[var(--admin-text)]">
                    حذف تصویر
                </h3>

                <p class="mt-2 text-xs leading-6 text-[var(--admin-muted)]">
                    با حذف تصویر، فایل آن نیز از فضای ذخیره‌سازی حذف خواهد شد.
                </p>

                <form
                    action="{{ route('admin.product-images.destroy', $productImage) }}"
                    method="POST"
                    class="mt-5"
                    onsubmit="return confirm('آیا از حذف این تصویر مطمئن هستید؟');"
                >

                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="admin-btn admin-btn-danger w-full"
                    >
                        حذف تصویر
                    </button>

                </form>

            </div>

        </div>

    </div>

@endsection
